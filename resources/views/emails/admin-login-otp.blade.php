<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Souq Plus Admin Login Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f4;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f5f5f4;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e7e5e4;">
                <tr>
                    <td style="background:linear-gradient(135deg,#5b21b6,#4c1d95);padding:28px 24px;text-align:center;">
                        <p style="margin:0;font-size:24px;line-height:1;font-weight:800;color:#ffffff;">E-Souq Plus</p>
                        <p style="margin:8px 0 0;font-size:12px;color:#ddd6fe;letter-spacing:0.08em;text-transform:uppercase;">Admin Security Verification</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 24px 12px;">
                        <p style="margin:0 0 12px;font-size:16px;line-height:1.5;color:#1c1917;">Hello {{ $userName }},</p>
                        <p style="margin:0;font-size:15px;line-height:1.6;color:#57534e;">
                            Use the verification code below to complete your admin login. This code expires in
                            <strong style="color:#1c1917;">{{ $expiresMinutes }} minutes</strong>.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:8px 24px 24px;">
                        <div style="display:inline-block;background-color:#fff7ed;border:2px dashed #f97316;border-radius:14px;padding:18px 28px;">
                            <p style="margin:0 0 6px;font-size:12px;color:#c2410c;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Your OTP Code</p>
                            <p style="margin:0;font-size:36px;line-height:1;letter-spacing:0.35em;font-weight:700;color:#5b21b6;font-family:'Courier New',Courier,monospace;">{{ $otp }}</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 28px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fafaf9;border-radius:12px;">
                            <tr>
                                <td style="padding:16px;">
                                    <p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#44403c;">
                                        After verification, you can sign in with your email and password for the next
                                        <strong>7 days</strong> without entering a new code.
                                    </p>
                                    <p style="margin:0;font-size:13px;line-height:1.5;color:#78716c;">
                                        If you did not attempt to sign in, you can safely ignore this email.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 28px;text-align:center;">
                        <p style="margin:0;font-size:12px;line-height:1.5;color:#a8a29e;">© {{ date('Y') }} E-Souq Plus — Admin Panel</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
