<?php

namespace App\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Enums\ImageTypeEnum;
use App\Models\Image;

trait ImageableTrait 
{
    protected UploadedFile $file;
    protected string $fileFieldName;

    protected function handleImage(Request $request, $parent, $fileFieldName = 'file')
    {
        $this->fileFieldName = $fileFieldName;
        $this->file = $request->file($fileFieldName);

        // Generate folder 
        $class = get_class($parent);
        $folder = $this->generateFolder($class, $parent->id);

        // Generate filename 
        $filename = $this->generateFilename($request, $parent);

        // Store file 
        $path = $request->file($fileFieldName)->storeAs($folder, $filename);

        // Retrun path 
        return $path;
    }

    private function saveImageToDB($imageData, $parent, $fieldName)
    {
        $image = null;

        if ($fieldName == 'image' || $parent->__get($fieldName) == null) {
            $image = $parent->images()->create($imageData);
        } else {
            $image = Image::where('id', $parent->__get($fieldName)->id)->update($imageData);
        }

        return $image;
    }

    private function generateFilename(Request $request, $parent)
    {
        if (get_class($parent) == "App\Models\User") {
            return $parent->username."_avatar.".$this->getFileExtension();
        }

        if (get_class($parent) == 'App\Models\Game') {
            if ($request->type == ImageTypeEnum::ICON) {
                return $parent->slug."_icon.".$this->getFileExtension();
            }
            else if ($request->type == ImageTypeEnum::SCREENSHOT) {
                $count = $this->countScreenShots(get_class($parent), $parent);
                return $parent->slug."screenshot_".$count.".".$this->getFileExtension();
            }
        }

        return "nofile.".$this->getFileExtension();
    }

    private function countScreenShots($class, $parent)
    {
        $folder = $this->generateFolder($class, $parent->id);
        $files = scandir($folder);
        $count = 0; 
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && strpos($file, 'screenshot') > 0) {
                $count ++;
            }
        }

        return ($count + 1);
    }

    private function getFileExtension()
    {
        return $this->file->extension();
    }

    private function generateFolder($class, $parentId)
    {
        $onlyClass = strtolower(explode("\\", $class)[2]);

        $folder = "/images/" . $onlyClass . "/" . $parentId;

        $this->chechIfFolderExists($folder);

        return $folder;
    }

    private function chechIfFolderExists($folder)
    {
        $file = new Filesystem;

        if (!$file->isDirectory(storage_path($folder))) {
            $file->makeDirectory(storage_path($folder), 766, true, true);
        }
    }

    private function getSize()
    {
        $size = [
            ImageTypeEnum::ICON => [
                'x' => 300, 
                'y' => 300
            ],
            ImageTypeEnum::AVATAR => [
                'x' => 300, 
                'y' => 300
            ], 
            ImageTypeEnum::SCREENSHOT => [
                'x' => 800, 
                'y' => 450
            ]
        ];
    }

    public function validateImageType($type)
    {
        return in_array($type, ImageTypeEnum::values());
    }

//TODO
   /* function resize_image($file, $w, $h, $crop=FALSE) {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width-($width*abs($r-$w/$h)));
            } else {
                $height = ceil($height-($height*abs($r-$w/$h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w/$h > $r) {
                $newwidth = $h*$r;
                $newheight = $h;
            } else {
                $newheight = $w/$r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    
        return $dst;
    }*/
}