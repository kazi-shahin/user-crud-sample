<?php

namespace Modules\user\Controllers;

use App\Helpers\ImageHelper;
use App\Helpers\JsonHelper;
use App\Helpers\XPressLog;
use App\Http\Controllers\Controller;
use App\Services\MessageService;
use App\Services\GlobalService;
use Couchbase\UserSettings;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\company\models\CompanyModel;
use Modules\user\models\UserModel;
use Modules\user\services\UserDataService;
use Throwable;
use DB;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    protected $userModel;
    protected $userService;
    protected $messageService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userService = new UserDataService();
        $this->messageService = new MessageService();
    }

    /**
     * Show user page
     *
     * @return user list
     */
    public function index(Request $request)
    {

        $users = UserModel::with('userRole')
            ->get();

        if ($request->ajax()) {
            $returnHTML = View::make('user::user_list', compact('users'))->renderSections()['content'];
            return response()->json(array('status' => 200, 'html' => $returnHTML));
        }
        return view('user::user_list', compact('users'));
    }

    /**
     * Create new user
     *
     * @return create user page
     */
    public function createUser(Request $request)
    {

        $roles = DB::table('roles')->get();
        $companies = CompanyModel::where('has_users', 1)->get();

        if ($request->ajax()) {
            $returnHTML = View::make('user::create_user',
                compact( 'roles', 'companies'))->renderSections()['content'];
            return response()->json(array('status' => 200, 'html' => $returnHTML));
        }
        return view('user::create_user', compact(  'roles',  'companies'));
    }

    /**
     * Store user
     *
     * @param user data
     *
     * @return success message
     */
    public function storeUser(Request $request)
    {
        try {

            DB::beginTransaction();

            $duplicateEmail = UserModel::where('login', $request->user_name)->first();

            if (!empty($duplicateEmail)) {
                return ['status' => 401, 'reason' => 'Duplicate login User Name'];
            }


            if ($request->user_name != "" && $request->password == "") {
                return ['status' => 401, 'reason' => 'Password Required'];
            }

           /*
            | Storing user data
            */
            $email = $request->email;
            $password = $request->password;

            $new_user = new UserModel();

            $new_user->first_name = $request->first_name;
            $new_user->mi = $request->mi;
            $new_user->last_name = $request->last_name;
            $new_user->email = $request->email;
            $new_user->telephone = $request->phone;
            if ($request->role_id == 3) {
                $new_user->employee_no = $request->employee_no;
                $new_user->company_id = $request->company_id;
            } else {
                $new_user->employee_no = '';
                $new_user->company_id = '';
            }

            if ($request->is_active == 1) {
                $new_user->active = 1;
            } else {
                $new_user->active = 0;
            }
            $new_user->role_id = $request->role_id;

            $new_user->save();

            /**
             * User Credentials Save
             */
            $this->userModel->userToolsCredentialUpdate(
                $new_user->id,
                $request->role_id,
                $request->user_name,
                $request->password
            );

            /**
             * Upload user image
             */

            $this->userModel->uploadToolsUserImage($request->file("image"), $new_user->id);


            DB::commit();

            return ['status' => 200, 'reason' => 'User successfully created'];
        } catch (Exception $e) {
            DB::rollback();
            //SendMails::sendErrorMail($e->getMessage(), null, 'UserController', 'storeApp', $e->getLine(), $e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return ['status' => 401, 'reason' => 'Something went wrong. Try again later' . $e->getMessage()];
        }
    }

    /**
     * Get user details
     *
     * @param integer user_id
     *
     * @return success message
     */
    public function editUser(Request $request)
    {
        $roles = DB::table('roles')->get();
        $user = UserModel::with( 'check_outes')->where('id', $request->id)->first();
        $image = ImageHelper::displayUserImage($user == "" ? "" : $user->userPhoto()["picture"], $user == "" ? "" : $user->id, "user");
        $companies = CompanyModel::where('has_users', 1)->get();

        if ($request->ajax()) {
            $returnHTML = View::make('user::edit_user',
                compact( 'user', 'roles', 'companies', 'image'))->renderSections()['content'];
            return response()->json(array('status' => 200, 'html' => $returnHTML));
        }
        return view('user::edit_user', compact( 'user', 'roles', 'companies', 'image'));
    }

    /**
     * Get user details
     *
     * @param integer user_id
     *
     * @return user data
     */
    public function usserDetails(Request $request)
    {
        $user = [];
        $userDetails = User::with('badges')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.telephone', 'users.image',
                'companies.name as company_name')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->where('users.id', $request->user_id)
            ->first();
        $user['id'] = $userDetails->id;
        $user['first_name'] = $userDetails->first_name;
        $user['last_name'] = $userDetails->last_name;
        $user['email'] = $userDetails->email;
        $user['company_name'] = $userDetails->company_name;
        if (count($userDetails->badges) != 0) {
            $user['badge_no'] = $userDetails->badges[count($userDetails->badges) - 1]->badge_no;
        } else {
            $user['badge_no'] = '';
        }
        if ($userDetails->image != '') {
            $user['image'] = $userDetails->image;
        } else {
            $user['image'] = '';
        }
        return ['status' => 200, 'reason' => '', 'user' => $user];
    }

    /**
     * Update user
     *
     * @return success message
     */
    public function updateUser(Request $request)
    {
        try {

            DB::beginTransaction();

            $duplicateEmail = UserModel::where('login', $request->user_name)->first();

            if (!empty($duplicateEmail) && $duplicateEmail->id != $request->user_id) {
                return ['status' => 401, 'reason' => 'Duplicate login User Name'];
            }

            /*
            | Storing user data
            */
            $email = $request->email;
            $user = UserModel::where('id', $request->user_id)->first();

            $user->first_name = $request->first_name;
            $user->mi = $request->mi;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->telephone = $request->phone;
            if ($user->role_id == 3) {
                $user->employee_no = $request->employee_no;
                $user->company_id = $request->company_id;
            } else {
                $user->employee_no = '';
                $user->company_id = '';
            }
            if ($request->is_active == 1) {
                $user->active = 1;
            } else {
                $user->active = 0;
            }

            $user->updated_at = date('Y-m-d h:i:s');
            $user->role_id = $request->role_id;
            $user->save();

            $user_id = $user->id;

            /**
             * User Credentials Save
             */
            $this->userModel->userToolsCredentialUpdate(
                $user_id,
                $request->role_id,
                $request->user_name,
                $request->password
            );

            /**
             * Upload user image
             */

            $this->userModel->uploadToolsUserImage($request->file("image"), $user_id);

            DB::commit();

            return ['status' => 200, 'reason' => 'User successfully updated'];
        } catch (Exception $e) {
            DB::rollback();
            // SendMails::sendErrorMail($e->getMessage(), null, 'AdminController', 'storeApp', $e->getLine(), $e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return ['status' => 401, 'reason' => 'Something went wrong. Try again later' . $e->getMessage()];
        }
    }

    /**
     * Update user status
     *
     * @return success message
     */
    public function updateUserStatus(Request $request)
    {
        try {
            $user = Auth::user();

            DB::beginTransaction();

            $user = User::where('id', $request->user_id)->first();
            $old_user_application = $user->application_id;
            $user->status = $request->status;
            $user->updated_by = $user->id;
            $user->updated_at = date('Y-m-d h:i:s');
            $user->save();

            DB::commit();

            return ['status' => 200, 'reason' => 'User ' . $request->status . ' successfully.'];
        } catch (Exception $e) {
            DB::rollback();
            SendMails::sendErrorMail($e->getMessage(), null, 'AdminController', 'updateUserStatus', $e->getLine(),
                $e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return ['status' => 401, 'reason' => 'Something went wrong. Try again later'];
        }
    }


    public function resetPasswordInvitation(Request $request)
    {
        try {
            $user = User::where('id', $request->id)->first();
            $token = base64_encode($request->id . "#" . $user->email . "#" . date('Y-m-d h:i:s'));
            $email_to = [$user->email];


            $emailData['email'] = $email_to;
            $emailData['subject'] = 'Xpress Entry-Password reset invitation';
            $emailData['url'] = url('/') . '/reset_password?token=' . $token;
            $bodyMessage = 'Click below link to reset your password';
            $emailData['bodyMessage'] = $bodyMessage;
            $view = 'emails.reset_password_invitation_email';

            $result = SendMails::sendMail($emailData, $view);

            if ($result == 'ok') {
                DB::table('password_resets')->insert(
                    ['email' => $user->email, 'token' => $token]
                );

                return ['status' => 200, 'reason' => 'Password reset email sent'];
            } else {
                SendMails::sendErrorMail($result, null, 'UserController', 'resetPasswordInvitation', 222,
                    'App/Controllers/UserController', '', '', '', '');
                return [
                    'status' => 402,
                    'reason' => 'Invitation not sent. Your SMTP configuration is not correct. Go to setting and update the SMTP configuration.'
                ];
            }
        } catch (Exception $e) {
            SendMails::sendErrorMail($e->getMessage(), null, 'UserController', 'resetPasswordInvitation', $e->getLine(),
                $e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return ['status' => 401, 'reason' => 'Something went wrong. Try again later'];
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $token = base64_decode($request->token);
            $token_data = explode('#', $token);

            $resetData = DB::table('password_resets')->where('email', $token_data[1])->orderBy('id', 'DESC')->first();
            if (empty($resetData)) {
                return redirect('error_404');
            }
            if ($request->token != $resetData->token) {
                return redirect('error_404');
            }
            if ($resetData->is_used == 1) {
                return redirect('error_404');
            }
            $hourdiff = round((strtotime(date('Y-m-d h:i:s')) - strtotime($token_data[2])) / 3600, 1);
            if ($hourdiff > 72) {
                return redirect('error_404');
            }

            $user = User::where('id', $token_data[0])->where('status', 'active')->first();
            if (empty($user)) {
                return redirect('error_404');
            }
            return view('auth/reset_password', compact('user'));
        } catch (Exception $e) {
            SendMails::sendErrorMail($e->getMessage(), null, 'UserController', 'resetPassword', $e->getLine(),
                $e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return back();
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = User::where('id', $request->user_id)->first();
            $user->password = bcrypt($request->new_password);
            $user->app_pass = base64_encode($request->new_password);
            $user->save();

            $resetData = DB::table('password_resets')
                ->where('email', $user->email)->orderBy('id', 'DESC')->limit(1)->update(['is_used' => 1]);

            return ['status' => 200, 'reason' => 'User password updated successfully'];
        } catch (Exception $e) {
            SendMails::sendErrorMail($e->getMessage(), null, 'UserController', 'updatePassword', $e->getLine(),
                $e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return ['status' => 401, 'reason' => 'Something went wrong. Try again later'];
        }
    }

    /**
     * Store user badge
     *
     * @return success message
     */
    public function storeUserBadge(Request $request)
    {
        $badge = new Badge();
        $badge->user_id = $request->user_id;
        $badge->badge_no = $request->badge_no;
        $badge->save();

        return ['status' => 200, 'reason' => 'Successfully added', 'badge' => $badge];
    }

    /**
     * Delete user badge
     *
     * @return success message
     */
    public function deleteUserBadge(Request $request)
    {
        $badge = Badge::where('id', $request->badge_id)->first();
        $badge->deleted_at = date('Y-m-d h:i:s');
        $badge->save();

        return ['status' => 200, 'reason' => 'Successfully deleted'];
    }
}
