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
        $query = Transaction::with(['booking', 'unit', 'user']);

        // Filter berdasarkan tanggal dari filterMonth & filterYear
        $filterMonth = $request->query('filterMonth');
        $filterYear = $request->query('filterYear');

        if ($filterMonth && $filterYear) {
            // Filter berdasarkan bulan dan tahun
            $query->whereYear('tanggal', $filterYear)
                ->whereMonth('tanggal', $filterMonth);
        }

        // Filter unit_id langsung dari query param
        $unitId = $request->query('unit_id');
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        // Kalau mau filter user_id juga bisa tambahkan
        $userId = $request->query('user_id');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $transactions = $query->get();

        return Excel::download(new TransactionExport($transactions), 'transactions.xlsx');
    }


}
