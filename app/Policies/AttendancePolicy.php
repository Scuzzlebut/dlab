<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use App\Http\Requests\SimpleRequest as Request;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy {
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user) {
        return true;
    }

    //admin e manager possono approvare SOLO se la richiesta non è già stata approvata in precedenza
    public function approval(User $user, Attendance $attendance) {
        return (
            (in_array($user->staff->role->role_slug, ['admin']) ||
                $user->staff->collaborators->contains($attendance->staff_id) //manager può approvare solo le sue e quelle dei suoi collaboratori
            ) && ($attendance->accepted == 0 && is_null($attendance->accepted_datetime) //entrambi possono solo se la richiesta non è già stata approvata
            )
        );
    }

    //diamo la possibilità SOLO all'admin di poter rimuovere i dati di approvazione e, di fatto, resettare lo stato della richiesta
    public function reset(User $user) {
        return (in_array($user->staff->role->role_slug, ['admin'])
        );
    }

    public function export(User $user) {
        return (in_array($user->staff->role->role_slug, ['admin'])
        );
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Attendance $attendance) {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $user->staff->collaborators->contains($attendance->staff_id) || // manager può vedere solo le sue e quelle dei suoi collaboratori
            $user->staff->id === $attendance->staff_id
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, int $staff_id) {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $user->staff->collaborators->contains($staff_id) || //manager può creare solo quelle per i suoi collaboratori
            $user->staff->id === $staff_id
        );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Attendance $attendance) {
        return (
            (in_array($user->staff->role->role_slug, ['admin']) ||
                ($user->staff->collaborators->contains($attendance->staff_id) || //manager può gestire solo le sue e quelle dei suoi collaboratori
                    $user->staff->id === $attendance->staff_id
                )) && ($attendance->accepted == 0 && is_null($attendance->accepted_datetime) //tutti possono solo se la richiesta non è già stata approvata
            )
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Attendance $attendance) {
        return (
            (in_array($user->staff->role->role_slug, ['admin']) ||
                ($user->staff->collaborators->contains($attendance->staff_id) || //manager può cancellare solo le sue e quelle dei suoi collaboratori
                    $user->staff->id === $attendance->staff_id
                )) && ($attendance->accepted == 0 && is_null($attendance->accepted_datetime) //tutti possono solo se la richiesta non è già stata approvata
            )
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Attendance $attendance) {
        return (in_array($user->staff->role->role_slug, ['admin'])
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Attendance $attendance) {
        return (in_array($user->staff->role->role_slug, ['admin'])
        );
    }
}
