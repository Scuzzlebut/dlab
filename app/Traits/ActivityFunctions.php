<?php

namespace App\Traits;

use App\Models\AttendanceType;
use App\Models\User;
use DateTime;
use App\Models\Staff;
use App\Models\Attendance;
use App\Traits\GlobalSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait ActivityFunctions {
    use GlobalSettings;

    /**
     * @param $minutes
     * @return int
     */
    public function minutesToFraction($minutes): int
    {
        return match ((int)$minutes) {
            15 => 25,
            30 => 50,
            45 => 75,
            default => 0,
        };
    }
}
