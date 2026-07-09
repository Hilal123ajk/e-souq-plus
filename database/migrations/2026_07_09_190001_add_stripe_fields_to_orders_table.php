<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_status', 30)->default('unpaid')->after('payment_method');
            $table->string('stripe_checkout_session_id')->nullable()->after('payment_status');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');

            $table->index('stripe_checkout_session_id');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn([
                'payment_status',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
            ]);
        });
    }
};
