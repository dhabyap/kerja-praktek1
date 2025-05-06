<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{

    public function download($id)
    {
        $booking = Booking::with(['unit', 'user', 'transactions'])->findOrFail($id);

        $pdf = Pdf::loadView('invoices.booking', compact('booking'));

        return $pdf->download("invoice-{$booking->kode_invoice}.pdf");
    }
}
