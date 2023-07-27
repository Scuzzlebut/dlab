<?php

namespace App\Http\Controllers;

use App\Models\Paysheet;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class PaysheetWebController extends Controller {

    public function file($filename) {
        $file_path = Attachment::getTmpPath(Paysheet::class);
        $file = sprintf("%s/%s", $file_path, $filename);

        $content = Storage::get($file);
        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function thumbnail($thumbname) {
        $thumb_path = 'thumbs/' . Attachment::getTmpPath(Paysheet::class);
        $thumb = sprintf("%s/%s", $thumb_path, $thumbname);

        $content = Storage::get($thumb);
        return response($content, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Content-Disposition', 'inline; filename="' . $thumbname . '"');
    }
}
