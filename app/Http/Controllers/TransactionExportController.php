<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Exports\TransactionExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionExportController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->input('tableFilters', []);
        
        $query = Transaction::with(['booking', 'unit', 'user']);

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

        if (!empty($filters['unit_id']['value'])) {
            $query->where('unit_id', $filters['unit_id']['value']);
        }
        if (!empty($filters['user_id']['value'])) {
            $query->where('user_id', $filters['user_id']['value']);
        }

        $transactions = $query->get();

        return Excel::download(new TransactionExport($transactions), 'transactions.xlsx');
    }

}
