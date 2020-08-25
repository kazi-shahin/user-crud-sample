<?php


namespace Modules\user\services;

use App\Helpers\ImageHelper;
use App\Helpers\JsonHelper;
use App\Helpers\XPressLog;
use App\Services\MessageService;
use App\Services\GlobalService;
use App\Services\PictureService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\company\services\CompanyService;
use Modules\group\services\GroupService;
use Modules\login\services\PasswordEncryption;
use Modules\role\models\RoleModel;
use Modules\role\services\RoleService;
use Modules\timeZone\models\TimeZoneModel;
use Modules\timeZone\services\TimeZoneService;
use Modules\user\models\GroupUserModel;
use Modules\user\models\PictureModel;
use Modules\user\models\TimeZoneUserModel;
use Modules\user\models\UserModel;
use Modules\user\models\UserZoneModel;
use Modules\zone\services\ZoneService;

class UserDataService
{
    protected $userModel;
    protected $companyService;
    protected $roleService;
    protected $zoneService;
    protected $groupService;
    protected $messageService;
    protected $timeZoneService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->companyService = new CompanyService();
        $this->roleService = new RoleService();
        $this->zoneService = new ZoneService();
        $this->groupService = new GroupService();
        $this->messageService = new MessageService();
        $this->timeZoneService = new TimeZoneService();
    }


    /**
     * Get Company, Group, Role, Zone Lists
     * @param $userObjectLists
     * @return mixed
     */
    public function getCompanyGroupRoleZoneLists()
    {
        $lists = array(
            "companyLists"          => $this->companyService->getAllCacheDataCompanyLists(),
            "roleLists"             => $this->roleService->getAllCacheDataRoleLists(),
            "zoneLists"             => $this->zoneService->getAllCacheDataZoneLists(),
            "groupLists"            => $this->groupService->getAllCacheDataGroupLists(),
            "timeZoneLists"         => $this->timeZoneService->getAllCacheDataTimeZoneLists()
        );

        return $lists;
    }

    /**
     * Get User Last Array From ArrayList
     * @param $userObjectLists
     * @return mixed
     */
    public function getUserLastArrayFromArrayList($userObjectLists)
    {
        if ($userObjectLists) {
            $userArrayLists = $userObjectLists->toArray();
            $lastUserArray = reset($userArrayLists);

            return $lastUserArray;
        }
    }


    /**
     * Get User All and last Array User Details
     * @param string $search
     * @return array
     */
    public function getUserFilterLists($search = "")
    {
        $userId         = "";
        $userPhoto      = "";
        $userLists = $this->userModel->selectAllUserLists($search);

        if ($userLists->getCollection()) {
            $userDetails    = $this->getUserLastArrayFromArrayList($userLists->getCollection());
            $userId         = $userDetails["id"];
        }

        $userDetails        = $this->userModel->getUserDetails($userId);
        if($userDetails != null){
            if(JsonHelper::getCurrentApplicationType() == "XPressTools"){
                $userPhoto      = ImageHelper::displayUserImage($userDetails == "" ? "" : $userDetails->userPhoto()["picture"], $userDetails->id, "user");
            }else{
                $userPhoto      = $this->userModel->displayUserImage($userDetails == "" ? "" : $userDetails->userPhoto());
            }
        }
        $checkOuts = $this->userModel->getUserCheckOutLists($userId);

        return array(
            "userLists"     => $userLists,
            "userDetails"   => $userDetails,
            "userPhoto"     => $userPhoto,
            "checkOuts"     => $checkOuts,
        );

    }

    /**
     * Get All Cache Data User Lists
     * @return mixed|string
     */
    public function getAllCacheDataUserLists()
    {
        $cacheUserLists = config("cacheName")["user_lists"];

        $listData = GlobalService::cacheHasData($cacheUserLists);
        if (!$listData) {
            $list = $this->userModel->getAllUserLists();
            $listData = GlobalService::addCacheData($cacheUserLists, $list);
        }
        return $listData;
    }

    /**
     * Unique Value Check in User Table
     * @param $column
     * @param $value
     * @param string $user_id
     * @return bool
     */
    public function userColumnExists($column, $value, $userId = "")
    {
        if ($value) {
            $userColumnExists = UserModel::where($column, $value);
            if ($userId) {
                $userColumnExists = $userColumnExists->where("id", "!=", $userId);
            }
            if ($userColumnExists->count() >= 1) {
                return true;
            }
        }
        return false;
    }


    /**
     * Unique Value Check in User Table
     *
     * @param $user_name
     * @param string $user_id
     * @return bool
     */
    public function userColumnUniqueValidationMessage($column, $value, $userId)
    {
        return $this->userColumnExists($column, $value, $userId);
    }


    /**
     * User Name And Password Check Per Role Condition is_admin or can_login_guard_app true
     *
     * @param $role_id
     * @return bool
     */
    public function userNameAndPasswordCheckAsPerRoleCondition($role_id)
    {
        if ($role_id) {
            $roleExists = RoleModel::where(["id" => $role_id, "is_admin" => true])
                ->orWhere("can_login_guard_app", true)
                ->first();
            if ($roleExists) {
                return true;
            }
        }
        return false;
    }

    /**
     * User Image Upload
     *
     * @param $userImage
     * @param $user_id
     */
    public function uploadUserImage($userImage, $userId)
    {
        if ($userImage) {
            try {

                $pictureArray = array();
                $pictureData = PictureModel::where("user_id", $userId)->first();

                if ($pictureData == null) {
                    $lastPicture = PictureModel::select("id")->orderBy("id", "Desc")->first();
                    $pictureArray["id"] = $lastPicture == "" ? 1 : $lastPicture->id + 1;
                } else {
                    $pictureId = $pictureData->id;
                }

                //Image Convert
                $pictureService = new PictureService();
                $image = $pictureService->imageConvertToVarBinary($userImage);

                //Data Set in $pictureArray
                $pictureArray["user_id"]        = $userId;
                $pictureArray["picture"]        = $image;
                $pictureArray["thumbnail"]      = $image;
                $pictureArray["tiny"]           = $image;

                if ($pictureData) {
                    //Update
                    PictureModel::where('id', $pictureId)
                        ->update($pictureArray);
                } else {
                    //Save
                    DB::unprepared('SET IDENTITY_INSERT pictures ON');
                    $pictureId = PictureModel::insertGetId($pictureArray);
                    DB::unprepared('SET IDENTITY_INSERT pictures OFF');

                    //User
                    UserModel::where("id", $userId)->update([
                        "picture_id" => $pictureId
                    ]);
                }

                return $pictureId;

            } catch (Exception $ex) {
                $this->messageService->throwException("uploadUserImage", $ex->getMessage());
            }
        }
    }


    /**
     * Delete Group User Access
     *
     * @param $user_id
     * @param $zone_permissions
     */
    public function deleteGroupUserAccess($user_id)
    {
        GroupUserModel::where('user_id', $user_id)->delete();
    }


    /**
     * group User Validation
     * @param $column
     * @param $value
     * @param string $user_id
     * @return bool
     */
    public function groupUserValidation($userId, $groupId)
    {
        $exists = GroupUserModel::where([
            "group_id"      => $groupId,
            "user_id"       => $userId
        ])->count();

        if ($exists >= 1) {
            return true;
        }
        return false;
    }

    /**
     * Store Access Group
     *
     * @param $user_id
     * @param $access_groups
     */
    public function storeGroupUserAccess($groupId, $userId)
    {
        if ($groupId != "" && $userId != "") {
            try {
                DB::unprepared('SET IDENTITY_INSERT groups_users ON');
                $lasUserGroup = GroupUserModel::orderBy('id', 'DESC')->first();

                $groupUser              = new GroupUserModel();

                $groupUser->id          = $lasUserGroup == "" ? 1 : $lasUserGroup->id + 1;
                $groupUser->user_id     = $userId;
                $groupUser->group_id    = $groupId;
                $groupUser->created_at  = now()->toDateTimeString();
                $groupUser->save();

                DB::unprepared('SET IDENTITY_INSERT groups_users OFF');

                return $groupUser;
            } catch (Exception $ex) {
                $this->messageService->throwException("storeGroupUserAccess" , $ex->getMessage());
            }
        }
    }

    /**
     * Delete User Access Group
     *
     * @param $user_id
     * @param $timezonePermissions
     */
    public function deleteUserGroupPermission($groupId, $userId)
    {
        try {
            $userGroup = GroupUserModel::where([
                'user_id'       => $userId,
                'group_id'      => $groupId,
            ])->first();

            if ($userGroup) {
                $userGroup->delete();
            }

        } catch (Exception $ex) {
            $this->messageService->throwException("deleteUserGroupPermission", $ex->getMessage());
        }
    }


    /**
     * User Zone Validation
     * @param $column
     * @param $value
     * @param string $user_id
     * @return bool
     */
    public function userZoneValidation($userId, $zoneId)
    {
        $exists = UserZoneModel::where([
            "zone_id"       => $zoneId,
            "user_id"       => $userId
        ])->count();

        if ($exists >= 1) {
            return true;
        }
        return false;
    }


    /**
     * Store User Zone Permission
     *
     * @param $user_id
     * @param $timezonePermissions
     */
    public function storeUserZonePermission($zoneId, $userId)
    {

        if ($zoneId != "" && $userId != "") {
            try {
                DB::unprepared('SET IDENTITY_INSERT users_zones ON');

                $lastUserZone = UserZoneModel::orderBy('id', 'DESC')->first();

                $userZoneModel                  = new UserZoneModel();

                $userZoneModel->id              = $lastUserZone == "" ? 1 : $lastUserZone->id + 1;
                $userZoneModel->user_id         = $userId;
                $userZoneModel->zone_id         = $zoneId;
                $userZoneModel->created_at      = now()->toDateTimeString();
                $userZoneModel->save();

                DB::unprepared('SET IDENTITY_INSERT users_zones OFF');
                return $userZoneModel;
            } catch (Exception $ex) {
                $this->messageService->throwException("storeUserZonePermission", $ex->getMessage());
            }
        }
    }

    /**
     * Delete User Zone Permission
     *
     * @param $user_id
     * @param $timezonePermissions
     */
    public function deleteUserZonePermission($userZoneId, $userId)
    {
        try {
            $userZone = UserZoneModel::where([
                'user_id'       => $userId,
                'zone_id'       => $userZoneId,
            ])->first();
            if ($userZone) {
                $userZone->delete();
            }

        } catch (Exception $ex) {
            $this->messageService->throwException("deleteUserZonePermission", $ex->getMessage());
        }
    }


    /**
     * User Time Zone Validation
     * @param $column
     * @param $value
     * @param string $user_id
     * @return bool
     */
    public function userTimeZoneValidation($userId, $timeZoneId)
    {
        $exists = TimeZoneUserModel::where([
            "timezone_id"       => $timeZoneId,
            "user_id"           => $userId
        ])->count();

        if ($exists >= 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete  User Time Zone Validation
     *
     * @param $user_id
     * @param $timezonePermissions
     */
    public function deleteUserTimeZonePermission($userTimeZoneId, $userId)
    {
        try {
            $userTimeZone = TimeZoneUserModel::where([
                'user_id'       => $userId,
                'timezone_id'   => $userTimeZoneId,
            ])->first();
            if ($userTimeZone) {
                $userTimeZone->delete();
            }

        } catch (Exception $ex) {
            $this->messageService->throwException("deleteUserTimeZonePermission", $ex->getMessage());
        }
    }

    /**
     * Store Timezone Permission
     *
     * @param $user_id
     * @param $timezonePermissions
     */
    public function storeTimezonePermission($timeZoneId, $userId)
    {

        if ($timeZoneId != "" && $userId != "") {
            try {

                DB::unprepared('SET IDENTITY_INSERT timezones_users ON');

                $lastUserTimeZone = TimeZoneUserModel::orderBy('id', 'DESC')->first();

                $userTimeZone               = new TimeZoneUserModel();

                $userTimeZone->id           = $lastUserTimeZone == "" ? 1 : $lastUserTimeZone->id + 1;
                $userTimeZone->user_id      = $userId;
                $userTimeZone->timezone_id  = $timeZoneId;
                $userTimeZone->created_at   = now()->toDateTimeString();
                $userTimeZone->save();

                DB::unprepared('SET IDENTITY_INSERT timezones_users OFF');

                return $userTimeZone;

            } catch (Exception $ex) {
                $this->messageService->throwException("storeTimezonePermission", $ex->getMessage());
            }
        }
    }


    /**
     * User Credentials Create/ Update
     *
     * @param $userData
     */
    public function userCredentialUpdate($userId, $roleId, $userName, $userPassword)
    {
        if ($userId) {
            try {
                $user = UserModel::where("id", $userId)->first();
                dd($this->userNameAndPasswordCheckAsPerRoleCondition($roleId));
                if ($this->userNameAndPasswordCheckAsPerRoleCondition($roleId) == true) {
                    $user->login = $userName;
                    if ($userPassword != "") {
                        $passwordEncryption     = new PasswordEncryption();
                        $s_pass                 = $passwordEncryption->createSalt($userName);
                        $c_pass                 = $passwordEncryption->createCSalt($s_pass, $userPassword);
                        $user->salt             = $s_pass;
                        $user->crypted_password = $c_pass;
                    }
                } else {
                    $user->login = "";
                    $user->salt = "";
                    $user->crypted_password = "";
                }
                $user->save();
            } catch (Exception $ex) {
                $this->messageService->throwException("userCredentialUpdate", $ex->getMessage());
            }
        }

    }


    /**
     * Store/Update User Data
     *
     * @param $request
     * @param string $user_id
     * @return mixed
     */
    public function storeUserData($userArray, $userId = "")
    {
        if ($userArray) {
            try {

                if ($userId) {
                    $user = UserModel::where('id', $userId)->first();
                    $oldZoneId = $user->current_zone_id;
                    $userArray["updated_at"]                = now()->toDateTimeString();
                } else {
                    $user = new UserModel();
                    $lastUser = UserModel::orderBy("id", "Desc")->first();
                    $userArray["id"]                        = $lastUser == "" ? 1 : $lastUser->id + 1;
                    $oldZoneId = "";
                    $userArray["created_at"]                = now()->toDateTimeString();
                }

                $userArray["current_zone_id"]               = $userArray["current_zone_id"];
                $userArray["is_visitor"]                    = $userArray["is_visitor"] ?? 0;
                $userArray["is_host"]                       = $userArray["is_host"] ?? 0;
                $userArray["start_date"]                    = GlobalService::dateFormat($userArray["start_date"]) ?? "";
                $userArray["end_date"]                      = GlobalService::dateFormat($userArray["end_date"]) ?? "";
                $userArray["alerts_on_data_manager"]        = $userArray["alerts_on_data_manager"] ?? 0;
                $userArray["alerts_on_muster"]              = $userArray["alerts_on_muster"] ?? 0;
                $userArray["alerts_on_rfid"]                = $userArray["alerts_on_rfid"] ?? 0;
                $userArray["state"]                         = $userArray["address_state"];
                $userArray["deleted_at"]                    = null;

                if (($oldZoneId != $userArray["current_zone_id"])) {
                    $userArray["current_zone_timestamp"]    = now()->toDateTimeString();// . ".0000000";
                    $userArray["zone_updated_at"]           = now()->toDateTimeString();// . ".0000000";
                }

                //Unset _token, user_id
                unset($userArray["_token"],
                    $userArray["group_id"],
                    $userArray["zone_id"],
                    $userArray["time_zone_id"],
                    $userArray["updated_by"],
                    $userArray["user_id"],
                    $userArray["action"],
                    $userArray["user_name"],
                    $userArray["user_password"],
                    $userArray["user_confirm_password"],
                    $userArray["image"],
                    $userArray["user_photo"],
                    $userArray["access_group"],
                    $userArray["zone_permission"],
                    $userArray["timezone_permission"],
                    $userArray['user_field_data_1'],
                    $userArray['user_field_data_2'],
                    $userArray['user_field_data_3'],
                    $userArray['user_field_data_4'],
                    $userArray['user_field_data_5'],
                    $userArray['user_field_data_6'],
                    $userArray['user_field_data_7'],
                    $userArray['user_field_data_8'],
                    $userArray['user_field_data_9'],
                    $userArray['user_field_data_10']
                );

                if ($userId) {
                    //Update Exists User
                    UserModel::where('id', $userId)->update($userArray);
                } else {
                    //Insert User
                    DB::unprepared('SET IDENTITY_INSERT users ON');
                    $user = UserModel::create($userArray);
                    DB::unprepared('SET IDENTITY_INSERT users OFF');
                }
                return $user;

            } catch (Exception $ex) {
                $this->messageService->throwException("storeUserData", $ex->getMessage());
            }
        }
    }

    /**
     * Delete  User
     *
     * @param $user_id
     * @param $timezonePermissions
     */
    public function deleteUser($userId)
    {
        try {
            $user = UserModel::where([
                'id' => $userId,
            ])->first();
            if ($user) {
                $user->delete();
            }

        } catch (Exception $ex) {
            $this->messageService->throwException("deleteUser", $ex->getMessage());
        }
    }

}
