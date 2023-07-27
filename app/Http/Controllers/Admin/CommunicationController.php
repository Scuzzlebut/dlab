<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attachment;
use App\Models\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SimpleRequest as Request;

class CommunicationController extends Controller {
    public function index(Request $request) {
        $this->authorize('viewAny', Communication::class);

        $communications = Communication::with('creator', 'roles')->select();

        $communications = $this->doTheSearch($communications, $request);
        $communications = $this->doTheSort($communications, $request);
        $communications = $this->doThePagination($communications, $request);

        return response()->json($communications);
    }

    public function store(Request $request) {
        $this->authorize('create', Communication::class);

        $request->validate([
            'title' => 'required|max:50',
            'body' => 'required',
            'roleIds' => 'nullable|array|exists:staff_roles,id',
        ]);

        $request['created_by'] = Auth::user()->staff->id;

        $communication = Communication::create(
            $request->all()
        );

        $communication->roles()->sync($request->roleIds);

        return response()->json([
            'code'      => 0,
            'message'   => 'Comunicazione creata con successo.',
            'object'    => $communication,
        ]);
    }

    public function setViewRoles(Request $request, $id) {
        $communication = Communication::findOrFail($id);

        $this->authorize('viewAny', $communication);

        $request->validate([
            'roleIds' => 'nullable|array|exists:staff_roles,id',
        ]);

        $communication->roles()->sync($request->roleIds);

        return response()->json([
            'code'      => 0,
            'message'   => 'I permessi di visibilità sono stati correttamente salvati.',
            'object'    => $communication->load('roles'),
        ]);
    }

    public function update(Request $request, $id) {
        $communication = Communication::findOrFail($id);

        $this->authorize('update', $communication);

        $request->validate([
            'title' => 'max:50',
            'roleIds' => 'nullable|array|exists:staff_roles,id',
        ]);

        $communication->fill(
            $request->except([
                'created_by'
            ])
        );

        $communication->save();
        $communication->roles()->sync($request->roleIds);

        return response()->json([
            'code'      => 0,
            'message'   => 'Comunicazione aggiornata con successo.',
            'object'    => $communication,
        ]);
    }

    public function destroy($id) {
        $communication = Communication::findOrFail($id);

        $this->authorize('delete', $communication);

        $communication->delete();

        return response()->json([
            'code'      => 0,
            'message'   => 'Comunicazione cancellata con successo.',
            'object'    => $communication,
        ]);
    }

    public function uploadAttachment(Request $request, $id) {
        $communication = Communication::findOrFail($id);

        //riutilizzo la policy update: solo l'admin e il manager possono caricare documenti
        $this->authorize('update', $communication);

        $request->validate([
            'attachment' => 'required|file|mimes:png,jpg,jpeg,gif,svg,pdf,doc,docx|max:5000',
            'title' => 'required|max:100',
            'description' => 'nullable|max:255',
        ]);

        if ($file = $request->file('attachment')) {
            $filepath = $file->store(Attachment::getFilesPath(Communication::class), Attachment::getAppEnvDisk());
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
                'model_type'        => Communication::class,
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
