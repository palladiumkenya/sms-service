<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Apps extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $table = 'tbl_apps_settings';
    public $timestamps = false;
    public $incrementing = false;
   
    protected $fillable = [
        'app_description', 'callback_url','api_key'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
  
}
