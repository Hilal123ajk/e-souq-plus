<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StartStripeCheckoutRequest;
use App\Models\Order;
use App\Services\OrderPaymentEventService;
use App\Services\OrderService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;

class StripeCheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly StripeCheckoutService $stripeCheckoutService,
        private readonly OrderPaymentEventService $paymentEventService,
    ) {}

    public function store(StartStripeCheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = $this->orderService->createPendingStripeOrder(
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'notes' => $validated['notes'] ?? null,
            ],
            $validated['items'],
        );

        try {
            $session = $this->stripeCheckoutService->createCheckoutSession($order);
        } catch (ApiErrorException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to start card payment. Please try again or choose Cash on Delivery.',
            ], 502);
        }

        $this->orderService->attachStripeSession($order, $session);

        return response()->json([
            'checkout_url' => $session->url,
            'order_number' => $order->order_number,
        ]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        if ($order->payment_method === Order::PAYMENT_METHOD_STRIPE
            && $order->payment_status !== Order::PAYMENT_STATUS_PAID) {
            $this->paymentEventService->recordCheckoutCancelled($order);
        }

        return redirect()
            ->route('store.checkout', ['payment_cancelled' => 1])
            ->with('checkout_error', 'Payment was cancelled. You can try again or choose Cash on Delivery.');
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id', '');

        if ($sessionId === '') {
            return redirect()
                ->route('store.checkout', ['payment_error' => 1])
                ->with('checkout_error', 'Payment session was not found.');
        }

        $order = null;
        $session = null;

        try {
            $session = $this->stripeCheckoutService->retrieveSession($sessionId);
            $order = $this->resolveOrderForSession($session);

            if ($order === null) {
                throw ValidationException::withMessages([
                    'payment' => ['Order for this payment could not be found.'],
                ]);
            }

            $order = $this->orderService->completeStripePayment($order, $session);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();

            if ($order instanceof Order && $session instanceof \Stripe\Checkout\Session) {
                $this->paymentEventService->recordPaymentVerificationFailed(
                    $order,
                    $session,
                    is_string($message) ? $message : 'Payment verification failed.',
                );
            }

            return redirect()
                ->route('store.checkout', ['payment_error' => 1])
                ->with('checkout_error', $message);
        } catch (ApiErrorException $exception) {
            report($exception);

            return redirect()
                ->route('store.checkout', ['payment_error' => 1])
                ->with('checkout_error', 'Unable to verify your payment. Please contact support if you were charged.');
        }

        return redirect()
            ->route('store.checkout')
            ->with('order_success', [
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
            ]);
    }

    public function webhook(Request $request): Response
    {
        try {
            $event = $this->stripeCheckoutService->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );
        } catch (SignatureVerificationException|\RuntimeException $exception) {
            $this->stripeCheckoutService->logWebhookFailure('Stripe webhook rejected.', [
                'message' => $exception->getMessage(),
            ]);

            return response('Invalid payload', 400);
        }

        $this->handleWebhookEvent($event);

        return response('OK', 200);
    }

    private function handleWebhookEvent(\Stripe\Event $event): void
    {
        $object = $event->data->object ?? null;

        try {
            if ($event->type === 'checkout.session.completed' && $object !== null) {
                $fullSession = $this->stripeCheckoutService->retrieveSession($object->id);
                $order = $this->resolveOrderForSession($fullSession);

                if ($order !== null) {
                    $this->paymentEventService->recordWebhookEvent($order, $event, $fullSession);

                    if ($this->stripeCheckoutService->isSessionPaid($fullSession)) {
                        $this->orderService->completeStripePayment($order, $fullSession);
                    }
                }

                return;
            }

            if ($event->type === 'checkout.session.expired' && $object !== null) {
                $order = $this->resolveOrderForSession($object);

                if ($order !== null) {
                    $this->paymentEventService->recordWebhookEvent($order, $event, $object);
                    $this->paymentEventService->recordPaymentFailed($order, $object, [
                        'source' => 'webhook',
                        'stripe_event_type' => $event->type,
                    ]);
                }

                return;
            }

            if ($event->type === 'payment_intent.payment_failed' && $object !== null) {
                $order = $this->resolveOrderForPaymentIntent($object);

                if ($order !== null) {
                    $this->paymentEventService->recordWebhookEvent($order, $event, $object);
                    $this->paymentEventService->recordPaymentFailed($order, $object, [
                        'source' => 'webhook',
                        'stripe_event_type' => $event->type,
                    ]);

                    $order->update(['payment_status' => Order::PAYMENT_STATUS_FAILED]);
                }
            }
        } catch (\Throwable $exception) {
            report($exception);
            $this->stripeCheckoutService->logWebhookFailure('Stripe webhook fulfillment failed.', [
                'event_id' => $event->id ?? null,
                'event_type' => $event->type ?? null,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveOrderForSession(\Stripe\Checkout\Session $session): ?Order
    {
        $metadataOrderId = $session->metadata['order_id'] ?? null;

        if ($metadataOrderId !== null) {
            $order = Order::query()->find((int) $metadataOrderId);

            if ($order !== null) {
                return $order;
            }
        }

        if ($session->id !== '') {
            return Order::query()
                ->where('stripe_checkout_session_id', $session->id)
                ->first();
        }

        return null;
    }

    private function resolveOrderForPaymentIntent(\Stripe\PaymentIntent $paymentIntent): ?Order
    {
        $paymentIntentId = $paymentIntent->id ?? null;

        if ($paymentIntentId !== null) {
            $order = Order::query()
                ->where('stripe_payment_intent_id', $paymentIntentId)
                ->first();

            if ($order !== null) {
                return $order;
            }
        }

        $sessionId = $paymentIntent->metadata['checkout_session_id'] ?? null;

        if (is_string($sessionId) && $sessionId !== '') {
            return Order::query()
                ->where('stripe_checkout_session_id', $sessionId)
                ->first();
        }

        $metadataOrderId = $paymentIntent->metadata['order_id'] ?? null;

        if ($metadataOrderId !== null) {
            return Order::query()->find((int) $metadataOrderId);
        }

        return null;
    }
}
