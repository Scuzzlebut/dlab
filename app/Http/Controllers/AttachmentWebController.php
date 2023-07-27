<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentWebController extends Controller {

    public function inline($id) {
        $attachment = Attachment::findOrFail($id);
        $content = Storage::get($attachment->filepath);
        return response($content, 200)
            ->header('Content-Type', $attachment->filetype)
            ->header('Content-Disposition', 'inline; filename="' . $attachment->filename . '"');
    }

    public function thumbnail($id) {
        $attachment = Attachment::findOrFail($id);
        $content = Storage::get($attachment->thumbnail_path);
        return response($content, 200)
            ->header('Content-Type', $attachment->getThumbnailFileType())
            ->header('Content-Disposition', 'inline; filename="' . $attachment->getThumbnailFileName() . '"');
    }

    public function download($id) {
        $attachment = Attachment::findOrFail($id);
        $content = Storage::get($attachment->filepath);
        return response($content, 200)
            ->header('Content-Type', $attachment->filetype)
            ->header('Content-Disposition', 'attachment; filename="' . $attachment->filename . '"');
    }
}
