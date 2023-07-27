<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use App\Models\Staff;
use App\Models\Paysheet;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaysheetPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return true;
    }

    public function view(User $user, Paysheet $paysheet) {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $user->staff->id === $paysheet->staff_id
        );
    }

    public function upload(User $user) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    public function create(User $user) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    public function update(User $user, Paysheet $paysheet) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    public function delete(User $user, Paysheet $paysheet) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }
}
