<?php
  
namespace App\Enums;

use App\Traits\EnumToArray;

enum ImageTypeEnum:string 
{
    use EnumToArray;

    case ICON = 'icon';
    case AVATAR = 'avatar';
    case SCREENSHOT = 'screenshot';
}
