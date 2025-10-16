<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>New Order Assignment</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; padding: 48px 16px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 640px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">
                        <tr>
                            <td style="background: linear-gradient(to right, #f59e0b, #d97706); padding: 24px; text-align: center;">
                                <h1 style="font-size: 24px; font-weight: bold; color: #ffffff; margin: 0;">
                                    New Order Assigned! 📋
                                </h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 32px;">
                                <p style="font-size: 18px; font-weight: 500; color: #1f2937; margin-bottom: 24px;">Hello {{ $order->callAgent->name }},</p>
                                
                                <div style="color: #374151; margin-bottom: 32px;">
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        A new order has been assigned to you for processing. Please review the order details and proceed with the next steps.
                                    </p>
                                    
                                    <div style="background-color: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                                        <h2 style="font-size: 18px; color: #92400e; margin-top: 0; margin-bottom: 16px;">Order Details</h2>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Order Number:</strong>
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
                                            <tr>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                    <strong>Contact:</strong>
                                                </td>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                    {{ $order->mobile }}
                                                    @if($order->phone)
                                                        / {{ $order->phone }}
                                                    @endif
                                                    @if($order->email)
                                                        <br>{{ $order->email }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 12px;">
                                                    <strong>Shipping Address:</strong>
                                                </td>
                                                <td style="padding-top: 12px; text-align: right;">
                                                    {{ $order->address }}, {{ $order->state }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        Please contact the customer to confirm the order details and proceed with the verification process.
                                    </p>
                                    
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        You can access this order from your dashboard to update its status and add any relevant notes.
                                    </p>
                                </div>
                                
                                <div style="text-align: center; margin-bottom: 32px;">
                                    <a href="{{ url('/admin/orders') }}" style="display: inline-block; padding: 12px 24px; border-radius: 6px; background: linear-gradient(to right, #f59e0b, #d97706); color: #ffffff; font-weight: 500; text-decoration: none;">
                                        View Order Details
                                        <svg style="display: inline-block; vertical-align: middle; margin-left: 8px; margin-right: -4px;" width="20" height="20" viewBox="0 0 20 20" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                                
                                <p style="color: #4b5563; margin-bottom: 0;">Best regards,</p>
                                <p style="color: #111827; font-weight: 500; margin-bottom: 4px;">Operations Team</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px; text-align: center;">
                                <p style="font-size: 12px; color: #6b7280; margin-top: 16px;">
                                    &copy; {{ date('Y') }} {{env('APP_NAME')}}. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>