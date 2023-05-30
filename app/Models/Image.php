<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public const ICON = 'icon'; 
    public const SCREENSHOT = 'screenshot';

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
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'path',
        'title',
        'imageable_type',
        'imageable_id'
    ];

    public static $morphs = [
        'Game' => Game::class,
        'User' => User::class
    ];

    public static function getImageables()
    {
        $imageables = []; 
        foreach(Image::$morphs as $key => $class)
        {
            $imageables[$key] = [
                'css-class' => strtolower($key), 
                'model' => $class,
                'items' => $class::all()
            ];
        }

        return $imageables;
    }

    public static function getImageTypes()
    {
        return [
            self::ICON => 'icon',
            self::SCREENSHOT => 'screenshot'
        ];
    }

    /**
     * Get the parent imageable model (jam or game).
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    public static function getImageTypeList()
    {
        return [
            Image::ICON => 'Icon', 
            Image::SCREENSHOT => 'ScreenShot',
        ];
    }

    public static function getMorphList()
    {
        return [
            Game::class => 'játék', 
            Jam::class => 'jam',
            User::class => 'felhasználó'
        ];
    }

    public function getDeleteStringAttribute()
    {
        return $this->path . " (ID: ".$this->id.")";
    }

    public function getParentAttribute()
    {
        return $this->imageable_type;
    }
}