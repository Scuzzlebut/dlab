<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\CommunicationStaff;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SimpleRequest as Request;

class CommunicationController extends Controller {
    public function index(Request $request) {
        $this->authorize('viewWithRoles', Communication::class);

        $staff_id = $request->user()->staff->id;
        $staff_role_id = $request->user()->staff->role_id;

        $communications = Communication::whereRelation('roles', 'staff_roles_id', $staff_role_id)->select();

        if (isset($request->toread)) {
            if (filter_var($request->toread, FILTER_VALIDATE_BOOLEAN)) {
                $communications->whereDoesntHave('staff', function ($query) use ($staff_id) {
                    $query->where('staff_id', $staff_id);
                });
            } else {
                $communications->whereHas('staff', function ($query) use ($staff_id) {
                    $query->where('staff_id', $staff_id);
                });
            }
        }

        $communications = $this->doTheSearch($communications, $request);
        $communications = $this->doTheSort($communications, $request);
        $communications = $this->doThePagination($communications, $request);

        return response()->json($communications);
    }

    public function show(Request $request, $id) {
        $communication = Communication::with('creator', 'attachments', 'roles')->findOrFail($id);

        $this->authorize('view', $communication);

        $communication['roleIds'] = $communication['roles'];
        unset($communication['roles']);

        return response()->json($communication);
    }

    public function setReadStatus(Request $request, $id) {
        $communication = Communication::findOrFail($id);

        $this->authorize('view', $communication);

        $staff_id = Auth::user()->staff->id;

        //se la variabile è true allora imposto la comunicazione come letta
        $communication->staff()->detach(Auth::user()->staff->id);
        if ($request->read) {
            $communication->staff()->attach($staff_id, ['date_read' => now()]);
            $status = 'letta';
        } else {
            $status = 'non letta';
        }

        return response()->json([
            'code'      => 0,
            'message'   => 'Comunicazione impostata come ' . $status . '.',
        ]);
    }
}
