<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Mail\GenericMail;
use App\Models\Attachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SimpleRequest as Request;

class StaffController extends Controller {
    public function show($id) {
        $staff = Staff::with(['user', 'attachments', 'managers'])->findOrFail($id);
        $this->authorize('view', $staff);
        return response()->json($staff);
    }

    public function getStaffEmployees($id) {
        $items = Staff::with('collaborators')->findOrFail($id);

        if (Auth::user()->staff->role->role_slug == 'admin') {
            $items['collaborators'] = Staff::with('collaborators')->where('id', '!=', Auth::user()->staff->id)->get()->all();
        }
        $this->authorize('view', $items);
        return response()->json($items->collaborators);
    }

    public function getManagers($id) {
        $items = Staff::with('managers')->findOrFail($id);
        $this->authorize('view', $items);
        return response()->json($items->managers);
    }

    public function update(Request $request, $id) {
        $staff_employee = Staff::findOrFail($id);

        $request->validate([
            'surname' => 'string|max:30',
            'name' => 'string|max:30',
            'taxcode' => 'string|max:16',
            'address' => 'string|max:100',
            'city' => 'string|max:100',
            'postcode' => 'string|max:10',
            'state' => 'string|max:50',
            'phone_number' => 'string|max:20',
            'private_email' => 'string|max:60|unique:staff,private_email,' . $id,
            'iban' => 'nullable|string|size:27',
            'gender' => 'nullable|string|max:10',
            'birthplace' => 'string|max:100',
            'birthday' => 'date',
            'notifications_on' => 'boolean',
            'note' => 'max:1000'
        ]);

        $this->authorize('update', $staff_employee);

        //controllo se IBAN è stato modificato
        if (strtoupper($staff_employee['iban']) != strtoupper($request['iban'])) {
            //se iban modificato allora mando email ai responsabili attivi e agli admins

            //email degli admins attivi
            $admins = Staff::with('user')->where('role_id', '=', '3')->where(function ($q) {
                $q->where('date_end')->orWhereDate('date_end', '>', format_date_for_db(Carbon::now()));
            })->get();
            //le unisco a quelle dei manager attivi del dipendente
            $arrayTo = $admins->merge($staff_employee->managers);
            foreach ($arrayTo as $to) {
                //nel caso di molti dipendenti magari pensare di inviare un'unica email a "noreply" e mettere in bcc tutti gli altri
                $mail_subject = "Variazione codice IBAN";
                $mail_body = '<p>Ciao!<br>Ti informiamo che in data ' . Carbon::now()->format('d/m/Y') . ' il dipendente ' . sprintf("%s (%s)", strtoupper($staff_employee->fullname), $staff_employee->code) . ' ha cambiato il suo codice IBAN.<br></p>';
                Mail::to($to->user->email)->send(new GenericMail($mail_subject, $mail_body));
            }
        }

        $staff_employee->fill(
            $request->except([
                'code',
                'date_start',
                'date_end',
                'morning_starttime',
                'morning_endtime',
                'afternoon_starttime',
                'afternoon_endtime',
                'user_id',
                'created_by',
                'role_id',
                'type_id',
            ])
        );
        $staff_employee->save();

        return response()->json([
            'code'      => 0,
            'message'   => 'Profilo aggiornato con successo.',
            'object'    => $staff_employee,
        ]);
    }

    public function uploadAttachment(Request $request, $id) {
        $staff_employee = Staff::findOrFail($id);

        //riutilizzo la policy update: l'admin può caricare documenti a tutti
        //il manager solo ai suoi collaboratori (e a se stesso) e il dipendente solo a se stesso
        $this->authorize('update', $staff_employee);

        $request->validate([
            'attachment' => 'required|file|mimes:png,jpg,jpeg,gif,svg,pdf,doc,docx|max:5000',
            'title' => 'required|max:100',
            'description' => 'nullable|max:255',
        ]);

        if ($file = $request->file('attachment')) {
            $filepath = $file->store(Attachment::getFilesPath(Staff::class), Attachment::getAppEnvDisk());
            $filename = $file->getClientOriginalName();
            $filetype = $file->getClientMimeType();
            $thumbnail_path = Attachment::createThumbFromFilename($filename, $filepath, $filetype);

            $params = [
                'title'             => $request->title,
                'description'       => $request->description,
                'filename'          => $filename,
                'filetype'          => $filetype,
                'filepath'          => $filepath,
                'filesize'          => $file->getSize(),
                'model_id'          => $id,
                'model_type'        => Staff::class,
                'category'          => $request->category,
                'thumbnail_path'    => $thumbnail_path
            ];

            $attachment = Attachment::create($params);

            return response()->json([
                'code'      => 0,
                'message'   => 'Documento caricato con successo.',
                'object'    => $attachment,
            ]);
        } else {
            return response()->json([
                'code'      => 1,
                'message'   => 'Houston, we have a problem!',
            ]);
        }
    }
}
