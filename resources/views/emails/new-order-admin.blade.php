<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order — E-Souq Plus</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f4;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f5f5f4;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e7e5e4;">
                <tr>
                    <td style="background:linear-gradient(135deg,#5b21b6,#4c1d95);padding:28px 24px;text-align:center;">
                        <p style="margin:0;font-size:24px;line-height:1;font-weight:800;color:#ffffff;">E-Souq Plus</p>
                        <p style="margin:8px 0 0;font-size:12px;color:#ddd6fe;letter-spacing:0.08em;text-transform:uppercase;">New Order Alert</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 24px 12px;">
                        <p style="margin:0 0 12px;font-size:16px;line-height:1.5;color:#1c1917;">Hello {{ $adminUser->name }},</p>
                        <p style="margin:0;font-size:15px;line-height:1.6;color:#57534e;">
                            A new order has been placed on the store. Review the details below and process it from the admin panel.
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
                                    <p style="margin:0 0 4px;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Customer:</strong> {{ $order->customer_name }}</p>
                                    <p style="margin:0 0 4px;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Email:</strong> {{ $order->email }}</p>
                                    <p style="margin:0 0 4px;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Phone:</strong> {{ $order->phone }}</p>
                                    <p style="margin:0 0 4px;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Payment:</strong> {{ $paymentLabel }}</p>
                                    <p style="margin:0;font-size:13px;color:#57534e;"><strong style="color:#1c1917;">Address:</strong> {{ $order->address }}, {{ $order->city }}, {{ $order->country }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 16px;">
                        <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#44403c;text-transform:uppercase;letter-spacing:0.06em;">Items ({{ $order->item_count }})</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td style="padding:8px 0;border-bottom:1px solid #f5f5f4;font-size:14px;color:#1c1917;">
                                        {{ $item->product_name }} × {{ $item->quantity }}
                                    </td>
                                    <td align="right" style="padding:8px 0;border-bottom:1px solid #f5f5f4;font-size:14px;font-weight:600;color:#5b21b6;white-space:nowrap;">
                                        AED {{ number_format((float) $item->line_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 24px;">
                        <p style="margin:0;font-size:16px;font-weight:700;color:#1c1917;text-align:right;">
                            Total: <span style="color:#5b21b6;">AED {{ number_format((float) $order->total, 2) }}</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0 24px 28px;">
                        <a href="{{ $adminOrdersUrl }}" style="display:inline-block;background-color:#5b21b6;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:14px 28px;border-radius:999px;">
                            View in Admin Panel
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 28px;text-align:center;">
                        <p style="margin:0;font-size:12px;line-height:1.5;color:#a8a29e;">© {{ date('Y') }} E-Souq Plus — Admin Notification</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
