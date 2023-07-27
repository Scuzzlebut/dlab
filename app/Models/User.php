<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword {
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active'            => 'boolean',
    ];

    protected $appends = [];

    public function sendPasswordResetNotification($token) {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification() {
        //
    }

    public function staff() {
        return $this->belongsTo(Staff::class, 'id', 'user_id');
    }

    public function setBlankPassowrd() {
        $this->password = null;
        $this->email_verified_at = null;
        $this->remember_token = null;
        $this->save();
        return $this;
    }

    public function setActive($status) {
        $this->active = $status;
        $this->save();
        return $this;
    }

    public function isActive() {
        return $this->active;
    }

    public function enable() {
        $this->setActive(true);
    }

    public function disable($pswd_reset = false) {
        $this->setActive(false);
        if ($pswd_reset) {
            $this->setBlankPassowrd();
        }
    }

    public function remove() {
        $this->setBlankPassowrd();
        $this->disable();
        $this->delete();
    }
}
