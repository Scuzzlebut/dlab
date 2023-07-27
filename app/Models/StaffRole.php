<?php

namespace App\Models;

use App\Models\Staff;

class StaffRole extends BaseModel {
    protected $table = 'staff_roles';

    protected $fillable = [
        'role_name',
    ];

    protected $hidden = [];

    protected $casts = [];

    public function staff() {
        return $this->hasMany(Staff::class, 'role_id', 'id');
    }
}
