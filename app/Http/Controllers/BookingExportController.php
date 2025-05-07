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
        $query = Booking::query()
            ->with('user', 'unit.appartement');

        $filters = $request->input('tableFilters', []);

        if (isset($filters['nama']) && !empty($filters['nama'])) {
            $query->where('nama', 'like', "%" . $filters['nama'] . "%");
        }

        if (isset($filters['tanggal_range']) && is_array($filters['tanggal_range'])) {
            $tanggalFrom = $filters['tanggal_range']['tanggal_from'] ?? null;
            $tanggalUntil = $filters['tanggal_range']['tanggal_until'] ?? null;

            if ($tanggalFrom) {
                $query->whereDate('tanggal', '>=', $tanggalFrom);
            }
            if ($tanggalUntil) {
                $query->whereDate('tanggal', '<=', $tanggalUntil);
            }
        }

        // Filter berdasarkan unit
        if (isset($filters['unit_id']) && !empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }

        // Filter berdasarkan user
        if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Ekspor ke Excel
        return Excel::download(new BookingsExport($query->get()), 'bookings.xlsx');
    }
}
