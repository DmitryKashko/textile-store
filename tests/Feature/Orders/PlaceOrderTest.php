<?php

namespace Tests\Feature\Orders;

use App\Events\OrderPlaced;
use App\Listeners\SendOrderConfirmation;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderConfirmation;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_it_places_an_order_and_reserves_stock(): void
    {
        Event::fake([OrderPlaced::class]);

        $linen = Product::factory()->create(['price' => 20000, 'stock' => 100]);
        $cotton = Product::factory()->create(['price' => 10000, 'stock' => 80]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $linen->id, 'quantity' => 3],
                ['product_id' => $cotton->id, 'quantity' => 50],
            ],
        ]);

        // 3 * 200.00 + 50 * 100.00 with 5% wholesale discount
        $response->assertCreated()
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.total', 60000 + 475000)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.items.1.discount_percent', 5);

        $this->assertSame(97, $linen->fresh()->stock);
        $this->assertSame(30, $cotton->fresh()->stock);
        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id, 'total' => 535000]);

        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_it_keeps_a_snapshot_of_the_product(): void
    {
        $product = Product::factory()->create(['name' => 'Navy linen', 'price' => 20000, 'stock' => 10]);

        $this->postJson('/api/orders', ['items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertCreated();

        $product->update(['name' => 'Renamed', 'price' => 99900]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name' => 'Navy linen',
            'unit_price' => 20000,
        ]);
    }

    public function test_it_rejects_an_order_when_stock_is_insufficient(): void
    {
        Event::fake([OrderPlaced::class]);

        $available = Product::factory()->create(['stock' => 10]);
        $scarce = Product::factory()->create(['stock' => 2]);

        $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $available->id, 'quantity' => 5],
                ['product_id' => $scarce->id, 'quantity' => 3],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.items.0.product_id', $scarce->id)
            ->assertJsonPath('errors.items.0.available', 2);

        // nothing is written when one of the lines fails
        $this->assertSame(10, $available->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        Event::assertNotDispatched(OrderPlaced::class);
    }

    public function test_inactive_product_cannot_be_ordered(): void
    {
        $product = Product::factory()->inactive()->create(['stock' => 10]);

        $this->postJson('/api/orders', ['items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertUnprocessable()
            ->assertJsonPath('errors.items.0.product_id', $product->id);
    }

    public function test_it_validates_items(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
                ['product_id' => $product->id, 'quantity' => 0],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id', 'items.1.product_id', 'items.1.quantity']);
    }

    public function test_guest_cannot_place_an_order(): void
    {
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/orders', ['items' => []])->assertUnauthorized();
    }

    public function test_confirmation_is_sent_from_the_queue(): void
    {
        Queue::fake();

        $product = Product::factory()->create(['stock' => 10]);

        $this->postJson('/api/orders', ['items' => [['product_id' => $product->id, 'quantity' => 2]]])
            ->assertCreated();

        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === SendOrderConfirmation::class);
    }

    public function test_listener_notifies_the_customer(): void
    {
        Notification::fake();
        Event::fake([OrderPlaced::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $this->postJson('/api/orders', ['items' => [['product_id' => $product->id, 'quantity' => 2]]]);

        $order = $this->user->orders()->first();
        (new SendOrderConfirmation)->handle(new OrderPlaced($order));

        Notification::assertSentTo($this->user, OrderConfirmation::class);
    }
}
