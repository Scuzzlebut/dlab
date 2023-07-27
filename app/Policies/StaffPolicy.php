<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffPolicy {
    use HandlesAuthorization;

    /*public function before(User $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }*/

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user) {
        return in_array($user->staff->role->role_slug, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Staff $staff) {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $staff->getManagerIds()->contains($user->staff->id) || //se sono manager posso vedere i miei collaboratori
            $user->staff->id === $staff->id
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Staff $staff) {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $staff->getManagerIds()->contains($user->staff->id) || //se sono manager posso modificare i miei collaboratori
            $user->staff->id === $staff->id
        );
    }

    //ulteriore policy - solo Admin possono modificare ruolo/tipo utente staff
    public function updateRoles(User $user, Staff $staff) {
        return (in_array($user->staff->role->role_slug, ['admin']));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Staff $staff) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Staff $staff) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Staff $staff) {
        return in_array($user->staff->role->role_slug, ['admin']);
    }

    //Per la gestione degli accessi, tabella users
    public function handleUsersLogin(User $user, Staff $staff) {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $staff->getManagerIds()->contains($user->staff->id) //se sono manager posso gestire solo i miei collaboratori
        );
    }
}
