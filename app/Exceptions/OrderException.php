<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Expected business errors of order placement: rendered as 422 and not reported.
 */
abstract class OrderException extends RuntimeException
{
}
