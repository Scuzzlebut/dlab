<?php

namespace App\Models;

class AttachmentLog extends BaseModel {
    protected $fillable = [
        'attachment_id',
        'staff_id',
        'user_agent',
        'ip',
        'downloaded_at'
    ];

    protected $hidden = [];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    protected $appends = [];
}
