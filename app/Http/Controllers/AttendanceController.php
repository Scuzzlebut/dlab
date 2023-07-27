<?php

namespace App\Http\Controllers;

use App\Models\AttendanceType;
use App\Models\Staff;
use App\Models\Attachment;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use App\Traits\AttendanceFunctions;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SimpleRequest as Request;

class AttendanceController extends Controller {
    use AttendanceFunctions;

    public function index(Request $request) {
        $this->authorize('viewAny', Attendance::class);

        $year = $request->year; //anno
        $month = $request->month; //mese
        $accepted = $request->accepted; //stato approvazione
        $type_id = $request->type; //tipo assenza
        $staff_ids = $request->staff_ids; //dipendenti
        $staff_types = $request->staff_types; //categorie dipendenti

        $attendances = Attendance::with('applicant', 'staff', 'approver')->viewByRole()->viewActiveStaff();

        if (isset($month) && !empty($month)) {
            $attendances->where(function ($q) use ($month) {
                $q->whereMonth('date_start', $month);
                $q->whereMonth('date_end', '=', $month, 'or');
            });
        }
        if (isset($year) && !empty($year)) {
            $attendances->where(function ($q) use ($year) {
                $q->whereYear('date_start', $year);
                $q->whereYear('date_end', '=', $year, 'or');
            });
        }
        if (isset($accepted)) {
            $attendances->where('accepted', filter_var($accepted, FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($type_id) && !empty($type_id)) {
            $attendances = $attendances->where('type_id', $type_id);
        }
        if (isset($staff_ids) && !empty($staff_ids)) {
            $attendances->whereIn('staff_id', $staff_ids);
        }
        if (isset($staff_types) && !empty($staff_types)) {
            $attendances->whereHas('staff', function ($q) use ($staff_types) {
                $q->whereIn('type_id', $staff_types);
            });
        }

        $attendances = $this->doTheSearch($attendances, $request);
        $attendances = $this->doTheSort($attendances, $request);
        $attendances = $this->doThePagination($attendances, $request);

        return response()->json($attendances);
    }

    public function calendar(Request $request) {
        $start = substr($request->date_start, 0, 19); //required
        $end = substr($request->date_end, 0, 19); //required
        $staff_ids = $request->staff_ids; //dipendente
        $type_id = $request->type; //tipologia assenza
        $onlyAccepted = $request->accepted; //stato approvazione

        $calendar = [];

        $attendances = Attendance::where(function ($q) use ($start, $end) {
            //q1 e q2 servono per verificare che una delle due date in input sia esterna all'intervallo dell'attendance
            $q->where(function ($q1) use ($start, $end) {
                $q1->whereDate('date_start', '>=', format_date_for_db($start));
                $q1->whereDate('date_start', '<=', format_date_for_db($end));
            });
            $q->orWhere(function ($q2) use ($start, $end) {
                $q2->whereDate('date_end', '>=', format_date_for_db($start));
                $q2->whereDate('date_end', '<=', format_date_for_db($end));
            });
            //q3 serve per verificare che l'intervallo in input sia interno all'intervallo dell'attendance (es: visualizzazione 4gg interna all'attendance)
            $q->orWhere(function ($q3) use ($start, $end) {
                $q3->whereDate('date_start', '<=', format_date_for_db($start));
                $q3->whereDate('date_end', '>=', format_date_for_db($end));
            });
        })->with('staff', 'type')->viewByRole()->viewActiveStaff();

        if (isset($staff_ids) && !empty($staff_ids)) {
            $attendances = $attendances->whereIn('staff_id', $staff_ids);
        }
        if (isset($type_id) && !empty($type_id)) {
            $attendances = $attendances->where('type_id', $type_id);
        }
        if (isset($onlyAccepted)) {
            $attendances = $attendances->where('accepted', filter_var($onlyAccepted, FILTER_VALIDATE_BOOLEAN));
        }

        foreach ($attendances->get() as $attendance) {
            array_push($calendar, [
                'attendance_id' => $attendance->id,
                'start'         => $attendance->date_start->format('Y-m-d H:i:s'),
                'end'           => $attendance->date_end->format('Y-m-d H:i:s'),
                'type'          => $attendance->type->type_name,
                'color'         => $attendance->type->color,
                'staff_fname'   => $attendance->staff->fullname,
                'staff_code'    => $attendance->staff->code,
                'accepted'      => $attendance->accepted
            ]);
        }

        return response()->json($calendar);
    }

    public function store(Request $request) {
        $authUser = Auth::user();

        if (empty($request->staff_id)) {
            $request['staff_id'] = $authUser->staff->id;
        }
        $request['applicant_id'] = $authUser->staff->id;

        $this->authorize('create', [Attendance::class, $request->staff_id]);

        $request->validate([
            'staff_id' => 'nullable|exists:staff,id',
            'date_start' => 'required',
            'date_end' => 'required|after_or_equal:date_start',
            'type_id' => 'exists:attendance_types,id',
            'sick_note' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:255',
            //questi tre seguenti potrebbero tornare utili nel caso di richiesta eseguita e subito approvata da manager/admin
            'accepted_by' => 'nullable|exists:staff,id',
            'accepted_datetime' => 'nullable',
            'accepted' => 'nullable|boolean',
        ]);

        //per sicurezza rimuovo eventuali parametri nel caso la richiesta provenisse da un dipendente
        if ($authUser->staff->role->role_slug == 'employee') {
            $request_data = $request->except([
                'accepted_by',
                'accepted_datetime',
                'accepted'
            ]);
        } else {
            $request_data = $request->all();
        }

        if($request["type_id"]===AttendanceType::OT_TYPE_ID){
            if(!Carbon::parse($request["date_start"])->isSameDay($request["date_end"]))
                return response()->json([
                    'code'      => 1,
                    'message'   => 'Non è possibile inserire straordinari a cavallo di più giorni'
                ]);
            if(!$this->isIntervalValidOvertime($request["date_start"],$request["date_end"],$request_data['staff_id']))
                return response()->json([
                    'code'      => 1,
                    'message'   => 'Attenzione, questo periodo è compreso nelle normali ore lavorative.'
                ]);
        }

        //controllo che in quel periodo (data+orario) non ci siano già altre richieste d'assenza dello stesso dipendente
        $is_already_in = $this->isAttendanceAlreadyPresent($request_data['date_start'], $request_data['date_end'], 'datetime', $request['staff_id']);
        if ($is_already_in) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, in questo periodo esistono già altre richieste.'
            ]);
        }

