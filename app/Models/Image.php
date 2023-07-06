<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ImageFactory;
use App\Models\_Base as Base;
use App\Enums\ImageTypeEnum;

class Image extends Base
{
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

    protected $casts = [
        'type' => ImageTypeEnum::class
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

    /**
     * Get the parent imageable model (jam or game).
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    public static function getMorphList()
    {
        return [
            Game::class => 'játék', 
            User::class => 'felhasználó'
        ];
    }

    public function getDeleteStringAttribute(): string
    {
        return $this->path . " (ID: ".$this->id.")";
    }

    public function getParentAttribute()
    {
        return $this->imageable_type;
    }
}