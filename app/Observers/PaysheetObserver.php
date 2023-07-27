<?php

namespace App\Observers;

use App\Models\Paysheet;

class PaysheetObserver {
    /**
     * Handle the Paysheet "created" event.
     *
     * @param  \App\Models\Paysheet  $paysheet
     * @return void
     */
    public function created(Paysheet $paysheet) {
        //
    }

    /**
     * Handle the Paysheet "updated" event.
     *
     * @param  \App\Models\Paysheet  $paysheet
     * @return void
     */
    public function updated(Paysheet $paysheet) {
        //
    }

    /**
     * Handle the Paysheet "deleted" event.
     *
     * @param  \App\Models\Paysheet  $paysheet
     * @return void
     */
    public function deleted(Paysheet $paysheet) {
        foreach ($paysheet->attachments as $att) {
            $att->delete();
        }
    }

    /**
     * Handle the Paysheet "restored" event.
     *
     * @param  \App\Models\Paysheet  $paysheet
     * @return void
     */
    public function restored(Paysheet $paysheet) {
        //
    }

    /**
     * Handle the Paysheet "force deleted" event.
     *
     * @param  \App\Models\Paysheet  $paysheet
     * @return void
     */
    public function forceDeleted(Paysheet $paysheet) {
        //
    }
}
