<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilityController extends Controller {
    public function getStaffTypes() {
        return DB::table('staff_types')->orderBy('type_name')->get();
    }

    public function getStaffRoles() {
        return DB::table('staff_roles')->orderBy('role_name')->get();
    }

    public function getAttendanceTypes() {
        return DB::table('attendance_types')->orderBy('type_name')->get();
    }

    public function getActivityTypes() {
        return ActivityType::query()->orderBy('title')->get();
    }

    public function getProjects() {
        return Project::query()->orderBy('title')->get();
    }
}
