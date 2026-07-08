<?php

declare(strict_types=1);

namespace App\Support;

final class DeliveryPolicy
{
    /**
     * @return array<string, mixed>
     */
    public static function frontendConfig(): array
    {
        return [
            'fee' => (int) config('esouq.standard_delivery_fee', 25),
        ];
    }
}
