<?php


namespace Modules\user\models;


use App\Services\DirectoryService;
use App\Services\GlobalService;
use App\Services\MessageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\borrowing_activity\models\BorrowingActivityModel;
use Modules\company\models\CompanyModel;
use Modules\login\services\PasswordEncryption;
use Modules\role\models\RoleModel;
use Modules\user\services\UserDataService;

class UserModel extends Model
{
    protected $messageService;
    public function __construct(array $attributes = [])
    {
        $this->messageService = new MessageService();
    }

    public $timestamps = false;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'external_id',
        'external_data',
        'external_data_2',
        'updated_at',
        'current_zone_id',
        'current_zone_timestamp',
        'last_seen_timestamp',
        'zone_updated_at',
        'current_room_id',
        'current_location_id',
        'deleted_at',
        'guid',
        'employee_no',
        'active',
        'first_name',
        'last_name',
        'mi',
        'login',
        'company_id',
        'email',
        'telephone',
        'crypted_password',
        'salt',
        'created_by',
        'updated_by',
        'created_at',
        'role_id',
        'remember_token',
        'remember_token_expires_at',
        'picture_id',
        'drivers_license_number',
        'drivers_license_state',
        'telephone_cell',
        'address_country',
        'address_zip',
        'address_city',
        'address_state',
        'title',
        'salutation',
        'gender',
        'address_line_1',
        'address_line_2',
        'state',
        'emergency_creation',
        'start_date',
        'end_date',
        /*'user_field_data_1',
        'user_field_data_2',
        'user_field_data_3',
        'user_field_data_4',
        'user_field_data_5',
        'user_field_data_6',
        'user_field_data_7',
        'user_field_data_8',
        'user_field_data_9',
        'user_field_data_10',*/
        'watch_list_id',
        'alerts_on_data_manager',
        'alerts_on_muster',
        'alerts_on_rfid',
        'is_visitor',
        'is_host',
        'host_id',
        'nda_signed',
        'raw_scan',
        'fp1',
        'fp2',
        'id_image',
        'record_dirty',
        'record_created',
    ];


    public function companies()
    {
        return $this->belongsTo(CompanyModel::class,'company_id');
    }

    /**
     * User Photo
     * @return BelongsTo
     */
    public function userPhoto()
    {
        $instance = $this->belongsTo(PictureModel::class, 'picture_id', 'id');
        return $instance->first();
    }

    /**
     * Group Users
     * @return Collection
     */
    public function groupUsers()
    {
        $instance = $this->hasMany(GroupUserModel::class, 'user_id');
        return $instance->get()->pluck('group_id');
    }

    /**
     * User Zones
     *
     * @return BelongsTo
     */
    public function userZones()
    {
        $instance = $this->hasMany(UserZoneModel::class, 'user_id');
        return $instance->get()->pluck('zone_id');
    }

    /**
     * User Time Zones
     *
     * @return BelongsTo
     */
    public function userTimeZones()
    {
        $instance = $this->hasMany(TimeZoneUserModel::class, 'user_id');
        $instance = $instance->select(
            'timezones_users.id as timezone_user_id',
            'timezones.id as timezone_id',
            'timezones.name as timezone_name',
            'timezone_dates.start_date',
            'timezone_dates.end_date',
            'timezone_intervals.start_time',
            'timezone_intervals.end_time',
            'timezone_intervals.monday',
            'timezone_intervals.tuesday',
            'timezone_intervals.wednesday',
            'timezone_intervals.thursday',
            'timezone_intervals.friday',
            'timezone_intervals.saturday',
            'timezone_intervals.sunday'
        );
        $instance = $instance->join('timezones', 'timezones.id', '=', 'timezones_users.timezone_id');
        $instance = $instance->join('timezone_dates', 'timezone_dates.timezone_id', '=', 'timezones.id');
        $instance = $instance->join('timezone_intervals', 'timezone_intervals.timezone_id', '=', 'timezones.id');
        return $instance->get();
    }

    /**
     * Get Role
     *
     * @return BelongsTo
     */
    public function userRole()
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    public function check_outes ()
    {
        $instance = $this->hasMany(BorrowingActivityModel::class,'user_id','id');
        $instance = $instance->select('borrowing_activities.*','catalog_items.description as catalog_item_name','items.id as item_id','items.stock_no','items.serial_number','items.item_type','item_statuses.name as status_name');
        $instance = $instance->join('items','items.id','=','borrowing_activities.item_id');
        $instance = $instance->join('catalog_items','catalog_items.id','=','items.catalog_item_id');
        $instance = $instance->join('item_statuses','item_statuses.id','=','items.status');
        $instance = $instance->where('activity_type_id',0);
        $instance = $instance->orderBy('borrowing_activities.id','DESC');
        return $instance;
    }


    public function getUserCheckOutLists ($userId)
    {
        return BorrowingActivityModel::select('borrowing_activities.*','catalog_items.description as catalog_item_name',
            'items.id as item_id','items.stock_no','items.serial_number','items.item_type','item_statuses.name as status_name')
            ->join('items','items.id','=','borrowing_activities.item_id')
            ->join('catalog_items','catalog_items.id','=','items.catalog_item_id')
            ->join('item_statuses','item_statuses.id','=','items.status')
            ->where('user_id',$userId)
            ->where('activity_type_id',0)
            ->orderBy('borrowing_activities.id','DESC')->get();
    }

    /**
     * Select All User Lists
     *
     * @return mixed
     */
    public function getAllUserLists()
    {
        return self::get();
    }

    /**
     * Select All user Lists
     *
     * @return mixed
     */
    public function selectAllUserLists($search = "")
    {
        $user = self::select(
            'id',
            'employee_no',
            'first_name',
            'last_name',
            'mi',
            'login'
        )
            ->orderBy('last_name', 'ASC')
            ->whereNull('deleted_at');

        if ($search != "") {
            $user = $user->where('last_name', 'LIKE', '%' . $search . '%')
                ->orWhere('first_name', 'LIKE', '%' . $search . '%')
                ->orWhere('employee_no', 'LIKE', '%' . $search . '%')
                ->orWhere('mi', 'LIKE', '%' . $search . '%')
                ->orWhere('login', 'LIKE', '%' . $search . '%');
        }
        $user = $user->paginate(10);
        return $user;
    }


    /**
     * Get User Details
     * @param $userId
     * @return mixed
     */
    public function getUserDetails($userId)
    {
        if ($userId != "") {
            return self::where("id", $userId)->whereNull('deleted_at')->first();
        }
    }

    /**
     * User Login Query
     *
     * @param $username
     * @return mixed
     */
    public function userLoginQuery($username)
    {
        return self::with("userRole")
            ->where("login", $username)
            ->whereNull('deleted_at');
    }

    /**
     * Get User Login Details
     *
     * @param $username
     * @return mixed
     */
    public function getUserLoginDetails($username)
    {
        return self::userLoginQuery($username)->first();
    }


    /**
     * Check User Login Role Exists
     * @param $username
     */
    public function checkUserLoginRoleExists($username)
    {
        return self::userLoginQuery($username)->whereHas('userRole', function ($q) {
            $q->where('is_admin', true);
            $q->orWhere('can_login_guard_app', true);
        })->first();
    }

    /**
     * Display User Photo
     * @param $picture
     * @return string
     */
    public function displayUserImage($image)
    {
        $fullPath = asset('assets/images/items/noimage.jpg');

        if ($image) {
            $path = "/images/";

            //Directory
            $directory = new DirectoryService();
            $directory->directory(public_path($path));

            //Full File Path
            $fullPath = $path . $image['id'] . ".jpg";

            //Unlike
            if(is_file( $fullPath )) {
                unlink( $fullPath );
            }

            $fileOpen = fopen(public_path($fullPath), 'wa+');

            fwrite($fileOpen, $image["thumbnail"]);

            fclose($fileOpen);
        }
        return $fullPath;
    }

    /**
     * Store/Update User Data
     *
     * @param $request
     * @param string $user_id
     * @return mixed
     */
    public function storeToolsUserData($userArray, $userId = "")
    {
        if ($userArray) {
            try {

                if ($userId) {
                    $user = self::where('id', $userId)->first();
                    $userArray["updated_at"]                = now()->toDateTimeString();
                } else {
                    $user = new UserModel();
                    $userArray["created_at"]                = now()->toDateTimeString();
                }

                $userArray["deleted_at"]                    = null;

                //Unset _token, user_id
                unset($userArray["_token"],
                    $userArray["updated_by"],
                    $userArray["user_id"],
                    $userArray["action"],
                    $userArray["user_name"],
                    $userArray["user_password"],
                    $userArray["user_confirm_password"],
                    $userArray["image"],
                    $userArray["user_photo"]
                );

                if ($userId) {
                    //Update Exists User
                    self::where('id', $userId)->update($userArray);
                } else {
                    //Insert User
                    self::insert($userArray);
                    $userId = DB::getPdo()->lastInsertId();
                    $user = self::where('id', $userId)->first();
                }
                return $user;

            } catch (Exception $ex) {
                $this->messageService->throwException("storeUserData", $ex->getMessage());
            }
        }
    }

    /**
     * User Credentials Create/ Update
     *
     * @param $userData
     */
    public function userToolsCredentialUpdate($userId, $roleId, $userName, $userPassword)
    {
        if ($userId) {
            try {
                $user = UserModel::where("id", $userId)->first();

                    if ($userPassword != "") {
                        $passwordEncryption     = new PasswordEncryption();
                        $s_pass                 = $passwordEncryption->createSalt($userName);
                        $c_pass                 = $passwordEncryption->createCSalt($s_pass, $userPassword);
                        $user->login            = $userName;
                        $user->salt             = $s_pass;
                        $user->crypted_password = $c_pass;
                    }
                    else {
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
     * User Image Upload
     *
     * @param $userImage
     * @param $user_id
     */
    public function uploadToolsUserImage($userImage, $userId)
    {
        if ($userImage) {
            try {

                $pictureArray = array();
                $pictureData = PictureModel::where("user_id", $userId)->first();


                //Data Set in $pictureArray
                $pictureArray["user_id"]        = $userId;
                $pictureArray["picture"]        =  file_get_contents($userImage);;

                if ($pictureData != null) {
                    //Update
                    PictureModel::where('user_id', $userId)
                        ->update($pictureArray);

                    $pictureId = $pictureData->id;
                } else {
                    //Save

                    PictureModel::insert($pictureArray);
                    $pictureId = DB::getPdo()->lastInsertId();;

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

    public static function boot()
    {
        parent::boot();

        $userService = new UserDataService();

        $cacheUserLists = config("cacheName")["user_lists"];

        self::created(function ($model) use ($userService, $cacheUserLists) {
            GlobalService::cacheDataDelete($cacheUserLists);
            $userService->getAllCacheDataUserLists();
        });

        self::updated(function ($model) use ($userService, $cacheUserLists) {
            GlobalService::cacheDataDelete($cacheUserLists);
            $userService->getAllCacheDataUserLists();
        });

        self::deleted(function ($model) use ($userService, $cacheUserLists) {
            GlobalService::cacheDataDelete($cacheUserLists);
            $userService->getAllCacheDataUserLists();
        });
    }
}