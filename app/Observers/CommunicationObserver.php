<?php

namespace App\Observers;

use App\Models\Communication;

class CommunicationObserver {
    /**
     * Handle the Communication "created" event.
     *
     * @param  \App\Models\Communication  $communication
     * @return void
     */
    public function created(Communication $communication) {
        //
    }

    /**
     * Handle the Communication "updated" event.
     *
     * @param  \App\Models\Communication  $communication
     * @return void
     */
    public function updated(Communication $communication) {
        //
    }

    /**
     * Handle the Communication "deleted" event.
     *
     * @param  \App\Models\Communication  $communication
     * @return void
     */
    public function deleted(Communication $communication) {
        foreach ($communication->attachments as $att) {
            $att->delete();
        }
    }

    /**
     * Handle the Communication "restored" event.
     *
     * @param  \App\Models\Communication  $communication
     * @return void
     */
    public function restored(Communication $communication) {
        //
    }

    /**
     * Handle the Communication "force deleted" event.
     *
     * @param  \App\Models\Communication  $communication
     * @return void
     */
    public function forceDeleted(Communication $communication) {
        //
    }
}
