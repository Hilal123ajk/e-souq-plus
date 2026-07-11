<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewOrderAdminMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderPlacedNotifications implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->orderId;
    }

    public function handle(): void
    {
        $order = Order::query()
            ->with('items')
            ->find($this->orderId);

        if ($order === null) {
            return;
        }

        if ($this->notificationsComplete($order)) {
            return;
        }

        $this->sendCustomerConfirmation($order);
        $order->refresh();

        $pendingAdminFailures = $this->sendAdminNotifications($order);
        $order->refresh();

        if ($pendingAdminFailures !== []) {
            throw new \RuntimeException(
                'Failed to notify admin users: '.implode(', ', $pendingAdminFailures),
            );
        }
    }

    public function failed(?Throwable $exception): void
    {
        report($exception);
    }

    private function notificationsComplete(Order $order): bool
    {
        if ($this->shouldSendCustomerConfirmation($order) && $order->customer_notified_at === null) {
            return false;
        }

        $adminUsers = $this->adminRecipients();

        if ($adminUsers->isEmpty()) {
            return true;
        }

        $notifiedIds = $this->notifiedAdminUserIds($order);

        return $adminUsers->every(fn (User $user): bool => in_array($user->id, $notifiedIds, true));
    }

    private function sendCustomerConfirmation(Order $order): void
    {
        if (! $this->shouldSendCustomerConfirmation($order) || $order->customer_notified_at !== null) {
            return;
        }

        Mail::to($order->email)->send(new OrderConfirmationMail($order));

        $order->forceFill(['customer_notified_at' => now()])->save();
    }

    /**
     * @return list<string>
     */
    private function sendAdminNotifications(Order $order): array
    {
        $failures = [];
        $notifiedIds = $this->notifiedAdminUserIds($order);
        $delaySeconds = 0;

        foreach ($this->adminRecipients() as $adminUser) {
            if (in_array($adminUser->id, $notifiedIds, true)) {
                continue;
            }

            if (! filter_var($adminUser->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($delaySeconds > 0) {
                sleep($delaySeconds);
            }

            try {
                Mail::to($adminUser->email)->send(new NewOrderAdminMail($order, $adminUser));

                $notifiedIds[] = $adminUser->id;
                $order->forceFill([
                    'admin_notified_user_ids' => array_values(array_unique($notifiedIds)),
                ])->save();

                $delaySeconds = 1;
            } catch (Throwable $exception) {
                report($exception);
                $failures[] = $adminUser->email;
                $delaySeconds = 1;
            }
        }

        return $failures;
    }

    private function shouldSendCustomerConfirmation(Order $order): bool
    {
        return filter_var($order->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return list<int>
     */
    private function notifiedAdminUserIds(Order $order): array
    {
        $ids = $order->admin_notified_user_ids;

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_map('intval', $ids));
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function adminRecipients()
    {
        return User::query()
            ->whereIn('role', ['admin', 'manager'])
            ->orderBy('id')
            ->get();
    }
}
