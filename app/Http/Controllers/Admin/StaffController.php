<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Staff;

use App\Mail\GenericMail;
use App\Models\Communication;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\SimpleRequest as Request;

class StaffController extends Controller {
    use \App\Traits\GlobalSettings;

    public function index(Request $request) {
        $this->authorize('viewAny', Staff::class);

        $staff = Staff::viewByRole()->select()->orderBy('surname', 'asc')->orderBy('name', 'asc');

        if (!isset($request->all) || !filter_var($request->all, FILTER_VALIDATE_BOOLEAN)) {
            $staff->viewActiveStaff();
        }

        $staff = $this->doTheSearch($staff, $request);
        $staff = $this->doTheSort($staff, $request);
        $staff = $this->doThePagination($staff, $request);

        return response()->json($staff);
    }

    public function store(Request $request) {
        $this->authorize('create', Staff::class);

        $global_fields_check = ['morning_starttime', 'morning_endtime', 'afternoon_starttime', 'afternoon_endtime'];

        $request->validate([
            'code' => 'nullable|unique:staff|digits_between:1,5', //|regex:/(^[0-9]{5}$)/
            'surname' => 'required|string|max:30',
            'name' => 'required|string|max:30',
            'taxcode' => 'string|max:16',
            'address' => 'string|max:100',
            'city' => 'string|max:100',
            'postcode' => 'string|max:10',
            'state' => 'string|max:50',
            'phone_number' => 'string|max:20',
            'private_email' => 'required|string|unique:staff|max:60',
            'iban' => 'nullable|string|size:27',
            'gender' => 'string|max:10',
            'birthplace' => 'string|max:100',
            'birthday' => 'nullable|date',
            'date_start' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'morning_starttime' => 'nullable|after_or_equal:00:00',
            'morning_endtime' => 'nullable|after:morning_starttime|before:afternoon_starttime',
            'afternoon_starttime' => 'nullable|after:morning_endtime|before:afternoon_endtime',
            'afternoon_endtime' => 'nullable|after:afternoon_starttime|before_or_equal:23:59',
            'notifications_on' => 'nullable|boolean',
            'user_id' => 'exists:users,id',
            'created_by' => 'nullable|exists:users,id',
            'role_id' => 'required|exists:staff_roles,id',
            'type_id' => 'required|exists:staff_types,id',
            'note' => 'max:1000',
        ]);

        if (!isset($request['notifications_on']) || empty($request['notifications_on'])) {
            $request['notifications_on'] = 0;
        }

        if (!isset($request['created_by']) || empty($request['created_by'])) {
            $request['created_by'] = Auth::user()->id;
        }

        //check matricola, se vuota la generiamo
        if (empty($request->code)) {
            $request['code'] = Staff::generateNewCode();
        } else {
            $request['code'] = Staff::formatCode($request->code);
        }

        //check orari di lavoro, se vuoti allora prendo quelli globali
        foreach ($global_fields_check as $index => $key) {
            if (!isset($request[$key]) || empty($request[$key])) {
                $request[$key] = $this->get_global_setting($key);
            }
        }

        $staff_employee = Staff::create($request->all());

        //segno come lette tutte le comunicazioni passate
        $communication_ids = Communication::whereRelation('roles', 'staff_roles_id', $staff_employee->role_id)->pluck('id')->toArray();
        $staff_employee->communications()->sync($communication_ids);

        return response()->json([
            'code'      => 0,
            'message'   => 'Dipendente creato con successo.',
            'object'    => $staff_employee,
        ]);
    }

    public function update(Request $request, $id) {
        $staff_employee = Staff::findOrFail($id);

        $this->authorize('update', $staff_employee);

        $request->validate([
            'code' =>  'nullable|digits_between:1,5|unique:staff,code,' . $id,
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
            'date_start' => 'date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'morning_starttime' => 'after_or_equal:00:00',
            'morning_endtime' => 'after:morning_starttime|before:afternoon_starttime',
            'afternoon_starttime' => 'after:morning_endtime|before:afternoon_endtime',
            'afternoon_endtime' => 'after:afternoon_starttime|before_or_equal:23:59',
            'notifications_on' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'created_by' => 'nullable|exists:users,id',
            'role_id' => 'exists:staff_roles,id',
            'type_id' => 'exists:staff_types,id',
            'note' => 'max:1000',
        ]);

        //check matricola, se vuota la generiamo
        if (empty($request->code)) {
            $request['code'] = Staff::generateNewCode(); //attenzione, da chiedere conferma nel frontend
        } else {
            $request['code'] = Staff::formatCode($request->code);
        }

        $request_data = $request;

        //solo gli admin possono modificare ruolo/tipo utente staff
        if (Auth::user()->staff->role->role_slug !== 'admin') {
            $request_data = $request->except([
                'role_id',
                'type_id'
            ]);
        } else {
            $request_data = $request->all();
        }

        $staff_employee->fill($request_data);
        $staff_employee->save();

        return response()->json([
            'code'      => 0,
            'message'   => 'Dipendente aggiornato con successo.',
            'object'    => $staff_employee,
        ]);
    }

    public function destroy($id) {
        $staff_employee = Staff::findOrFail($id);

        $this->authorize('delete', $staff_employee);

        $staff_employee->delete();
        //che facciamo nella tabella users?

        return response()->json([
            'code'      => 0,
            'message'   => 'Dipendente cancellato con successo.',
            'object'    => $staff_employee,
        ]);
    }

