<?php

namespace App\Models;

use App\Traits\HasAttachment;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

class Paysheet extends BaseModel {
    use HasAttachment;

    protected $fillable = [
        'staff_id',
        'reference_month',
        'reference_year',
        'created_by',
        'downloaded_at',
        'ip',
        'user_agent'
    ];

    protected $appends = [
        //'link',
        //'thumbnail_link'
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function staff() {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public static function getTmpLinkAttribute($filename) {
        return URL::temporarySignedRoute('web.paysheet.file.preview', now()->addMinutes(60), ['filename' => $filename]);
    }

    public static function getTmpThumbnailLinkAttribute($thumbname) {
        return URL::temporarySignedRoute('web.paysheet.thumb.preview', now()->addMinutes(60), ['thumbname' => $thumbname]);
    }

    public function scopeViewByRole($query) {

        $staff_user = Auth::user()->staff;

        if ($staff_user->role->role_slug !== 'admin') {
            $query->where('staff_id', $staff_user->id);
        }

        /*if($staff_user->role->role_slug === 'manager'){
            return $query->where(function ($q) use ($staff_user) {
                $q->whereIn('staff_id', $staff_user->collaborators->pluck('id')->toArray());
                $q->orWhere('staff_id', $staff_user->id);
                $q->orWhere('applicant_id', $staff_user->id);
            });
        }*/
    }
}
