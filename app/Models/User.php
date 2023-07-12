<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Database\Factories\UserFactory;
use App\Enums\ImageTypeEnum;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * We need to create a uuid on create
     */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public $incrementing = false;
    
    public $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    protected static function factory() 
    {
        return UserFactory::new();
    }

    public function getDeleteStringAttribute()
    {
        return $this->name . " (".$this->username.")";
    }

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The games related to this user
     */
    public function games()
    {
        return $this->hasMany(Game::class);
    }

    /**
     * The images of the user
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * The avatar image of the user (expected to be only 1)
     */
    public function avatar()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', ImageTypeEnum::AVATAR);
    }

    /**
     * The links of the user
     */
    public function links()
    {
        return $this->morphMany(Link::class, 'linkable');
    }
}
