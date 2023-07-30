<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Traits\GlobalSettings;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SimpleRequest as Request;

class ActivityController extends Controller {
    use GlobalSettings;

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

    public function store(Request $request) {

    }

    public function delete(Request $request) {

    }
}
