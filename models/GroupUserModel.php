<?php


namespace Modules\user\models;


use App\Services\GlobalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\user\services\UserDataService;

class GroupUserModel extends Model
{
    protected $table = 'groups_users';

    public $primaryKey = 'id';
    
    public $timestamps = false;


}