<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * Register a new user.
     *
     * @header Content-Type application/json
     * @group Account management
     * @bodyParam name string required the name of the user. Example: Gautier
     * @bodyParam email string required the email of the user. Example: gautier@position.cm
     * @bodyParam password string required the password of the user. Example: gautier123
     * @responseFile storage/responses/register.json
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|between:6,20',
        ]);


        if ($validator->fails()) {
            return $this->sendError('Erreur de paramètres.', $validator->errors(), 400);
        }

        $input = $request->only(['name', 'email', 'password']);
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $user->sendEmailVerificationNotification();
        $success['token'] = $user->createToken('Ifdd')->accessToken;
        $success['user'] = $user;

        if ($user) {
            return $this->sendResponse($success, 'Création réussie verifiez vos mails.');
        }

        return $this->sendError("Pas autorisé.", ['error' => 'Unauthorised']);
    }



    /**
     * Login a new user.
     *
     * @header Content-Type application/json
     * @group Account management
     * @bodyParam email string required if phone not found the email of the user. Example: gautier@position.cm
     * @bodyParam password string required the password of the user. Example: gautier123
     * @responseFile storage/responses/login.json
     */
    public function login(Request $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if ($user->hasVerifiedEmail()) {
                $success['token'] = $user->createToken('Ifdd')->accessToken;
                $success['user'] = $user;

                return $this->sendResponse($success, 'Connexion réussie.');
            }

            return $this->sendError("Email not verified.", ['error' => 'Unauthorised'], 401);
        }

        return $this->sendError('Pas autorisé.', ['error' => 'Login Error'], 400);
    }

    /**
     * Logout a user.
     *
     * @header Content-Type application/json
     * @authenticated
     * @group Account management
     * @responseFile storage/responses/logout.json
     */
    public function logout()
    {
        $user = Auth::user();

        if($user) {
            $token = $user->token();
        $revoque = $token->revoke();

        if ($revoque) {
            return $this->sendResponse("", 'Deconnexion réussie.');
        }
        
        return $this->sendError('Pas autorisé.', ['error' => 'Echec de deconnexion'], 400);
        } 
        else {
             return $this->sendResponse("", 'Deconnexion réussie.');
        }


        

    }

    /**
     * get User Account.
     *
     * @header Content-Type application/json
     * @authenticated
     * @group Account management
     * @responseFile storage/responses/getuser.json
     */
    public function getUser()
    {
        $user = Auth::user();

        if ($user) {
            $success["user"] = $user;

            return $this->sendResponse($success, 'Utilisateur');
        }

        return $this->sendError('Pas autorisé.', ['error' => 'Unauthorised']);
    }

    /**
     * Update user account.
     *
     * @header Content-Type application/json
     * @authenticated
     * @group Account management
     * @urlParam id int required the id of the admin. Example: 2
     * @bodyParam name string the name of the user. Example: Gautier
     * @responseFile 201 storage/responses/getuser.json
     */
    public function updateuser($id, Request $request)
    {
        $user = Auth::user();
        if ($user->role == 1 || $user->id == $id) {


            $user = Auth::user();

            $user->name = $request->name ?? $user->name;

            $save = $user->save();

            if ($save) {
                $success["user"] = $user;
                return $this->sendResponse($success, "Utilisateur", 201);
            } else {
                return $this->sendError('Erreur.', ['error' => 'Echec de mise à jour'], 400);
            }
        } else {
            return $this->sendError('Erreur.', ['error' => 'Echec de suppression'], 400);
        }
    }


    /**
     * Delete user account.
     *
     * @header Content-Type application/json
     * @authenticated
     * @group Account management
     * @urlParam id int required the id of the admin. Example: 2
     * @responseFile 201 storage/responses/delete.json
     */
    public function deleteuser($id)
    {
        $user = Auth::user();
        if ($user->role == 1 || $user->id == $id) {

            try {
                DB::beginTransaction();
                User::destroy($id);

                DB::commit();

                return $this->sendResponse("", "Delete Success", 201);
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->sendError('Erreur.', ['error' => 'Echec de suppression'], 400);
            }
        }
    }


    /**
     * Forgot Password
     *
     * @header Content-Type application/json
     * @group Account management
     * @bodyParam email string required the email of the user. Example: gautier@position.cm
     * @responseFile storage/responses/forgot.json
     */
    public function forgot()
    {
        $credentials = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($credentials);

        return $this->sendResponse("", "Un lien de reinitialisation vous a été envoyé par mail.", 200);
    }

    /**
     * Reset Password
     *
     * @header Content-Type application/json
     * @group Account management
     * @bodyParam email string required the email of the user. Example: gautier@position.cm
     * @bodyParam token string required token give in mail.
     * @bodyParam password string required the new password of the user. Example: gautier124
     * @bodyParam password_confirmation string required the password confirmation of the user. Example: gautier124
     * @responseFile 201 storage/responses/reset.json
     */
    public function reset(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erreur de paramètres.', $validator->errors(), 400);
        }

        $credentials = $request->only(['email', 'token', 'password']);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return $this->sendError("Invalid token provided", ['error' => 'Unauthorised']);
        }

        return $this->sendResponse("", "Password has been successfully changed", 201);
    }
}
