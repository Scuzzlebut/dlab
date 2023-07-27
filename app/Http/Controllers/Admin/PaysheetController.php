<?php

namespace App\Http\Controllers\Admin;

use DateTime;
//use mikehaertl\pdftk\Pdf;

use App\Models\Staff;
use setasign\Fpdi\Fpdi;
use App\Models\Paysheet;
use App\Mail\GenericMail;
use App\Models\Attachment;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\PdfToText\Pdf as PdfToText;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SimpleRequest as Request;

class PaysheetController extends Controller {
    private static $pathPdfToText = 'C:\Program Files\Git\mingw64\bin\pdftotext.exe';

    private static $thumb_size = [
        'w'     => 1024,
        'h'     => 1024,
        'res'   => 100,
    ];

    private static $months = [
        'gennaio'       => 1,
        'febbraio'      => 2,
        'marzo'         => 3,
        'aprile'        => 4,
        'maggio'        => 5,
        'giugno'        => 6,
        'luglio'        => 7,
        'agosto'        => 8,
        'settembre'     => 9,
        'ottobre'       => 10,
        'novembre'      => 11,
        'dicembre'      => 12,
        'tredicesima'   => 13,
    ];

    public function upload(Request $request) {
        $staff_user = Auth::user()->staff;

        $this->authorize('upload', Paysheet::class);

        $request->validate([
            'attachment' => 'required|file|mimes:pdf|max:5000',
        ]);

        if ($file = $request->file('attachment')) {

            $tmp_disk = 'local';
            $storage_disk = Attachment::getAppEnvDisk();

            $key_month_year = 'PERIODO LIQUIDAZIONE';
            $key_tredicesima = '13 MENSILITA\'';
            $ext = '.pdf';
            $code = $fullname = $month = $year = '';

            $partname = Carbon::now()->timestamp;
            $filename = $partname . $ext;
            $filename_source = $file->getClientOriginalName();
            $filepath = $file->storeAs(Attachment::getTmpPath(Paysheet::class), $filename, $tmp_disk);
            $partname = pathinfo($filepath, PATHINFO_FILENAME);
            $filetype = strtolower($file->getClientMimeType());

            if ($filetype == 'application/pdf') {
                $pathPdfToText = null;
                if (get_os_system() === 'WIN') {
                    $pathPdfToText = self::$pathPdfToText;
                }

                //verifico la versione del file PDF (non la compressione)
                $pdf_version = Attachment::pdfGetVersionFromHeader($filepath, $tmp_disk);
                if (!empty($pdf_version)) {
                    //se la versione di PDF è superiore alla 1.4
                    if ($pdf_version[0] > 1 || $pdf_version[1] > 4) {
                        //allora di sicuro non è supportato da FPDI e quindi converto il file
                        $converted_paths = Attachment::pdfGSConvert14($filepath, $tmp_disk);
                        $end_path = $converted_paths['end_path'];
                        $filepath = $converted_paths['filepath'];
                    }
                } else {
                    //genero un errore - da verificare in futuro con dei test
                    return response()->json([
                        'code'      => 1,
                        'message'   => 'Impossibile recuperare le informazioni sulla versione del file PDF. Processo abortito.'
                    ]);
                }

                $end_path = Storage::disk($tmp_disk)->path($filepath);
                $end_dir = Storage::disk($tmp_disk)->path(Attachment::getTmpPath(Paysheet::class));
                $end_dir = preg_replace('/[\/]+/', '/', $end_dir . '/' . substr($filename, 0, strrpos($filename, '/')));

                $pdf = new FPDI();
                $pagecount = $pdf->setSourceFile($end_path);

                //Splitto ogni pagina in un pdf
                for ($i = 1; $i <= $pagecount; $i++) {
                    $pdf_new = new FPDI();
                    $pdf_new->AddPage();
                    $pdf_new->setSourceFile($end_path);
                    $pdf_new->useTemplate($pdf_new->importPage($i));

                    try {
                        $new_name = sprintf("%s_%04d%s", $partname, $i, $ext);
                        $pdf_new_filename = $end_dir . $new_name;
                        $pdf_new->Output($pdf_new_filename, "F");
                        //shell_exec('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile="'.$pdf_new_filename.'" "'.$pdf_new_filename.'"');
                        $thumbnail_path = Attachment::createThumbFromFilename(
                            $filename_source,
                            $filepath,
                            $filetype,
                            $new_name,
                            self::$thumb_size['w'],
                            self::$thumb_size['h'],
                            self::$thumb_size['res'],
                            $i
                        );
                        $thumbnail_filename = pathinfo($thumbnail_path, PATHINFO_BASENAME);

                        //attenzione che si ottengono risultati diversi tra windows/unix
                        $pdf_text = preg_replace('/\s*($|\n)/', '\1', PdfToText::getText($pdf_new_filename, $pathPdfToText));

                        //cerco matricola - full name
                        if (preg_match('/^\d{5}\s.+$/m', $pdf_text, $matched)) {
                            $str_split = explode(' ', trim($matched[0]), 2);
                            $code = $str_split[0];
                            $fullname = $str_split[1];

                            //Aggiungere controllo su matricola, se errore allora ricerco dipendente dal nome
                        }

                        //cerco mese - anno
                        //if (preg_match('/^[' . $key_month_year . ']+\n\w+\s\d{4}$/m', $pdf_text, $matched)) {
                        if (preg_match('/\w+\s\d{4}$/m', $pdf_text, $matched)) {
                            $str_split = explode(' ', trim(str_replace($key_month_year, "", $matched[0])), 2);
                            $month = strtolower($str_split[0]);
                            $year = $str_split[1];
                        } else {
                            //altrimenti cerco la tredicesima
                            if (preg_match('/^[' . $key_tredicesima . ']+[\s]*\d{4}/m', $pdf_text, $matched)) {
                                $str_split = explode(' ', trim(str_replace($key_tredicesima, "", $matched[0])), 2);
                                $month = array_search(13, self::$months);
                                $year = $str_split[0];
                            }
                        }

                        $data[] = [
                            'filename' => $new_name,
                            'code' => $code,
                            //'fullname' => $fullname,
                            //'month' => ucfirst($month),
                            'month_nr' => (isset(self::$months[$month]) ? self::$months[$month] : ''),
                            'year' => $year,
                            'link' => Paysheet::getTmpLinkAttribute($new_name),
                            'thumbnail_link' => Paysheet::getTmpThumbnailLinkAttribute($thumbnail_filename),
                        ];
                    } catch (Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                }

                return response()->json([
                    'code'      => 0,
                    'message'   => 'Elaborazione avvenuta con successo',
                    'object'    => $data,
                ]);
            } else {

                return response()->json([
                    'code'      => 1,
                    'message'   => 'Attenzione, il file caricato non è in formato pdf.',
                ]);
            }
        }
    }

    public function store(Request $request) {
        $ps = [];

        $this->authorize('create', Paysheet::class);

        $tmp_disk = 'local';
        $storage_disk = Attachment::getAppEnvDisk();

        if (is_array($request['data'])) {
            foreach ($request['data'] as $paysheet) {
                //verifico i parametri di ogni singolo oggetto dell'array
                $validator = Validator::make($paysheet, [
                    'filename'  => 'required',
                    'code'      => 'required|exists:staff,code',
                    'month_nr'  => 'required|numeric|min:1|max:13',
                    'year'      => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'code'      => 1,
                        'message'   => $validator->errors(),
                    ]);
                }

                $paysheet = $validator->validated();

                //verifico che il mese sia valido
                $month = DateTime::createFromFormat('m', $paysheet['month_nr']);
                if (!$month || (int)$paysheet['month_nr'] > 13) {
                    return response()->json([
                        'code'      => 1,
                        'message'   => 'Mese non corretto',
                        'object'    => ['month_nr' => $paysheet['month_nr']],
                    ]);
                }
                //verifico se tredicesima
                if ((int)$paysheet['month_nr'] == 13) {
                    $month_format_m = 13;
                } else {
                    $month_format_m = $month->format('m');
                }
                $month_name = array_search($paysheet['month_nr'], self::$months);

                //verifico che l'anno sia valido
                $year = DateTime::createFromFormat('Y', $paysheet['year']);
                if (!$year) {
                    return response()->json([
                        'code'      => 1,
                        'message'   => 'Anno non corretto',
                        'object'    => ['year' => $paysheet['year']],
                    ]);
                }

                //cerco il dipendente dalla matricola
                $staff_employee = Staff::where('code', 'LIKE', trim($paysheet['code']))->first();
                if (!$staff_employee) {
                    return response()->json([
                        'code'      => 1,
                        'message'   => 'Dipendente non trovato',
                        'object'    => ['code' => $paysheet['code']],
                    ]);
                }

                //se il file esiste ancora nella temp allora lo sposto altrimenti errore e fermo l'elaborazione
                $pdf_tmp_path = sprintf("%s/%s", Attachment::getTmpPath(Paysheet::class), $paysheet['filename']);
                if (Storage::disk($tmp_disk)->exists($pdf_tmp_path)) {

                    $pdf_new_name = sprintf("%04d_%02d_Cedolino_%s.pdf", $year->format('Y'), $month_format_m, friendly_string($staff_employee->fullname, '_'));
                    $pdf_new_path = sprintf("%s/%s", Attachment::getFilesPath(Paysheet::class, $staff_employee->id), $pdf_new_name);

                    $result = Storage::disk($storage_disk)->put($pdf_new_path, Storage::disk($tmp_disk)->get($pdf_tmp_path));
                    if ($result) {
                        Storage::disk($tmp_disk)->delete($pdf_tmp_path);
                    }

                    //sposto anche la thumb
                    $thumb_tmp_path = sprintf("%s/%s.jpg", Attachment::getTmpPath(Paysheet::class, true), $paysheet['filename']);
                    //se esiste
                    if (Storage::exists($thumb_tmp_path)) {
                        $thumb_new_name = sprintf("%s.jpg", $pdf_new_name);
                        $thumb_new_path = sprintf("%s/%s", Attachment::getThumbsPath(Paysheet::class, $staff_employee->id), $thumb_new_name);
                        $result = Storage::disk($storage_disk)->put($thumb_new_path, Storage::get($thumb_tmp_path));
                        if ($result) {
                            Storage::delete($thumb_tmp_path);
                        }
                    } else {
                        //se non esiste, la rigenero nella cartella corretta
                        $thumb_new_path = Attachment::createThumbFromFilename(
                            '',
                            $pdf_new_path,
                            Storage::mimeType($pdf_new_path),
                            $pdf_new_name,
                            self::$thumb_size['w'],
                            self::$thumb_size['h'],
                            self::$thumb_size['res']
                        );
                    }

                    //preparo l'oggetto paysheet da salvare nel db
                    $params = [
                        'staff_id'          => $staff_employee->id,
                        'reference_month'   => $month_format_m,
                        'reference_year'    => $year->format('Y'),
                        'publish_date'      => Carbon::now(),
                        'created_by'        => Auth::user()->id,
                    ];
                    $ps_obj = Paysheet::create($params);
                    $ps[] = $ps_obj;

                    //preparo l'oggetto attachment da salvare nel db
                    $params = [
                        'title'             => 'Cedolino',
                        'description'       => sprintf("%s %04d", ucfirst($month_name), $year->format('Y')),
                        'filename'          => $pdf_new_name,
                        'filetype'          => Storage::mimeType($pdf_new_path),
                        'filepath'          => $pdf_new_path,
                        'filesize'          => Storage::size($pdf_new_path),
                        'model_id'          => $ps_obj->id,
                        'model_type'        => Paysheet::class,
                        'category'          => '',
                        'thumbnail_path'    => $thumb_new_path
                    ];
                    $attachment = Attachment::create($params);

                    //invio email di notifica nuovo cedolino caricato se l'utente ha le notifiche abilitate
                    if ($staff_employee->notifications_on) {
                        $mail_subject = sprintf("D.Lab HR - Cedolino %s %04d", ucfirst($month_name), $year->format('Y'));
                        $mail_body = '<p>Ciao ' . $staff_employee->name . '!<br>
                        ti informiamo che è stato caricato un nuovo cedolino sul portale ' . sprintf("<a href='%s' target='_blank'>%s</a>", env('APP_URL'), 'D.Lab HR') . '
                        <br>
                        </p>';
                        Mail::to($staff_employee->private_email)->send(new GenericMail($mail_subject, $mail_body));
                    }
                } else {
                    return response()->json([
                        'code'      => 1,
                        'message'   => 'File non trovato',
                        'object'    => ['file' => $paysheet['filename']],
                    ]);
                }
            }

            return response()->json([
                'code'          => 0,
                'message'       => 'Associazione effettuata con successo',
                'paysheets'     => $ps
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paysheet  $paysheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paysheet $paysheet) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Paysheet  $paysheet
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $paysheet = Paysheet::findOrFail($id);

        $this->authorize('delete', $paysheet);

        // NB: l'eliminazione fisica del file avviene nell'observer del modello Attachment
        $paysheet->delete();

        return response()->json([
            'code'      => 0,
            'message'   => 'Cedolino eliminato con successo',
            'object'    => $paysheet,
        ]);
    }
}
