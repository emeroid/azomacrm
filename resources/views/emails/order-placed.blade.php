<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Thank You for Your Order!</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; padding: 48px 16px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 640px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">
                        <tr>
                            <td style="background: linear-gradient(to right, #3b82f6, #6366f1); padding: 24px; text-align: center;">
                                <h1 style="font-size: 24px; font-weight: bold; color: #ffffff; margin: 0;">
                                    Order Confirmed! 🎉
                                </h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 32px;">
                                <p style="font-size: 18px; font-weight: 500; color: #1f2937; margin-bottom: 24px;">Dear {{ $order->full_name }},</p>
                                
                                <div style="color: #374151; margin-bottom: 32px;">
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        Thank you for your order! We've received your order <strong style="font-weight: 600;">#{{ $order->order_number }}</strong> and it is now being processed.
                                    </p>
                                    
                                    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                                        <h2 style="font-size: 18px; color: #1e40af; margin-top: 0; margin-bottom: 16px;">Order Summary</h2>
                                        
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
                                                    <strong>Shipping Address:</strong>
                                                </td>
                                                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                                    {{ $order->address }}, {{ $order->state }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 12px;">
                                                    <strong>Contact:</strong>
                                                </td>
                                                <td style="padding-top: 12px; text-align: right;">
                                                    {{ $order->mobile }}
                                                    @if($order->phone)
                                                        / {{ $order->phone }}
                                                    @endif
                                                    @if($order->email)
                                                        <br>{{ $order->email }}
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        Our team will now verify your order details and prepare it for shipment. You'll receive another notification once your order is on its way.
                                    </p>
                                    
                                    <p style="margin-bottom: 16px; line-height: 1.5;">
                                        If you have any questions about your order, please contact our customer service team with your order number ready.
                                    </p>
                                </div>
                                
                                <p style="color: #4b5563; margin-bottom: 0;">Thank you for choosing us,</p>
                                <p style="color: #111827; font-weight: 500; margin-bottom: 4px;">Customer Service Team</p>
                                <p style="color: #374151;">We're here to serve you better.</p>
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