        $request_data['date_start'] = Carbon::parse($request_data['date_start']);
        $request_data['date_end'] = Carbon::parse($request_data['date_end']);

        if (!empty($request_data['accepted_datetime'])) {
            $request_data['accepted_datetime'] = Carbon::parse($request_data['accepted_datetime']);
        }

        //verifico che le date non siano a cavallo di un mese, se così fosse allora splitto la richiesta in più sottorichieste
        $diffMonths = $request_data['date_start']->copy()->floorMonth()->diffInMonths($request_data['date_end']->copy()->floorMonth());
        if ($diffMonths > 0) {
            $diffMonths--;
            $split_attendances = $this->splitAttendance($request_data['date_start'], $request_data['date_end'], $request['staff_id'], $diffMonths);
            foreach ($split_attendances as $split_att) {
                $request_data['date_start'] = $split_att['date_start'];
                $request_data['date_end'] = $split_att['date_end'];
                $request_data['hours'] = $split_att['hours'];
                $result = Attendance::create($request_data);
                if (!isset($attendance)) {
                    $attendance = $result;
                }
            }
        } else {
            //calcolo le ore dell'assenza (non servono controlli, sono in creazione)
            $request_data['hours'] = $this->calcAttendanceHours($request_data['date_start'], $request_data['date_end'], $request['staff_id']);
            $attendance = Attendance::create($request_data);
        }

