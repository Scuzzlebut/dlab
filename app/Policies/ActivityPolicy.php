<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ActivityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Activity $attendance
     * @return bool
     */
    public function view(User $user, Activity $activity): bool
    {
        return (in_array($user->staff->role->role_slug, ['admin']) ||
            $user->staff->collaborators->contains($activity->staff_id) || // manager può vedere solo le sue e quelle dei suoi collaboratori
            $user->staff->id === $activity->staff_id
        );
    }
}
