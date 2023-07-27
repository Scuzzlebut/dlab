<?php

namespace App\Models;

use App\Models\StaffRole;
use App\Models\Communication;
use App\Traits\HasAttachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Staff extends BaseModel {
    use HasAttachment;

    protected $fillable = [
        'code',
        'surname',
        'name',
        'taxcode',
        'address',
        'city',
        'postcode',
        'state',
        'phone_number',
        'private_email',
        'iban',
        'gender',
        'birthplace',
        'birthday',
        'date_start',
        'date_end',
        'morning_starttime',
        'morning_endtime',
        'afternoon_starttime',
        'afternoon_endtime',
        'notifications_on',
        'user_id',
        'created_by',
        'role_id',
        'type_id',
        'note'
    ];

    protected $appends = ['fullname', 'address_complete', 'role_name', 'type_name', 'attachments_count'];

    protected $casts = [
        'birthday' => 'date',
        'date_start' => 'date',
        'date_end' => 'date',
        'notifications_on' => 'boolean'
    ];

    //protected $with = ['role'];

    public function getFullnameAttribute() {
        return $this->surname . ' ' . $this->name;
    }

    public function getAddressCompleteAttribute() {
        return $this->address . ', ' . $this->postcode . ' ' . $this->city . ' (' . $this->state . ')';
    }

    public function role() {
        return $this->belongsTo(StaffRole::class, 'role_id', 'id')->select('id', 'role_name', 'role_slug');
    }

    public function getRoleNameAttribute() {
        return $this->role->role_name;
    }

    public function type() {
        return $this->belongsTo(StaffType::class, 'type_id', 'id')->select('id', 'type_name');
    }

    public function getTypeNameAttribute() {
        return $this->type->type_name;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function collaborators() {
        return $this->belongsToMany(Staff::class, 'staff_managers', 'manager_id', 'staff_id');
    }

    public function getStaffIds() {
        return $this->collaborators->pluck('id');
    }

    public function communications() {
        return $this->belongsToMany(Communication::class, 'communication_staff', 'staff_id', 'communication_id')->withPivot('date_read');
    }

    //manager attualmente attivi
    public function managers() {
        return $this->belongsToMany(Staff::class, 'staff_managers', 'staff_id', 'manager_id')
            ->whereNull('staff.date_end')
            ->orWhereDate('date_end', '>', format_date_for_db(Carbon::now()));
    }

    public function getManagerIds() {
        return $this->managers->pluck('id');
    }

    public static function formatCode($code) {
        return str_pad(abs($code), 5, "0", STR_PAD_LEFT);
    }

    public static function generateNewCode() {
        return Staff::formatCode((int) ((Staff::latest('code')->first()->code ?? 0) + 1));
    }

    public function getWorkedTimePerDay($type = 'hour', $part = 'all') {
        $workedTime = $workedMTime = $workedATime = 0;
        $dt_aetime = Carbon::parse($this->afternoon_endtime);
        $dt_astime = Carbon::parse($this->afternoon_starttime);
        $dt_metime = Carbon::parse($this->morning_endtime);
        $dt_mstime = Carbon::parse($this->morning_starttime);

        switch (strtolower($type)) {
            case 'hour':
                $workedATime = $dt_astime->diffInHours($dt_aetime);
                $workedMTime = $dt_mstime->diffInHours($dt_metime);
                break;
            case 'minutes':
                $workedATime = $dt_astime->diffInMinutes($dt_aetime);
                $workedMTime = $dt_mstime->diffInMinutes($dt_metime);
                break;
            case 'seconds':
                $workedATime = $dt_astime->diffInSeconds($dt_aetime);
                $workedMTime = $dt_mstime->diffInSeconds($dt_metime);
                break;
        }

        switch (strtolower($part)) {
            case 'all':
                $workedTime = $workedMTime + $workedATime;
                break;
            case 'morning':
                $workedTime = $workedMTime;
                break;
            case 'afternoon':
                $workedTime = $workedATime;
                break;
        }

        return $workedTime;
    }

    public function getLunchTimePerDay($type = 'hour') {
        $lunchTime = 0;
        $dt_astime = Carbon::parse($this->afternoon_starttime);
        $dt_metime = Carbon::parse($this->morning_endtime);

        switch (strtolower($type)) {
            case 'hour':
                $lunchTime = $dt_metime->diffInHours($dt_astime);
                break;
            case 'minutes':
                $lunchTime = $dt_metime->diffInMinutes($dt_astime);
                break;
            case 'seconds':
                $lunchTime = $dt_metime->diffInSeconds($dt_astime);
                break;
        }

        return $lunchTime;
    }

    public function scopeViewActiveStaff($query) {
        return $query->whereNull('date_end')->orWhereDate('date_end', '>', format_date_for_db(Carbon::now()));
    }

    public function scopeViewByRole($query) {

        $staff_user = Auth::user()->staff;

        if ($staff_user->role->role_slug === 'employee') {
            $query->where('id', $staff_user->id);
        }

        if ($staff_user->role->role_slug === 'manager') {
            return $query->where(function ($q) use ($staff_user) {
                $q->whereIn('id', $staff_user->collaborators->pluck('id')->toArray());
                $q->orWhere('id', $staff_user->id);
            });
        }
    }
}
