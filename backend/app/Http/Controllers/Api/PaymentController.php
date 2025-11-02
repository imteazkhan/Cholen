<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private $sslcommerz_store_id;
    private $sslcommerz_store_password;
    private $sslcommerz_is_sandbox;

    public function __construct()
    {
        $this->sslcommerz_store_id = config('sslcommerz.store_id');
        $this->sslcommerz_store_password = config('sslcommerz.store_password');
        $this->sslcommerz_is_sandbox = config('sslcommerz.is_sandbox', true);
    }

    /**
     * Initialize payment with SSLCommerz
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'amount' => 'required|numeric|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
        ]);

        try {
            $ride = Ride::findOrFail($request->ride_id);
            
            // Check if user owns this ride
            if ($ride->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ride'
                ], 403);
            }

            // Generate unique transaction ID
            $transaction_id = 'CHOLEN_' . time() . '_' . $ride->id;

            // Create payment record
            $payment = Payment::create([
                'ride_id' => $ride->id,
                'user_id' => auth()->id(),
                'transaction_id' => $transaction_id,
                'amount' => $request->amount,
                'currency' => 'BDT',
                'status' => 'pending',
                'payment_method' => 'sslcommerz',
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
            ]);

            // Prepare SSLCommerz payment data
            $post_data = [
                'store_id' => $this->sslcommerz_store_id,
                'store_passwd' => $this->sslcommerz_store_password,
                'total_amount' => $request->amount,
                'currency' => 'BDT',
                'tran_id' => $transaction_id,
                'success_url' => route('payment.success'),
                'fail_url' => route('payment.fail'),
                'cancel_url' => route('payment.cancel'),
                'ipn_url' => route('payment.ipn'),
                
                // Customer Information
                'cus_name' => $request->customer_name,
                'cus_email' => $request->customer_email,
                'cus_add1' => $ride->pickup_location,
                'cus_city' => 'Dhaka',
                'cus_country' => 'Bangladesh',
                'cus_phone' => $request->customer_phone,
                
                // Product Information
                'product_name' => 'Ride Payment - ' . $ride->pickup_location . ' to ' . $ride->dropoff_location,
                'product_category' => 'Transportation',
                'product_profile' => 'general',
                
                // Shipping Information
                'ship_name' => $request->customer_name,
                'ship_add1' => $ride->dropoff_location,
                'ship_city' => 'Dhaka',
                'ship_country' => 'Bangladesh',
                
                // Additional Information
                'value_a' => $ride->id, // Ride ID for reference
                'value_b' => auth()->id(), // User ID for reference
            ];

            // Get SSLCommerz API URL
            $sslcommerz_url = $this->sslcommerz_is_sandbox 
                ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
                : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';

            // Make request to SSLCommerz
            $response = Http::asForm()->post($sslcommerz_url, $post_data);
            $sslcommerz_response = $response->json();

            if ($sslcommerz_response['status'] === 'SUCCESS') {
                // Update payment with SSLCommerz session key
                $payment->update([
                    'gateway_session_key' => $sslcommerz_response['sessionkey'],
                    'gateway_response' => json_encode($sslcommerz_response)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment initialized successfully',
                    'data' => [
                        'payment_id' => $payment->id,
                        'transaction_id' => $transaction_id,
                        'gateway_url' => $sslcommerz_response['GatewayPageURL'],
                        'session_key' => $sslcommerz_response['sessionkey'],
                        'amount' => $request->amount,
                        'currency' => 'BDT'
                    ]
                ]);
            } else {
                // Payment initialization failed
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode($sslcommerz_response)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment initialization failed',
                    'error' => $sslcommerz_response['failedreason'] ?? 'Unknown error'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment initialization error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process cash payment
     */
    public function processCashPayment(Request $request)
    {
        $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $ride = Ride::findOrFail($request->ride_id);
            
            // Check if user owns this ride or is the driver
            if ($ride->user_id !== auth()->id() && $ride->driver_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ride'
                ], 403);
            }

            // Check if ride is completed
            if ($ride->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ride must be completed before payment'
                ], 400);
            }

            // Generate unique transaction ID for cash payment
            $transaction_id = 'CASH_' . time() . '_' . $ride->id;

            // Get user information for required fields
            $user = auth()->user();

            // Create payment record
            $payment = Payment::create([
                'ride_id' => $ride->id,
                'user_id' => $ride->user_id,
                'transaction_id' => $transaction_id,
                'amount' => $request->amount,
                'currency' => 'BDT',
                'payment_method' => 'cash',
                'status' => 'completed',
                'customer_name' => $user->name ?? 'Cash Payment Customer',
                'customer_email' => $user->email ?? 'cash@payment.local',
                'customer_phone' => $user->phone ?? '01700000000',
                'gateway_response' => json_encode([
                    'payment_type' => 'cash',
                    'processed_at' => now(),
                    'confirmed_by' => auth()->user()->role ?? 'user'
                ])
            ]);

            // Update ride payment status
            $ride->update([
                'payment_status' => 'paid',
                'final_price' => $request->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash payment processed successfully',
                'data' => [
                    'payment' => $payment,
                    'ride' => $ride
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cash payment processing failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Cash payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle successful payment
     */
    public function paymentSuccess(Request $request)
    {
        $transaction_id = $request->tran_id;
        $amount = $request->amount;
        $currency = $request->currency;

        try {
            $payment = Payment::where('transaction_id', $transaction_id)->first();
            
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found'
                ], 404);
            }

            // Verify payment with SSLCommerz
            $validation_response = $this->validatePayment($transaction_id, $amount, $currency);
            
            if ($validation_response['status'] === 'VALID') {
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'gateway_transaction_id' => $request->val_id,
                    'gateway_response' => json_encode($request->all()),
                    'completed_at' => now()
                ]);

                // Update ride status to paid
                $ride = $payment->ride;
                $ride->update([
                    'payment_status' => 'paid',
                    'final_price' => $amount
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed successfully',
                    'data' => [
                        'payment_id' => $payment->id,
                        'transaction_id' => $transaction_id,
                        'amount' => $amount,
                        'status' => 'completed'
                    ]
                ]);
            } else {
                // Payment validation failed
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode($validation_response)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment validation failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment success handling error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle failed payment
     */
    public function paymentFail(Request $request)
    {
        $transaction_id = $request->tran_id;

        try {
            $payment = Payment::where('transaction_id', $transaction_id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode($request->all())
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'data' => [
                    'transaction_id' => $transaction_id,
                    'status' => 'failed'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment fail handling error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment fail processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle cancelled payment
     */
    public function paymentCancel(Request $request)
    {
        $transaction_id = $request->tran_id;

        try {
            $payment = Payment::where('transaction_id', $transaction_id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'cancelled',
                    'gateway_response' => json_encode($request->all())
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment cancelled by user',
                'data' => [
                    'transaction_id' => $transaction_id,
                    'status' => 'cancelled'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment cancel handling error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment cancel processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle IPN (Instant Payment Notification)
     */
    public function paymentIPN(Request $request)
    {
        $transaction_id = $request->tran_id;
        $amount = $request->amount;
        $currency = $request->currency;

        try {
            $payment = Payment::where('transaction_id', $transaction_id)->first();
            
            if (!$payment) {
                Log::error('IPN: Payment record not found for transaction: ' . $transaction_id);
                return response('Payment not found', 404);
            }

            // Verify payment with SSLCommerz
            $validation_response = $this->validatePayment($transaction_id, $amount, $currency);
            
            if ($validation_response['status'] === 'VALID') {
                $payment->update([
                    'status' => 'completed',
                    'gateway_transaction_id' => $request->val_id,
                    'gateway_response' => json_encode($request->all()),
                    'completed_at' => now()
                ]);

                // Update ride payment status
                $ride = $payment->ride;
                $ride->update([
                    'payment_status' => 'paid',
                    'final_price' => $amount
                ]);

                Log::info('IPN: Payment completed for transaction: ' . $transaction_id);
                return response('Payment processed successfully', 200);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode($validation_response)
                ]);

                Log::error('IPN: Payment validation failed for transaction: ' . $transaction_id);
                return response('Payment validation failed', 400);
            }

        } catch (\Exception $e) {
            Log::error('IPN processing error: ' . $e->getMessage());
            return response('IPN processing failed', 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($transaction_id)
    {
        try {
            $payment = Payment::where('transaction_id', $transaction_id)
                ->with('ride')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'created_at' => $payment->created_at,
                    'completed_at' => $payment->completed_at,
                    'ride' => [
                        'id' => $payment->ride->id,
                        'pickup_location' => $payment->ride->pickup_location,
                        'dropoff_location' => $payment->ride->dropoff_location,
                        'status' => $payment->ride->status
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate payment with SSLCommerz
     */
    private function validatePayment($transaction_id, $amount, $currency)
    {
        $validation_url = $this->sslcommerz_is_sandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';

        $validation_data = [
            'val_id' => request('val_id'),
            'store_id' => $this->sslcommerz_store_id,
            'store_passwd' => $this->sslcommerz_store_password,
            'format' => 'json'
        ];

        $response = Http::asForm()->get($validation_url, $validation_data);
        return $response->json();
    }
}