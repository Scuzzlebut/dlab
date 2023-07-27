<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use Illuminate\Support\Carbon;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\SimpleRequest as Request;

class AttendanceController extends Controller {
    public function accepted(Request $request, $id) {
        $attendance = Attendance::findOrFail($id);

        $this->authorize('approval', $attendance);

        $attendance->accepted = 1;
        $attendance->accepted_datetime = Carbon::now();
        $attendance->accepted_by = Auth::user()->staff->id;
        $attendance->save();

        return response()->json([
            'code'      => 0,
            'message'   => 'Richiesta di assenza approvata con successo.',
            'object'    => $attendance,
        ]);
    }

    public function reset(Request $request, $id) {
        $attendance = Attendance::findOrFail($id);

        $this->authorize('reset', $attendance);

        $attendance->accepted = 0;
        $attendance->accepted_datetime = null;
        $attendance->accepted_by = null;
        $attendance->save();

        return response()->json([
            'code'      => 0,
            'message'   => 'Flag di approvazione reimpostato con successo.',
            'object'    => $attendance,
        ]);
    }

    public function export(Request $request) {
        $this->authorize('export', Attendance::class);

        // estraggo solo le assenze accettate, utile per la generazione delle buste paga
        $items = Attendance::select('id')->viewOnlyAccepted();

        if (isset($request->month) && !empty($request->month)) {
            $month = $request->month;
            $items->where(function ($q) use ($month) {
                $q->whereMonth('date_start', $month);
                $q->whereMonth('date_end', '=', $month, 'or');
            });
        }
        if (isset($request->year) && !empty($request->year)) {
            $year = $request->year;
            $items->where(function ($q) use ($year) {
                $q->whereYear('date_start', $year);
                $q->whereYear('date_end', '=', $year, 'or');
            });
        }
        /*if (isset($request->accepted)) {
            $items->where('accepted', filter_var($request->accepted, FILTER_VALIDATE_BOOLEAN));
        }*/
        if (isset($request->type) && !empty($request->type)) {
            $items->where('type_id', $request->type);
        }
        if (isset($request->staff_ids) && !empty($request->staff_ids)) {
            $items->whereIn('staff_id', $request->staff_ids);
        }
        if (isset($request->staff_types) && !empty($request->staff_types)) {
            $items->whereHas('staff', function ($q) use ($request) {
                $q->whereIn('type_id', $request->staff_types);
            });
        }

        $items = $this->doTheSearch($items, $request);

        $filename = 'Report_presenze.xls';

        $excel = (new AttendanceExport($filename, $items))->download($filename, \Maatwebsite\Excel\Excel::XLS, ['Content-Type' => 'application/vnd.ms-excel',]);

        return $excel;
    }
}