        return response()->json([
            'code'      => 0,
            'message'   => 'Richiesta di assenza creata con successo.',
            'object'    => $attendance
        ]);
    }

    public function show(Request $request, $id) {
        $attendance = Attendance::with(['applicant', 'staff', 'approver', 'attachments'])->findOrFail($id);

        $this->authorize('view', $attendance);

        return response()->json($attendance);
    }

    public function update(Request $request, $id) {

        $attendance = Attendance::findOrFail($id);

        $this->authorize('update', $attendance);

        $request->validate([
            'date_start' => 'required',
            'date_end' => 'required|after_or_equal:date_start',
            'type_id' => 'exists:attendance_types,id',
            'sick_note' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:255',
            //questi tre seguenti potrebbero tornare utili nel caso di richiesta eseguita e subito approvata da manager/admin
            'accepted_by' => 'nullable|exists:staff,id',
            'accepted_datetime' => 'nullable',
            'accepted' => 'nullable|boolean',
        ]);

        //ricalcolo le ore dell'assenza solo se questa non è già stata accettata
        if (!$attendance->accepted && is_null($attendance->accepted_datetime)) {
            $request['hours'] = $this->calcAttendanceHours($request['date_start'], $request['date_end'], $attendance->staff_id);
        }

        //per sicurezza rimuovo eventuali parametri nel caso la richiesta provenisse da un dipendente
        if (Auth::user()->staff->role->role_slug == 'employee') {
            $request_data = $request->except([
                'accepted_by',
                'accepted_datetime',
                'accepted'
            ]);
        } else {
            $request_data = $request->all();
        }

        if($request["type_id"]===AttendanceType::OT_TYPE_ID){
            if(!Carbon::parse($request["date_start"])->isSameDay($request["date_end"]))
                return response()->json([
                    'code'      => 1,
                    'message'   => 'Non è possibile inserire straordinari a cavallo di più giorni'
                ]);
            if(!$this->isIntervalValidOvertime($request["date_start"],$request["date_end"],$request_data['staff_id']))
                return response()->json([
                    'code'      => 1,
                    'message'   => 'Attenzione, questo periodo è compreso nelle normali ore lavorative.'
                ]);
        }

        //controllo che in quel periodo (data+orario) non ci siano già altre richieste d'assenza dello stesso dipendente
        $is_already_in = $this->isAttendanceAlreadyPresent($request_data['date_start'], $request_data['date_end'], 'datetime', $request['staff_id'], $id);
        if ($is_already_in) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, in questo periodo esistono già altre richieste.'
            ]);
        }


        //per sicurezza sovrascrivo questi due valori con quelli della richiesta iniziale così da essere sicuri non cambino mai
        $request_data['staff_id'] = $attendance->staff_id;
        $request_data['applicant_id'] = $attendance->applicant_id;

        $request_data['date_start'] = Carbon::parse($request_data['date_start']);
        $request_data['date_end'] = Carbon::parse($request_data['date_end']);

        //verifico che le date non siano a cavallo di un mese, se così fosse allora splitto la richiesta in più sottorichieste
        $diffMonths = $request_data['date_start']->copy()->floorMonth()->diffInMonths($request_data['date_end']->copy()->floorMonth());
        if ($diffMonths > 0) {
            //se sono a cavallo del mese allora per comodità e velocità cancello la richiesta in modifica così da ricrearla fino a fine mese
            $attendance->delete();
            //slitto in più richieste
            $diffMonths--;
            $split_attendances = $this->splitAttendance($request_data['date_start'], $request_data['date_end'], $request['staff_id'], $diffMonths);
            foreach ($split_attendances as $split_att) {
                $request_data['date_start'] = $split_att['date_start'];
                $request_data['date_end'] = $split_att['date_end'];
                $request_data['hours'] = $split_att['hours'];
                $result = Attendance::create($request_data);
                if (!isset($attendance)) {
                    $attendance = $result;
                }
            }
        } else {
            //ricalcolo le ore dell'assenza
            $request_data['hours'] = $this->calcAttendanceHours($request_data['date_start'], $request_data['date_end'], $request['staff_id']);
            $attendance->fill($request_data);
            $attendance->save();
        }

        return response()->json([
            'code'      => 0,
            'message'   => 'Richiesta di assenza modificata con successo.',
            'object'    => $attendance,
        ]);
    }

    public function destroy($id) {
        $attendance = Attendance::findOrFail($id);

        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response()->json([
            'code'      => 0,
            'message'   => 'Richiesta di assenza cancellata con successo.',
            'object'    => $attendance,
        ]);
    }

    public function uploadAttachment(Request $request, $id) {
        $attendance = Attendance::findOrFail($id);

        //riutilizzo la policy update: l'admin può caricare documenti a tutti
        //il manager solo ai suoi collaboratori (e a se stesso) e il dipendente solo a se stesso
        //ma solo se la richiesta non è stata ancora approvata
        $this->authorize('update', $attendance);

        $request->validate([
            'attachment' => 'required|file|mimes:png,jpg,jpeg,gif,svg,pdf,doc,docx|max:5000',
            'title' => 'required|max:100',
            'description' => 'nullable|max:255',
        ]);

        if ($file = $request->file('attachment')) {
            $filepath = $file->store(Attachment::getFilesPath(Attendance::class), Attachment::getAppEnvDisk());
            $filename = $file->getClientOriginalName();
            $filetype = $file->getClientMimeType();
            $thumbnail_path = Attachment::createThumbFromFilename($filename, $filepath, $filetype);

            $params = [
                'title'             => $request->title,
                'description'       => $request->description,
                'filename'          => $filename,
                'filetype'          => $filetype,
                'filepath'          => $filepath,
                'filesize'          => $file->getSize(),
                'model_id'          => $id,
                'model_type'        => Attendance::class,
                'category'          => $request->category,
                'thumbnail_path'    => $thumbnail_path
            ];

            $attachment = Attachment::create($params);

            return response()->json([
                'code'      => 0,
                'message'   => 'Documento caricato con successo.',
                'object'    => $attachment,
            ]);
        } else {
            return response()->json([
                'code'      => 1,
                'message'   => 'Houston, we have a problem!',
            ]);
        }
    }
}
