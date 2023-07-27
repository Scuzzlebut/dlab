<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\SimpleRequest as Request;

class AuthController extends Controller {
    public function logout(Request $request) {

        $request->user()->currentAccessToken()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Verifico email
        $user = User::where('email', $fields['email'])->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Non è presente un account con questa email.'],
            ]);
        }
        // Verifico password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenziali errate.'],
            ]);
        }
        //Se l'account user è attivo o meno
        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => ['Account sospeso.'],
            ]);
        }
        //Controllo campo verified_at (usi futuri)
        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email da verificare, controlla la tua posta in arrivo.'],
            ]);
        }
        //Rimuovo il vecchio token se presente
        $device_name = 'main_token';
        if (isset($request->device_name) && !empty($request->device_name)) {
            $device_name = $request->device_name;
        }
        $user->tokens()->where('name', $device_name)->delete();
        //Genero il nuovo token
        $token = $user->createToken($device_name);

        $response = [
            'user' => $user,
            'token' => $token->plainTextToken,
            'staff' => $user->staff,
        ];

        return response($response, 201);
    }

    // !! Cambia la password dell'utente cui viene passato l'id anche se il token non è suo !!
    public function changePassword($id = NULL, Request $request) {
        $request->validate([
            'password' => password_validator_rules(),
        ], [
            'password.regex' => password_validator_rules_custom_error()
        ]);

        if ($request->password !== $request->password_confirmation) {
            throw ValidationException::withMessages([
                'message' => ['Le password inserite non coincidono.'],
            ]);
        }

        $user = User::findOrFail($id);
        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['Le nuova password deve essere differente dalla precedente.'],
            ]);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        $response = [
            'message' => 'Password cambiata con successo.',
        ];

        return response($response, 201);
    }

    public function user(Request $request) {

        $response = [
            'user' => $request->user(),
            'staff' => $request->user()->staff
        ];

        return response($response, 201);
    }

    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users',
            'password' => password_validator_rules(),
        ], [
            'password.regex' => password_validator_rules_custom_error()
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 200);
        }

        $user = User::where('email', $request['email'])->first();
        if (!$user->active) {
            $validator->errors()->add('password', 'Questo utente non è più attivo');
            return response()->json(['errors' => $validator->errors()], 200);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                //$user->tokens()->delete();
                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response(['message' => __($status)]);
        }
        return response(['errormessage' => __($status)]);
    }

    public function askResetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 200);
        }

        $user = User::where('email', $request['email'])->first();
        if (!$user->active) {
            $validator->errors()->add('email', 'Questo utente non è più attivo');
            return response()->json(['errors' => $validator->errors()], 200);
        }

        $status = Password::sendResetLink(
            ['email' => $user->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response(['message' => __($status)]);
        }
        return response(['errors' => ['email' => [__($status)]]]);
    }
}
