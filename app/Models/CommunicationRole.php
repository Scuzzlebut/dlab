<?php

namespace App\Models;

use App\Models\Communication;

class CommunicationRole extends BaseModel {
    protected $table = 'communication_roles';

    protected $fillable = [
        'communication_id',
        'staff_roles_id'
    ];

    protected $hidden = [];

    protected $casts = [];
}
