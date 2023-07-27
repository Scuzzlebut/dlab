<?php

namespace App\Traits;

use App\Models\Attachment;

trait HasAttachment {
    public function attachments() {
        return $this->morphMany(Attachment::class, 'model');
    }

    public function getAttachmentsCountAttribute() {
        return $this->attachments()->count();
    }
}
