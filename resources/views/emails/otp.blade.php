<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BNHS eDocument - Verification Code</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0038a8 0%, #002d86 100%); padding: 30px 40px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <img src="{{ config('app.url') }}/images/logo.png" alt="BNHS Logo" style="height: 80px; width: auto; margin-bottom: 15px; display: block;" />
                                        <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600; letter-spacing: 0.5px;">Bato National High School</h1>
                                        <p style="margin: 5px 0 0 0; color: #e6ebf5; font-size: 14px;">eDocument Request</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 22px; font-weight: 600;">Your Verification Code</h2>
                            
                            <p style="margin: 0 0 25px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                @if($purpose === 'request')
                                    You are requesting to submit a document request on the BNHS eDocument Request.
                                @else
                                    You are attempting to track your document request on the BNHS eDocument Request.
                                @endif
                            </p>

                            <p style="margin: 0 0 15px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                Your one-time verification code is:
                            </p>

                            <!-- OTP Code Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 25px 0;">
                                <tr>
                                    <td align="center" style="background-color: #f8f9fa; border: 2px solid #0038a8; border-radius: 8px; padding: 25px;">
                                        <div style="text-align: center; font-size: 36px; font-weight: bold; letter-spacing: 12px; font-family: 'Courier New', monospace; color: #0038a8;">
                                            {{ $otp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0 0 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                This code will expire in <strong>{{ $expiresIn }}</strong>.
                            </p>

                            <div style="margin-top: 30px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #fcd116; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                    <strong>Important:</strong> If you did not request this code, please ignore this email.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; color: #4a4a4a; font-size: 14px; line-height: 1.6;">
                                <strong>Bato National High School</strong><br>
                                Toledo City, Cebu
                            </p>
                            <p style="margin: 15px 0 0 0; color: #888888; font-size: 12px; line-height: 1.5;">
                                This is an automated message from the BNHS eDocument Request. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
