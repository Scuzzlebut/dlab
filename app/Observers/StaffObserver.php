<?php

namespace App\Observers;

use App\Models\Staff;

class StaffObserver {
    /**
     * Handle the Staff "created" event.
     *
     * @param  \App\Models\Staff  $staff
     * @return void
     */
    public function created(Staff $staff) {
        //
    }

    /**
     * Handle the Staff "updated" event.
     *
     * @param  \App\Models\Staff  $staff
     * @return void
     */
    public function updated(Staff $staff) {
        //
    }

    /**
     * Handle the Staff "deleted" event.
     *
     * @param  \App\Models\Staff  $staff
     * @return void
     */
    public function deleted(Staff $staff) {
        foreach ($staff->attachments as $att) {
            $att->delete();
        }
    }

    /**
     * Handle the Staff "restored" event.
     *
     * @param  \App\Models\Staff  $staff
     * @return void
     */
    public function restored(Staff $staff) {
        //
    }

    /**
     * Handle the Staff "force deleted" event.
     *
     * @param  \App\Models\Staff  $staff
     * @return void
     */
    public function forceDeleted(Staff $staff) {
        //
    }
}
