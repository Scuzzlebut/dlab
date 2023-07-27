<?php

namespace App\Http\Controllers;

use App\Models\Paysheet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SimpleRequest as Request;

class PaysheetController extends Controller {
    public function index(Request $request) {
        $this->authorize('viewAny', Paysheet::class);

        $year = $request->year; //anno
        $month = $request->month; //mese
        $staff_ids = $request->staff_ids; //dipendenti

        $paysheets = Paysheet::with('attachments', 'staff', 'creator')->viewByRole();

        if (isset($month) && !empty($month)) {
            $paysheets->where('reference_month', $month);
        }
        if (isset($year) && !empty($year)) {
            $paysheets->where('reference_year', $year);
        }
        if (isset($staff_ids) && !empty($staff_ids)) {
            $paysheets->whereIn('staff_id', $staff_ids);
        }

        $paysheets = $this->doTheSearch($paysheets, $request);
        $paysheets = $this->doTheSort($paysheets, $request);
        $paysheets = $this->doThePagination($paysheets, $request);

        return response()->json($paysheets);
    }

    public function show($id) {
        $paysheet = Paysheet::with('attachments', 'staff', 'creator')->findOrFail($id);
        $this->authorize('view', $paysheet);
        return response()->json($paysheet);
    }

    public function setDownloaded(Request $request, $id) {
        $paysheet = Paysheet::findOrFail($id);

        $this->authorize('view', $paysheet);

        //mi assicuro che solamente il dipendente di quel cedolino possa impostarlo come scaricato (così evito gli admin)
        if (Auth::user()->staff->id == $paysheet->staff_id) {
            if (is_null($paysheet->downloaded_at)) {
                $paysheet->ip = get_client_real_ip();
                $paysheet->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
                $paysheet->downloaded_at = Carbon::now();
                $paysheet->save();

                return response()->json([
                    'code'      => 0,
                    'message'   => 'Download cedolino tracciato con successo',
                    'object'    => $paysheet,
                ]);
            } else {
                return response()->json([
                    'code'      => 1,
                    'message'   => 'Attenzione, download cedolino già tracciato',
                    'object'    => $paysheet,
                ]);
            }
        } else {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, utente loggato non autorizzato',
            ]);
        }
    }
}
