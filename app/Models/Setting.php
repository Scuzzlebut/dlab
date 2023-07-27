<?php

namespace App\Models;

class Setting extends BaseModel {
    protected $fillable = [
        '',
    ];

    protected $hidden = [
        'field_name',
        'field_value'
    ];

    protected $casts = [
        '' => '',
    ];

    protected $appends = [];
}
