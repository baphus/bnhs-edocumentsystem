<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BNHS eDocument - Request Submitted</title>
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
                            <h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 22px; font-weight: 600;">Request Submitted Successfully</h2>
                            
                            <p style="margin: 0 0 25px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                Dear {{ $request->first_name }},
                            </p>

                            <p style="margin: 0 0 25px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                Your document request has been successfully submitted to the BNHS eDocument Request. We have received your request and will begin processing it shortly.
                            </p>

                            <h3 style="margin: 30px 0 15px 0; color: #0038a8; font-size: 18px; font-weight: 600;">Request Details</h3>

                            <!-- Request Details Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 30px 0; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Tracking Code:</strong>
                                                    <span style="color: #0038a8; font-size: 14px; font-weight: 600; font-family: 'Courier New', monospace; margin-left: 10px;">{{ $request->tracking_id }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Document Type:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->documentType->name }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Purpose:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->purpose }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Quantity:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->quantity ?? 1 }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Status:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->status }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Estimated Completion Date:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->estimated_completion_date ? $request->estimated_completion_date->format('F d, Y') : 'To be determined' }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Date Requested:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->created_at->format('F d, Y h:i A') }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <h3 style="margin: 30px 0 15px 0; color: #0038a8; font-size: 18px; font-weight: 600;">Next Steps</h3>

                            <p style="margin: 0 0 25px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                Your request is currently under review. We will notify you via email when there are any status updates.
                            </p>

                            <!-- Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #0038a8; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; text-align: center;">View Your Dashboard</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #0038a8; border-radius: 4px;">
                                <p style="margin: 0; color: #004085; font-size: 14px; line-height: 1.6;">
                                    <strong>Important:</strong> Please save your tracking code (<span style="font-family: 'Courier New', monospace; font-weight: 600;">{{ $request->tracking_id }}</span>) for future reference. You can use it to track your request status at any time.
                                </p>
                            </div>

                            <p style="margin: 30px 0 0 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                If you have any questions or concerns, please contact the registrar's office.
                            </p>
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
