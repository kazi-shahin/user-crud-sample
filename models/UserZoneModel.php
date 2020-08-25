<?php


namespace Modules\user\models;

use App\Services\GlobalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\user\services\UserDataService;

class UserZoneModel extends Model
{
    protected $table = 'users_zones';

    public $primaryKey = 'id';
    
    public $timestamps = false;


}