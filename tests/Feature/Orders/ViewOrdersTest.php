<?php

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ViewOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->for($user)->create();
        Order::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_view_own_order(): void
    {
        $order = Order::factory()->create();

        Sanctum::actingAs($order->user);

        $this->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_user_cannot_view_someone_elses_order(): void
    {
        $order = Order::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/orders/{$order->id}")->assertForbidden();
    }
}