    public function activateLogin(Request $request, $id) {
        $staff_employee = Staff::findOrFail($id);

        $this->authorize('handleUsersLogin', $staff_employee);

        if ($staff_employee->user_id !== null && $staff_employee->user->active) {
            throw ValidationException::withMessages([
                'message' => ['Questo utente ha già un accesso attivo associato.'],
            ]);
        }

        /*
        * La password deve contenere almeno tre caratteri tra quelli seguenti
        * - Lettere maiuscole/minuscole
        * - Numeri
        * - Caratteri non alfanumerici (!, $, #, or %)
        * - Caratteri unicode
        */
        $request->validate([
            'email' => 'required|string|unique:users,email,' . $staff_employee->user_id,
            'password' => password_validator_rules()
        ], [
            'password.regex' => password_validator_rules_custom_error()
        ]);

        $params = [
            'name' => $staff_employee->fullname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => Carbon::now(),
            'active' => 1
        ];

        //se il record esisteva già allora vado in update e lo riabilito altrimenti creo un nuovo record
        if ($staff_employee->user_id !== null) {
            $staff_employee->user->fill($params);
            $staff_employee->user->save();
            $user = $staff_employee->user;
        } else {
            $user = User::create($params);
            $staff_employee->user_id = $user->id;
            $staff_employee->save();
        }

        $token = $user->createToken($staff_employee->fullname)->plainTextToken;

        //invio email di accesso
        $mail_subject = "D.Lab HR - Creazione utente per accesso web";
        $mail_body = '<p>Ciao ' . $staff_employee->name . '!<br>
        Di seguito ti inoltriamo le credenziali per poter accedere al portale ' . sprintf("<a href='%s' target='_blank'>%s</a>", env('APP_URL'), 'D.Lab HR') . ':<br>
        <p>
            <span>• email: <a style="pointer-events: none; color: inherit; name="email">' . $user->email . '</a></span><br>
            <span>• password: ' . $request->password . '</span>
        </p>
        <br>
        </p>';
        Mail::to($user->email)->send(new GenericMail($mail_subject, $mail_body));

        return response()->json([
            'code'      => 0,
            'message'   => 'Credenziali create con successo.',
            'object'    => $user,
            'token' => $token
        ]);
    }

    private function setUserLoginStatus($user_id, $active = 0) {
        $result = null;
        $user = User::where('id', $user_id)->first();

        if (!empty($user)) {
            $result = $user->id;
            $user->active = $active;
            $user->save();
        }

        return $result;
    }

    //disabilita l'accesso web del dipendente
    public function disableLogin(Request $request, $id) {
        $staff_employee = Staff::with('user')->findOrFail($id);

        $this->authorize('handleUsersLogin', $staff_employee);

        //se è già stato disabilitato, avviso con un messaggio
        if ($staff_employee->user_id == null) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Questo dipendente non ha un accesso web',
                'object'    => $staff_employee
            ]);
        }

        //disabilito l'utenza web e cancello i tokens per una disconnessione istantanea
        $staff_employee->user->disable(true);
        $staff_employee->user->tokens()->delete();

        return response()->json([
            'code'      => 0,
            'message'   => 'Accesso disabilitato con successo.',
            'object'    => $staff_employee
        ]);
    }

    //licenziamento
    public function disable(Request $request, $id) {
        $staff_employee = Staff::with('user')->findOrFail($id);

        $this->authorize('handleUsersLogin', $staff_employee);

        //se è già stato licenziato, avviso con un messaggio
        if ($staff_employee->date_end !== null) {
            return response()->json([
                'code'      => 1,
                'message'   => 'Dipendente già disabilitato in precedenza.',
                'object'    => $staff_employee
            ]);
        }

        $request->validate([
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        $date_end = $request->date_end;
        if (empty($date_end)) {
            $date_end = now()->format('Y-m-d');
        }

        //disabilito l'accesso web (se presente)
        if ($staff_employee->user_id !== null) {
            $staff_employee->user->remove();
        }

        $staff_employee->date_end = $date_end;
        $staff_employee->save();

        return response()->json([
            'code'      => 0,
            'message'   => 'Dipendente disabilitato con successo.',
            'object'    => $staff_employee
        ]);
    }

    //associo un array di managers all'utente staff - solo admin può associare i dipendenti/manager
    public function setManagers(Request $request, $id) {
        $staff_employee = Staff::findOrFail($id);

        $this->authorize('viewAny', $staff_employee);

        $request->validate([
            'managerIds' => 'nullable|array|exists:staff,id',
        ]);

        // !! potrebbe servire un controllo per capire se i manager scelti non siano di ruolo inferiore (es: associo un dipendente come manager) !!

        $staff_employee->managers()->sync($request->managerIds);

        return response()->json([
            'code'      => 0,
            'message'   => 'Managers associati correttamente.',
            'object'    => $staff_employee->load('managers'),
        ]);
    }

    //associo un array di utenti staff ad un manager - solo admin può associare i dipendenti/manager
    public function setStaffEmployees(Request $request, $id) {
        $staff_manager = Staff::findOrFail($id);

        $this->authorize('viewAny', $staff_manager);

        $request->validate([
            'staffIds' => 'nullable|array|exists:staff,id',
        ]);

        // !! potrebbe servire un controllo per capire se lo staff scelto non sia di ruolo superiore (es: associo come staff un admin ad un manager) !!

        $staff_manager->collaborators()->sync($request->staffIds);

        return response()->json([
            'code'      => 0,
            'message'   => 'Dipendenti staff associati correttamente.',
            'object'    => $staff_manager->load('staff'),
        ]);
    }
}
