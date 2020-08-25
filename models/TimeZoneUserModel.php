<?php


namespace Modules\user\models;

use App\Services\GlobalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\user\services\UserDataService;

class TimeZoneUserModel extends Model
{
    protected $table = 'timezones_users';

    public $primaryKey = 'id';

}