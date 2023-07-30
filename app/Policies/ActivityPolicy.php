<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ActivityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    public function view(User $user, Activity $activity): bool
    {
        return ($user->staff->role->role_slug == 'admin' ||
            $user->staff->collaborators->contains($activity->staff_id) || // manager può vedere solo le sue e quelle dei suoi collaboratori
            $user->staff->id === $activity->staff_id
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return true;
    }


    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param  Activity  $activity
     * @return Response|bool
     */
    public function update(User $user, Activity $activity): Response|bool
    {
        return ($user->staff->role->role_slug == 'admin' ||
            $user->staff->collaborators->contains($activity->staff_id) || //manager può gestire solo le sue e quelle dei suoi collaboratori
            $user->staff->id === $activity->staff_id
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param  Activity  $activity
     * @return bool
     */
    public function delete(User $user, Activity $activity): bool
    {
        return ($user->staff->role->role_slug == 'admin' ||
            $user->staff->collaborators->contains($activity->staff_id) || //manager può gestire solo le sue e quelle dei suoi collaboratori
            $user->staff->id === $activity->staff_id
        );
    }
}
