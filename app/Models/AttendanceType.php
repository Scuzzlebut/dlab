<?php

namespace App\Models;

use App\Models\Attendance;

class AttendanceType extends BaseModel {
    protected $table = 'attendance_types';

    const CLOSURE_TYPE_ID=2;
    const OT_TYPE_ID=6;

    protected $fillable = [
        'type_name',
        'color',
    ];

    protected $hidden = [];

    protected $casts = [];

    public function attendances() {
        return $this->hasMany(Attendance::class, 'type_id', 'id');
    }
}
