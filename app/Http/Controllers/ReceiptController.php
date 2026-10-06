<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class ReceiptController extends Controller
{
    public function show(Payment $payment)
    {
        $payment->load(['household', 'duesType', 'recorder']);

        return view('public.receipt.show', [
            'payment' => $payment,
        ]);
    }
}
