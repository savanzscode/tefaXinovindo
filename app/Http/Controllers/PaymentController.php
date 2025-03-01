<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function getSnapToken($order_id)
    {
        $order = Order::find($order_id);

        if (!$order || $order->payment_status !== 'belum_dibayar') {
            return response()->json(['error' => 'Order tidak ditemukan atau sudah dibayar'], 404);
        }

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        
        if (!$order->snap_token) {
            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $order->total,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $order->snap_token = $snapToken;
                $order->save();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal mendapatkan snap token'], 500);
            }
        }

        return response()->json(['snap_token' => $order->snap_token]);
    }

    public function updatePaymentStatus(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);

        if ($request->transaction_status === 'settlement' || $request->transaction_status === 'capture') {
            $order->payment_status = 'sudah_dibayar';
            $order->save();

            return response()->json(['success' => true, 'message' => 'Status pembayaran diperbarui.']);
        }

        return response()->json(['success' => false, 'message' => 'Transaksi belum selesai.']);
    }

    public function handleMidtransNotification(Request $request)
    {

        $notif = $request->all();


        $order = Order::where('id', $notif['order_id'])->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }


        if (in_array($notif['transaction_status'], ['settlement', 'capture'])) {
            $order->payment_status = 'sudah_dibayar';
        } elseif ($notif['transaction_status'] == 'pending') {
            $order->payment_status = 'pending';
        } elseif (in_array($notif['transaction_status'], ['cancel', 'deny', 'expire', 'failure'])) {
            $order->payment_status = 'belum_dibayar';
        }

        $order->save();

        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil diproses.']);
    }
}
