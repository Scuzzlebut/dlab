<?php

namespace App\Models;

use App\Traits\HasAttachment;

class Communication extends BaseModel {
    use HasAttachment;

    protected $fillable = [
        'title',
        'body',
        'created_by',
    ];

    protected $hidden = [];

    protected $casts = [];

    protected $appends = ['creator_name', 'attachments_count'];

    public function getCreatorNameAttribute() {
        return $this->creator->surname . ' ' . $this->creator->name;
    }

    public function roles() {
        return $this->belongsToMany(StaffRole::class, 'communication_roles', 'communication_id', 'staff_roles_id');
    }

    public function getViewRolesIds() {
        return $this->roles()->allRelatedIds();
    }

    public function creator() {
        return $this->belongsTo(Staff::class, 'created_by', 'id');
    }

    public function staff() {
        return $this->belongsToMany(Staff::class, 'communication_staff', 'communication_id', 'staff_id')->withPivot('date_read');
    }
}
