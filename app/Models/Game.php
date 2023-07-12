<?php

namespace App\Models;

use App\Enums\ImageTypeEnum;
use App\Models\_Base as Base;

class Game extends Base
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'publish_date',
        'visible',
    ];

    public function getDeleteStringAttribute():string
    {
        return $this->name;
    }
    
    /** 
     * THe user this game blongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The images of the game
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * The icon image of the game (expected to be only 1)
     */
    public function icon()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', ImageTypeEnum::ICON);
    }

    /**
     * The links of the game
     */
    public function links()
    {
        return $this->morphMany(Link::class, 'linkable');
    }
}
