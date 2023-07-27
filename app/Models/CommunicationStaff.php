<?php

namespace App\Models;

use App\Models\Communication;

class CommunicationStaff extends BaseModel {
    protected $table = 'communication_staff';

    protected $fillable = [
        'communication_id',
        'staff_id',
        'date_read'
    ];

    protected $hidden = [];

    protected $casts = [];
}
