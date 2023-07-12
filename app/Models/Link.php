<?php

namespace App\Models;

use App\Models\_Base as Base;

class Link extends Base
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'linktype_id',
        'linkable_type',
        'linkable_id',
        'link',
        'display_text',
        'visible',
    ];

    protected $casts = [
        'linktype' => LinkTypes::class
    ];

    public static $morphs = [
        'Game' => Game::class,
        'User' => User::class
    ];

    public function getDeleteStringAttribute():string
    {
        return $this->link;
    }

    public static function getLinkables()
    {
        $linkables = []; 
        foreach(Link::$morphs as $key => $class)
        {
            $linkables[$key] = [
                'css-class' => strtolower($key), 
                'model' => $class,
                'items' => $class::all()
            ];
        }

        return $linkables;
    }

    /**
     * Get the parent imageable model (jam or game).
     */
    public function linkable()
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

    /** 
     * THe linktpye this link blongs to
     */
    public function linktype()
    {
        return $this->belongsTo(LinkType::class);
    }
}
