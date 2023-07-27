<?php

namespace App\Models;

use App\Traits\Searchable;
use App\Traits\HasAttachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Attendance extends BaseModel {
    use HasAttachment;

    protected $fillable = [
        'applicant_id',
        'staff_id',
        'date_start',
        'date_end',
        'hours',
        'type_id',
        'accepted_by',
        'accepted_datetime',
        'accepted',
        'sick_note',
        'note',
    ];

    protected $hidden = [];

    protected $searchable = [
        'staff:name,surname',
        'date_start',
        'date_end',
        'type:type_name',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'accepted_datetime' => 'datetime',
        'accepted' => 'boolean'
    ];

    protected $appends = ['attachments_count'];

    public function applicant() {
        return $this->belongsTo(Staff::class, 'applicant_id', 'id');
    }

    public function staff() {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function approver() {
        return $this->belongsTo(Staff::class, 'accepted_by', 'id');
    }

    public function type() {
        return $this->belongsTo(AttendanceType::class, 'type_id', 'id');
    }

    public function scopeViewActiveStaff($query) {
        return $query->whereHas('staff', function ($q) {
            $q->whereNull('date_end')->orWhereDate('date_end', '>', format_date_for_db(Carbon::now()));
        });
    }

    public function scopeViewByRole($query) {

        $staff_user = Auth::user()->staff;

        if ($staff_user->role->role_slug === 'employee') {
            $query->where('staff_id', $staff_user->id);
        }

        if ($staff_user->role->role_slug === 'manager') {
            return $query->where(function ($q) use ($staff_user) {
                $q->whereIn('staff_id', $staff_user->collaborators->pluck('id')->toArray());
                $q->orWhere('staff_id', $staff_user->id);
                $q->orWhere('applicant_id', $staff_user->id);
            });
        }
    }

    public function scopeViewOnlyAccepted($query) {
        return $query->where('accepted', true);
    }
}
