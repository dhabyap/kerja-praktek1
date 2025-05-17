<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Exports\BookingsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookingExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Booking::query()->with('user', 'unit.appartement');

        // Cek parameter langsung dari query string
        if ($request->has('nama')) {
            $query->where('nama', 'like', '%' . $request->nama . '%');
        }

        if ($request->has('tanggal_from')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_from);
        }

        if ($request->has('tanggal_until')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_until);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('filterMonth')) {
            $query->whereMonth('tanggal', $request->filterMonth);
        }

        if ($request->has('filterYear')) {
            $query->whereYear('tanggal', $request->filterYear);
        }

        return Excel::download(new BookingsExport($query->get()), 'bookings.xlsx');
    }
}
