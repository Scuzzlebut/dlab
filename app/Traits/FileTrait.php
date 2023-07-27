<?php

namespace App\Traits;

use App\Models\Paysheet;
use App\Models\Attachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf as PdfToImage;

trait FileTrait {
    public static function createThumbFromId($attachment_id, $filename_dest = null, $w = 0, $h = 0, $res = 0, $pdf_page = null) {
        $attachment = Attachment::findOrFail($attachment_id);
        return self::createThumbnail($attachment->filename, $attachment->filepath, $attachment->filetype, $filename_dest, $w, $h, $res, $pdf_page);
    }

    public static function createThumbFromFilename($filename, $filepath, $filetype, $filename_dest = null, $w = 0, $h = 0, $res = 0, $pdf_page = null) {
        return self::createThumbnail($filename, $filepath, $filetype, $filename_dest, $w, $h, $res, $pdf_page);
    }

    public static function createThumbnail($filename, $filepath, $filetype, $filename_dest = null, $w = 0, $h = 0, $res = 0, $pdf_page = null) {
        $thumb_max_height = ($h != 0) ? $h : 1024;
        $thumb_max_width = ($w != 0) ? $w : 1024;
        $thumb_resolution = ($res != 0) ? $res : 200;
        $storage_disk = 'local_tmp'; //Attachment::getAppEnvDisk();

        if ($filename_dest === null) {
            $filename_dest = $filename;
            $thumbnail_path = 'thumbs/' . $filepath . '.jpg';
        } else {
            $filename = $filename_dest;
            $thumbnail_path = sprintf('thumbs/%s/%s.jpg', pathinfo($filepath, PATHINFO_DIRNAME), $filename);
        }

        if (Storage::disk('local')->exists($filepath)) {
            $file_content = Storage::disk('local')->get($filepath);
        } else {
            $file_content = Storage::get($filepath);
        }
        $res = Storage::disk($storage_disk)->put($filename_dest, $file_content);
        $thumbnail_filename = $filename;
        //$thumbnail_filename = $attachment->getThumbnailFileName(true);
        //$thumbnail_path = $filepath . '.thumb.jpg';
        //$thumbnail_path = Attachment::getFilesPath($attachment->model_type) . '/thumbs/' . $thumbnail_filename;

        if ($res) {
            switch (strtolower($filetype)) {
                case 'application/pdf':
                    $pdf = new PdfToImage(temp_storage_path($filename));
                    if ($pdf_page !== null) {
                        $pdf->setPage($pdf_page);
                    }
                    $pdf->setResolution($thumb_resolution)->saveImage(temp_storage_path($thumbnail_filename));
                    $imageTmp = imagecreatefromjpeg(temp_storage_path($thumbnail_filename));
                    break;
                case 'image/png':
                    $imageTmp = imagecreatefrompng(temp_storage_path($filename));
                    break;
                case 'image/jpeg2000':
                case 'image/jpeg':
                case 'image/jpg':
                    $imageTmp = imagecreatefromjpeg(temp_storage_path($filename));
                    break;
                case 'image/gif':
                    $imageTmp = imagecreatefromgif(temp_storage_path($filename));
                    break;
                default:
                    //Log::warning('BZZT NON SO GESTIRE QUESTO TIPO DI FILE: ' . $filetype);
                    break;
            }

            if (strtolower($filetype) !== 'application/pdf') {
                list($width_orig, $height_orig) = getimagesize(temp_storage_path($filename));
            } else {
                list($width_orig, $height_orig) = getimagesize(temp_storage_path($thumbnail_filename));
            }

            $ratio_orig = $width_orig / $height_orig;

            if ($thumb_max_width / $thumb_max_height > $ratio_orig) {
                $thumb_max_width = $thumb_max_height * $ratio_orig;
            } else {
                $thumb_max_height = $thumb_max_width / $ratio_orig;
            }

            $image_p = imagecreatetruecolor($thumb_max_width, $thumb_max_height);
            imagecopyresampled($image_p, $imageTmp, 0, 0, 0, 0, $thumb_max_width, $thumb_max_height, $width_orig, $height_orig);

            imagejpeg($image_p, temp_storage_path($thumbnail_filename));

            Storage::put($thumbnail_path, Storage::disk($storage_disk)->get($thumbnail_filename));

            imagedestroy($image_p);
            imagedestroy($imageTmp);

            //Storage::put($thumbnail_path, Storage::disk($storage_disk)->get($thumbnail_filename));
            Storage::disk($storage_disk)->delete($filename); //cancello la copia di lavoro temporanea

            //$attachment->thumbnail_path = $thumbnail_path;
            //$attachment->saveQuietly();
            return $thumbnail_path ?? null;
        }
    }

    //recupero le informazioni sulla versione del file PDF
    public static function pdfGetVersionFromHeader($filepath, $disk = null) {
        $pdf_version = [];

        if ($disk == null) {
            $disk = 'local';
        }

        if (Storage::disk($disk)->exists($filepath)) {
            $rootpath = Storage::disk($disk)->path($filepath);
            $file_content = file_get_contents($rootpath);
            if (preg_match('/%PDF-(\d)\.(\d)/', $file_content, $result) !== 0) {
                list(, $major, $minor) = $result;
                $pdf_version = [(int) $major, (int) $minor];
            }
        }

        return $pdf_version;
    }

    //soluzione temporanea - si utilizza Ghostscript per convertire il pdf nella più supportata v1.4
    public static function pdfGSConvert14($filepath, $disk = null) {
        $suffix = '-conv.pdf';
        $cmd = 'gs -dSAFER -dEmbedAllFonts=true -dSubsetFonts=true -dCompatibilityLevel=%s -dPDFA -dQUIET -dBATCH -dNOPAUSE -sColorConversionStrategy=UseDeviceIndependentColor -sDEVICE=pdfwrite -dPDFACompatibilityPolicy=1 -sOutputFile=%s %s';

        if ($disk == null) {
            $disk = 'local';
        }

        $input_file = Storage::disk($disk)->path($filepath);
        $output_pathfile = $filepath . $suffix;
        $output_rootfile = $input_file . $suffix;
        $command = sprintf($cmd, '1.4', $output_rootfile, escapeshellarg($input_file));

        shell_exec($command);

        return ['end_path' => $output_rootfile, 'filepath' => $output_pathfile];
    }
}
