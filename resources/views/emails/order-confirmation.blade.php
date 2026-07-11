<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation — E-Souq Plus</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f4;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f5f5f4;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e7e5e4;">
                <tr>
                    <td style="background:linear-gradient(135deg,#5b21b6,#4c1d95);padding:28px 24px;text-align:center;">
                        <p style="margin:0;font-size:24px;line-height:1;font-weight:800;color:#ffffff;">E-Souq Plus</p>
                        <p style="margin:8px 0 0;font-size:12px;color:#ddd6fe;letter-spacing:0.08em;text-transform:uppercase;">Order Confirmation</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 24px 12px;">
                        <p style="margin:0 0 12px;font-size:16px;line-height:1.5;color:#1c1917;">Hello {{ $order->customer_name }},</p>
                        <p style="margin:0;font-size:15px;line-height:1.6;color:#57534e;">
                            Thank you for your order. We have received it and will contact you shortly to confirm delivery.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 24px 16px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fafaf9;border-radius:12px;">
                            <tr>
                                <td style="padding:16px;">
                                    <p style="margin:0 0 8px;font-size:12px;color:#78716c;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Order number</p>
                                    <p style="margin:0 0 16px;font-size:20px;font-weight:700;color:#5b21b6;">{{ $order->order_number }}</p>
                                    <p style="margin:0 0 4px;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Payment:</strong> {{ $paymentLabel }}</p>
                                    <p style="margin:0 0 4px;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Phone:</strong> {{ $order->phone }}</p>
                                    <p style="margin:0;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Delivery to:</strong> {{ $order->address }}, {{ $order->city }}, {{ $order->country }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 16px;">
                        <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#44403c;text-transform:uppercase;letter-spacing:0.06em;">Order items</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f5f5f4;font-size:14px;color:#1c1917;">
                                        {{ $item->product_name }}
                                        @if ($item->variant_label)
                                            <span style="color:#78716c;">({{ $item->variant_label }})</span>
                                        @endif
                                        <br>
                                        <span style="font-size:12px;color:#78716c;">Qty {{ $item->quantity }} × AED {{ number_format((float) $item->unit_price, 2) }}</span>
                                    </td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #f5f5f4;font-size:14px;font-weight:600;color:#5b21b6;white-space:nowrap;">
                                        AED {{ number_format((float) $item->line_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 24px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fff7ed;border-radius:12px;">
                            <tr>
                                <td style="padding:16px;font-size:14px;color:#44403c;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding:2px 0;">Subtotal</td>
                                            <td align="right" style="padding:2px 0;">AED {{ number_format((float) $order->subtotal, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:2px 0;">Delivery</td>
                                            <td align="right" style="padding:2px 0;">AED {{ number_format((float) $order->delivery_fee, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0 0;font-size:16px;font-weight:700;color:#1c1917;">Total</td>
                                            <td align="right" style="padding:8px 0 0;font-size:16px;font-weight:700;color:#5b21b6;">AED {{ number_format((float) $order->total, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @if ($order->notes)
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <p style="margin:0 0 6px;font-size:12px;color:#78716c;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">Your notes</p>
                            <p style="margin:0;font-size:14px;line-height:1.5;color:#57534e;">{{ $order->notes }}</p>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:0 24px 28px;text-align:center;">
                        <p style="margin:0;font-size:12px;line-height:1.5;color:#a8a29e;">© {{ date('Y') }} E-Souq Plus</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
