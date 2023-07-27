<?php

namespace App\Observers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttachmentObserver {
    /**
     * Handle the Attachment "created" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function created(Attachment $attachment) {
        //
    }

    /**
     * Handle the Attachment "updated" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function updated(Attachment $attachment) {
        //
    }

    /**
     * Handle the Attachment "deleted" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function deleted(Attachment $attachment) {
        try {
            if (Storage::exists($attachment->filepath))
                Storage::delete($attachment->filepath);
            if (Storage::exists($attachment->thumbnail_path))
                Storage::delete($attachment->thumbnail_path);
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the Attachment "restored" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function restored(Attachment $attachment) {
        //
    }

    /**
     * Handle the Attachment "force deleted" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function forceDeleted(Attachment $attachment) {
        //
    }
}
