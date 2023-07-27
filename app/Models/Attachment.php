<?php

namespace App\Models;

use App\Traits\FileTrait;
use App\Traits\Searchable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Spatie\PdfToText\Pdf as PdfToText;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class Attachment extends BaseModel {
    use FileTrait, Searchable;

    protected $fillable = [
        'title',
        'description',
        'filename',
        'filetype',
        'filepath',
        'filesize',
        'model_id',
        'model_type',
        'category',
        'thumbnail_path',
    ];

    protected $hidden = [];

    protected $casts = [];

    protected $appends = [
        'link',
        'thumbnail_link',
        'download_link'
    ];

    protected $searchable = [
        'title',
        'description',
        'filename',
        'filetype',
    ];

    public function attachable() {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public function getLinkAttribute() {
        return URL::temporarySignedRoute('web.attachment.inline', now()->addMinutes(60), ['id' => $this->id]);
    }

    public function getThumbnailLinkAttribute() {
        if (!empty($this->thumbnail_path)) {
            return URL::temporarySignedRoute('web.attachment.preview', now()->addMinutes(60), ['id' => $this->id]);
        }

        return NULL;
    }

    public function getDownloadLinkAttribute() {
        return URL::temporarySignedRoute('web.attachment.download', now()->addMinutes(60), ['id' => $this->id]);
    }

    public function getThumbnailFileType() {
        return 'image/jpeg';
    }

    public function getThumbnailFileName($onlyname = false) {
        if (!$onlyname) {
            return $this->filename . '.thumb.jpg';
        } else {
            $parts = explode('/', $this->filepath);
            return end($parts);
        }
    }

    public function getTextWithOCR() {
        $res = Storage::disk('local_tmp')->put($this->filename, Storage::get($this->filepath));
        if (!$res) {
            return NULL;
        }
        $text = (new TesseractOCR(temp_storage_path($this->filename)))/*->lang('ita')*/->run();
        return $text;
    }

    public function getTextFromPdf() {
        $res = Storage::disk('local_tmp')->put($this->filename, Storage::get($this->filepath));
        if (!$res) {
            return NULL;
        }
        $text = PdfToText::getText(temp_storage_path($this->filename));
        return $text;
    }

    public function getContentText() {
        switch ($this->filetype) {
            case 'application/pdf':
                $text = $this->getTextFromPdf();
                break;
            case 'image/png':
            case 'image/jpeg':
            case 'image/jpeg2000':
            case 'image/gif':
            case 'image/tiff':
                $text = $this->getTextWithOCR();
                break;
            default:
                $text = NULL;
                break;
        }

        return $text;
    }

    public static function getTmpPath($class, $thumb = false) {
        switch (strtolower($class)) {
            case 'app\models\attendance':
                $path = 'assenze/tmp';
                break;
            case 'app\models\communication':
                $path = 'comunicazioni/tmp';
                break;
            case 'app\models\paysheet':
                $path = 'cedolini/tmp';
                break;
            case 'app\models\staff':
                $path = 'documenti/tmp';
                break;
            default:
                $path = 'generale/tmp';
        }

        if ($thumb)
            $path = 'thumbs/' . $path;

        return $path;
    }

    public static function getThumbsPath($class, $staff_id = NULL) {
        return 'thumbs/' . self::getFilesPath($class, $staff_id);
    }

    public static function getFilesPath($class, $staff_id = NULL) {
        if (is_null($staff_id)) {
            $staff_id = Auth::user()->staff->id;
        }
        switch (strtolower($class)) {
            case 'app\models\attendance':
                $path = 'assenze/' . $staff_id;
                break;
            case 'app\models\communication':
                $path = 'comunicazioni';
                break;
            case 'app\models\paysheet':
                $path = 'cedolini/' . now()->year . '/' . $staff_id;
                break;
            case 'app\models\staff':
                $path = 'documenti/' . $staff_id;
                break;
            default:
                $path = 'generale/' . $staff_id;
        }
        return $path;
    }

    public static function getAppEnvDisk() {
        if (App::environment('local'))
            return 'local';
        else
            return 's3';
    }

    //per allegati di tipo Staff (documenti dipendente)
    public function scopeViewByStaff($query, $model_class, $model_id) {
        $staff_user = Auth::user()->staff;

        if (($staff_user->role->role_slug === 'admin') ||
            ($staff_user->role->role_slug === 'manager' && in_array($model_id, $staff_user->collaborators->pluck('id')->toArray())) ||
            (in_array($staff_user->role->role_slug, ['employee', 'manager']) && $model_id == $staff_user->id)
        ) {
            return $query->where('model_type', $model_class)->where('model_id', $model_id);
        }

        return $query->whereRaw('1 = 0');
    }

    //per allegati di tipo Attendance
    public function scopeViewByAttendance($query, $attendance, $model_class, $model_id) {
        $staff_user = Auth::user()->staff;

        if (($staff_user->role->role_slug === 'admin') ||
            ($staff_user->role->role_slug === 'manager' && in_array($attendance->staff_id, $staff_user->collaborators->pluck('id')->toArray())) ||
            (in_array($staff_user->role->role_slug, ['employee', 'manager']) && in_array($staff_user->id, [$attendance->staff_id, $attendance->applicant_id]))
        ) {
            return $query->where('model_type', $model_class)->where('model_id', $model_id);
        }

        return $query->whereRaw('1 = 0');
    }

    //per allegati di tipo Paysheet (cedolini dipendente)
    public function scopeViewByPaysheet($query, $model_class, $model_id) {
        $staff_user = Auth::user()->staff;

        if (($staff_user->role->role_slug === 'admin') ||
            (in_array($staff_user->role->role_slug, ['employee', 'manager']) && $model_id == $staff_user->id)
        ) {
            return $query->where('model_type', $model_class)->where('model_id', $model_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
