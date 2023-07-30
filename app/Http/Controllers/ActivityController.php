<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AttendanceType;
use App\Traits\ActivityFunctions;
use App\Traits\AttendanceFunctions;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SimpleRequest as Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller {
    use ActivityFunctions, AttendanceFunctions;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with('project', 'staff', 'type');
        $activities = $this->doTheSearch($activities, $request);
        $activities = $this->doTheSort($activities, $request);
        $activities = $this->doThePagination($activities, $request);

        return response()->json($activities);
    }

    /**
     * @throws AuthorizationException
     */
    public function show($id): JsonResponse
    {
        $activity = Activity::with(['project', 'staff', 'type'])->findOrFail($id);
        $this->authorize('view', $activity);
        return response()->json($activity);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('update', $activity);

        $authUser_id = Auth::user()->staff->id;
        $request->validate([
            'day' => 'required',
            'activity_type_id' => 'exists:activity_types,id',
            'project_id' => 'exists:projects,id',
            'timepicker' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        //controllo se l'azienda è chiusa in quel giorno
        $is_activity_closed = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', null,null,[AttendanceType::CLOSURE_TYPE_ID]);
        if ($is_activity_closed) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, nella data selezionata l\'azienda è chiusa!'
            ]);
        }
        //controllo se il dipendente è in ferie
        $is_staff_on_vacation = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', $authUser_id,null,[AttendanceType::VACATION_TYPE_ID]);
        if ($is_staff_on_vacation) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, Nella data selezionata sei in ferie.'
            ]);
        }

        $day = Carbon::parse($request["day"]);
        $hours = Carbon::parse($request["timepicker"])->format("H");
        $minutes = Carbon::parse($request["timepicker"])->format("i");
        $minutes = $this->minutesToFraction($minutes);

        $worked_hours = (int)$hours+(float)("0.".$minutes);
        if($worked_hours == 0)
            return response()->json([
                'code'      => 1,
                'message'   => 'Inserire le ore lavorate'
            ]);

        //calcolo ore lavorative base
        $base_hours = $this->calcAttendanceHours($day->format("Y-m-d H:i:s"), $day->addDay()->format("Y-m-d H:i:s"),$authUser_id);
        //calcolo ore attività già registrate
        $results = Activity::where("staff_id",$authUser_id)->where("day",$request["day"])->where("id","<>",$activity->id)->get();
        $activity_hours = 0;
        foreach ($results as $result)
            $activity_hours+=$result->hours;
        //calcolo ore assenze
        $results = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', $authUser_id,null,[AttendanceType::ABSENSE_TYPE_ID,AttendanceType::SICKNESS_TYPE_ID,AttendanceType::TIMEOFF_TYPE_ID],true);
        $missing_hours = 0;
        foreach ($results as $result)
            $missing_hours+=$result->hours;
        //calcolo ore straordinario
        $results = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', $authUser_id,null,[AttendanceType::OT_TYPE_ID],true);
        $ot_hours = 0;
        foreach ($results as $result)
            $ot_hours+=$result->hours;
        //calcolo ore libere residue
        $workable_hours = $base_hours-$missing_hours-$activity_hours+$ot_hours;

        if($workable_hours < $worked_hours)
            return response()->json([
                'code'      => 1,
                'message'   => "Hai ".$workable_hours." ore lavorative assegnabili, aggiungi uno straordinario per incrementarle"
            ]);

        try{
            $activity->day = $request["day"];
            $activity->hours = $worked_hours;
            $activity->staff_id = $authUser_id;
            $activity->project_id = $request["project_id"];
            $activity->activity_type_id = $request["activity_type_id"];
            $activity->note = $request["note"];
            $activity->save();
        }
        catch(\Exception $e){
            return response()->json([
                'code'      => 1,
                'message'   => 'Errore di sistema'//$e->getMessage()
            ]);
        }
        return response()->json([
            'code'      => 0,
            'message'   => 'Attività modificata con successo',
            'object'    => $activity
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser_id = Auth::user()->staff->id;
        $request->validate([
            'day' => 'required',
            'activity_type_id' => 'exists:activity_types,id',
            'project_id' => 'exists:projects,id',
            'timepicker' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        //controllo se l'azienda è chiusa in quel giorno
        $is_activity_closed = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', null,null,[AttendanceType::CLOSURE_TYPE_ID]);
        if ($is_activity_closed) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, nella data selezionata l\'azienda è chiusa!'
            ]);
        }
        //controllo se il dipendente è in ferie
        $is_staff_on_vacation = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', $authUser_id,null,[AttendanceType::VACATION_TYPE_ID]);
        if ($is_staff_on_vacation) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Attenzione, Nella data selezionata sei in ferie.'
            ]);
        }

        $day = Carbon::parse($request["day"]);
        $hours = Carbon::parse($request["timepicker"])->format("H");
        $minutes = Carbon::parse($request["timepicker"])->format("i");
        $minutes = $this->minutesToFraction($minutes);

        $worked_hours = (int)$hours+(float)("0.".$minutes);
        if($worked_hours == 0)
            return response()->json([
                'code'      => 1,
                'message'   => 'Inserire le ore lavorate'
            ]);

        //calcolo ore lavorative base
        $base_hours = $this->calcAttendanceHours($day->format("Y-m-d H:i:s"), $day->addDay()->format("Y-m-d H:i:s"),$authUser_id);
        //calcolo ore attività già registrate
        $results = Activity::where("staff_id",$authUser_id)->where("day",$request["day"])->get();
        $activity_hours = 0;
        foreach ($results as $result)
            $activity_hours+=$result->hours;
        //calcolo ore assenze
        $results = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', $authUser_id,null,[AttendanceType::ABSENSE_TYPE_ID,AttendanceType::SICKNESS_TYPE_ID,AttendanceType::TIMEOFF_TYPE_ID],true);
        $missing_hours = 0;
        foreach ($results as $result)
            $missing_hours+=$result->hours;
        //calcolo ore straordinario
        $results = $this->isAttendanceAlreadyPresent($request["day"], $request["day"], 'date', $authUser_id,null,[AttendanceType::OT_TYPE_ID],true);
        $ot_hours = 0;
        foreach ($results as $result)
            $ot_hours+=$result->hours;
        //calcolo ore libere residue
        $workable_hours = $base_hours-$missing_hours-$activity_hours+$ot_hours;

        if($workable_hours < $worked_hours)
            return response()->json([
                'code'      => 1,
                'message'   => "Hai ".$workable_hours." ore lavorative assegnabili, aggiungi uno straordinario per incrementarle"
            ]);

        try{
            $activity = new Activity();
            $activity->day = $request["day"];
            $activity->hours = $worked_hours;
            $activity->staff_id = $authUser_id;
            $activity->project_id = $request["project_id"];
            $activity->activity_type_id = $request["activity_type_id"];
            $activity->note = $request["note"];
            $activity->save();
        }
        catch(\Exception $e){
            return response()->json([
                'code'      => 1,
                'message'   => 'Errore di sistema'//$e->getMessage()
            ]);
        }
        return response()->json([
            'code'      => 0,
            'message'   => 'Attività creata con successo',
            'object'    => $activity
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): JsonResponse
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('delete', $activity);

        $activity->delete();

        return response()->json([
            'code'      => 0,
            'message'   => 'Attività cancellata con successo.',
            'object'    => $activity,
        ]);
    }
}
