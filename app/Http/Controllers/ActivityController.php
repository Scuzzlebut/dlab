<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Staff;
use App\Mail\GenericMail;
use App\Models\Attachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SimpleRequest as Request;

class ActivityController extends Controller {

    public function index(Request $request) {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with('project', 'staff', 'type');

        $activities = $this->doTheSearch($activities, $request);
        $activities = $this->doTheSort($activities, $request);
        $activities = $this->doThePagination($activities, $request);

        return response()->json($activities);
    }

    public function show($id) {
        $activity = Activity::with(['project', 'staff', 'type'])->findOrFail($id);
        $this->authorize('view', $activity);
        return response()->json($activity);
    }

    public function update(Request $request, $id) {

    }

    public function store(Request $request) {

    }
}
