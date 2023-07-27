<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Paysheet;
use App\Models\Attachment;
use App\Models\Attendance;
use App\Models\Communication;
use App\Http\Requests\SimpleRequest as Request;

class AttachmentController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $this->authorize('viewAny', Attachment::class);

        $request->validate([
            'model_type'    => 'required',
            'model_id'      => 'nullable',
        ]);

        $model_class = NULL;
        $model_id = $request->model_id;

        switch (strtolower($request->model_type)) {
            case 'attendance':
                $model_class = Attendance::class;
                $attendance = Attendance::findOrFail($model_id); //model_id è l'id della richiesta di assenza
                $items = Attachment::viewByAttendance($attendance, $model_class, $model_id);
                break;
            case 'communication':
                $model_class = Communication::class;
                $items = Attachment::where('model_type', $model_class)->where('model_id', $model_id);
                break;
            case 'paysheet':
                $model_class = Paysheet::class;
                $items = Attachment::viewByPaysheet($model_class, $model_id);
                break;
            case 'staff':
                $model_class = Staff::class;
                $items = Attachment::viewByStaff($model_class, $model_id);
                break;
            default:
                throw new \App\Exceptions\DontReportUserException('Richiesta non valida', 125);
                break;
        }

        $items = $this->doTheSearch($items, $request);
        $items = $this->doTheSort($items, $request);
        $items = $this->doThePagination($items, $request);

        return response()->json($items);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function show(Attachment $attachment, $id) {
        $attachment = Attachment::findOrFail($id);
        $this->authorize('view', $attachment);
        return response()->json($attachment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function edit(Attachment $attachment) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $attachment = Attachment::findOrFail($id);

        $this->authorize('update', $attachment);

        $params = $request->only([
            'title',
            'description',
            'filename',
            'category',
        ]);

        //ATTENZIONE - DEVO RIGENERARE LA THUMB


        $attachment->fill($params);
        $attachment->save();

        return response()->json([
            'code'      => 0,
            'message'   => 'Allegato aggiornato con successo.',
            'object'    => $attachment,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $attachment = Attachment::findOrFail($id);

        $this->authorize('delete', $attachment);

        // NB: l'eliminazione fisica del file avviene nell'observer
        $attachment->delete();

        return response()->json([
            'code'      => 0,
            'message'   => 'Allegato eliminato con successo.',
            'object'    => $attachment,
        ]);
    }

    public function getTextWithOCR($id) {
        $attachment = Attachment::findOrFail($id);

        return response()->json([
            'object'    => $attachment,
            'text'      => $attachment->getContentText(),
        ]);
    }
}
