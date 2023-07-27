<?php

namespace App\Models;

use App\Models\Staff;

class StaffType extends BaseModel {
    protected $table = 'staff_types';

    protected $fillable = [
        'type_name',
    ];

    protected $hidden = [];

    protected $casts = [];

    public function staff() {
        return $this->hasMany(Staff::class, 'type_id', 'id');
    }
}
