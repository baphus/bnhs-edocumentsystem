<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BNHS eDocument - Request Status Updated</title>
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
                                        <p style="margin: 5px 0 0 0; color: #e6ebf5; font-size: 14px;">eDocument System</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 22px; font-weight: 600;">Request Status Updated</h2>
                            
                            <p style="margin: 0 0 25px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                Dear {{ $request->first_name }},
                            </p>

                            <p style="margin: 0 0 25px 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                Your document request status has been updated.
                            </p>

                            <h3 style="margin: 30px 0 15px 0; color: #0038a8; font-size: 18px; font-weight: 600;">Status Update</h3>

                            <!-- Status Details Box -->
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
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Previous Status:</strong>
                                                    <span style="color: #666666; font-size: 14px; margin-left: 10px;">{{ $oldStatus }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Current Status:</strong>
                                                    <span style="color: #0038a8; font-size: 14px; font-weight: 600; margin-left: 10px;">{{ $request->status }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Document Type:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->documentType->name }}</span>
                                                </td>
                                            </tr>
                                            @if($request->estimated_completion_date)
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Estimated Completion Date:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->estimated_completion_date->format('F d, Y') }}</span>
                                                </td>
                                            </tr>
                                            @endif
                                            @if($request->completed_at)
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Date Completed:</strong>
                                                    <span style="color: #4a4a4a; font-size: 14px; margin-left: 10px;">{{ $request->completed_at->format('F d, Y h:i A') }}</span>
                                                </td>
                                            </tr>
                                            @endif
                                            @if($request->admin_remarks)
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <strong style="color: #1a1a1a; font-size: 14px;">Admin Remarks:</strong>
                                                    <div style="color: #4a4a4a; font-size: 14px; margin-top: 8px; padding: 10px; background-color: #ffffff; border-radius: 4px; line-height: 1.6;">
                                                        {{ $request->admin_remarks }}
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <h3 style="margin: 30px 0 15px 0; color: #0038a8; font-size: 18px; font-weight: 600;">Status Description</h3>

                            <div style="padding: 15px; background-color: #f8f9fa; border-radius: 6px; border-left: 4px solid #0038a8; margin-bottom: 25px;">
                                <p style="margin: 0; color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                                    @if($request->status === 'Pending')
                                        Your request is currently pending review. We will begin processing it shortly.
                                    @elseif($request->status === 'Verified')
                                        Your request has been verified and is ready for processing.
                                    @elseif($request->status === 'Processing')
                                        Your document is currently being prepared. This may take a few business days.
                                    @elseif($request->status === 'Ready')
                                        Your document is ready for pickup! Please visit the registrar's office during office hours.
                                    @elseif($request->status === 'Completed')
                                        Your request has been completed. Thank you for using the BNHS eDocument System!
                                    @elseif($request->status === 'Rejected')
                                        Unfortunately, your request has been rejected. Please check the admin remarks above for more information or contact the registrar's office.
                                    @else
                                        Your request status has been updated. Please check your dashboard for more details.
                                    @endif
                                </p>
                            </div>

                            <!-- Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #0038a8; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; text-align: center;">View Your Dashboard</a>
                                    </td>
                                </tr>
                            </table>

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
                                This is an automated message from the BNHS eDocument System. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
