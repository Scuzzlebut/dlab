<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Traits\ActivityFunctions;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SimpleRequest as Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller {
    use ActivityFunctions;

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

    public function update(Request $request, $id) {

    }

    public function store(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        $request->validate([
            'day' => 'required',
            'activity_type_id' => 'exists:activity_types,id',
            'project_id' => 'exists:projects,id',
            'timepicker' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $hours = Carbon::parse($request["timepicker"])->format("H");
        $minutes = Carbon::parse($request["timepicker"])->format("i");
        $minutes = $this->minutesToFraction($minutes);

        $worked_hours = (int)$hours+(float)("0.".$minutes);
        if($worked_hours == 0)
            return response()->json([
                'code'      => 1,
                'message'   => 'Inserire le ore lavorate'
            ]);

        try{
            $activity = new Activity();
            $activity->day = $request["day"];
            $activity->hours = $worked_hours;
            $activity->staff_id = $authUser->staff->id;
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

    public function delete(Request $request) {

    }
}
