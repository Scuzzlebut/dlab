<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Activity extends Model
{
    use HasFactory;

    public function type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ActivityType::class,"activity_type_id");
    }
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class);
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
            });
        }
    }
}
