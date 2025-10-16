<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>New Order Notification</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; padding: 48px 16px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 640px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">
                        <tr>
                            <td style="background: linear-gradient(to right, #8b5cf6, #7c3aed); padding: 24px; text-align: center;">
                                <h1 style="font-size: 24px; font-weight: bold; color: #ffffff; margin: 0;">
                                    New Sale! 🎯
                                </h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 32px;">
                                <p style="font-size: 18px; font-weight: 500; color: #1f2937; margin-bottom: 24px;">Hello {{ $order->marketer->name }},</p>
                                
                                <div style="color: #374151; margin-bottom: 32px;">
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        Great news! A new order has been placed under your affiliate code. Your marketing efforts are paying off!
                                    </p>
                                    
                                    <div style="background-color: #faf5ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                                        <h2 style="font-size: 18px; color: #5b21b6; margin-top: 0; margin-bottom: 16px;">Order Details</h2>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Your Order Number:</strong>
                                                </td>
                                                <td style="padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                    {{ $order->order_number }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Product(s):</strong>
                                                </td>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                    {{ $order->product_name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Customer:</strong>
                                                </td>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                    {{ $order->full_name }}
                                                </td>
                                            </tr>
                                            {{-- <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Sale Value:</strong>
                                                </td>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                    
                                                </td>
                                            </tr> --}}
                                            {{-- <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Commission:</strong>
                                                </td>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                </td>
                                            </tr> --}}
                                            <tr>
                                                <td style="padding-top: 12px;">
                                                    <strong>Date & Time:</strong>
                                                </td>
                                                <td style="padding-top: 12px; text-align: right;">
                                                    {{ $order->created_at->format('M j, Y \a\t g:i A') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        This sale will contribute to your monthly performance metrics and commission earnings. Keep up the great work!
                                    </p>
                                    
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        You can track this order's progress and view your performance statistics through your marketer dashboard.
                                    </p>
                                </div>
                                
                                <div style="text-align: center; margin-bottom: 32px;">
                                    <a href="{{ url('/admin/orders') }}" style="display: inline-block; padding: 12px 24px; border-radius: 6px; background: linear-gradient(to right, #8b5cf6, #7c3aed); color: #ffffff; font-weight: 500; text-decoration: none;">
                                        View Your Dashboard
                                        <svg style="display: inline-block; vertical-align: middle; margin-left: 8px; margin-right: -4px;" width="20" height="20" viewBox="0 0 20 20" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                                
                                <p style="color: #4b5563; margin-bottom: 0;">Keep up the great work,</p>
                                <p style="color: #111827; font-weight: 500; margin-bottom: 4px;">Sales & Marketing Team</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px; text-align: center;">
                                <p style="font-size: 12px; color: #6b7280; margin-top: 16px;">
                                    &copy; {{ date('Y') }} {{env("APP_NAME")}}. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>