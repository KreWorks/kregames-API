<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Enums\ImageTypeEnum;
use App\Models\User;
use App\Models\Image;

class ImageControllerTest extends TestCase
{
    /**
     * Image show
     * Success
     */
    public function testImageShow()
    {
        $token = $this->getApiToken();

        $image = Image::factory(Image::class)->create(
            $this->getImageData("show", ImageTypeEnum::AVATAR->value, User::all()->first())
        );
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/images/'.$image->id->ToString());

        $response->assertJsonPath('data.title', 'Show image');
        $response->assertJsonPath('data.path', 'show.jpg');
        $response->assertJsonPath('data.id', $image->id->ToString());
        $this->validateSuccessResponse($response, 'images', 1); 
    }

    /**
     * Image show
     * Error - image not found
     */
    public function testImageShowMissingImage()
    {
        $token = $this->getApiToken();

        $image = Image::factory(Image::class)->create(
            $this->getImageData("show", ImageTypeEnum::AVATAR->value, User::all()->first())
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/images/'."aa-bb-cc");

        $this->validateErrorResponse($response, ['image'], 404);
    }

    /**
     * Create Image
     * Success
     */
    public function testImageCreate()
    {
        $token = $this->getApiToken();

        $parent = User::all()->first();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images', 
            $this->getImageData("create", ImageTypeEnum::AVATAR->value, $parent, true)
        );

        $response->assertJsonPath('data.title', 'Create image');
        $imagePath = 'images/user/' . $parent->id .'/' . $parent->username . '_avatar.jpg';
        $response->assertJsonPath('data.path', $imagePath);

        $this->validateSuccessResponse($response, 'images', 1); 
    }

    /**
     * Create Image
     * Error - missing file content
     */
    public function testImageCreateMissingFileContent()
    {
        $token = $this->getApiToken();

        $data = $this->getImageData("create", ImageTypeEnum::AVATAR->value, User::all()->first(), true);
        unset($data['file']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images', 
            $data
        );

        $this->validateErrorResponse($response, ['file'], 400);
    }
    

    /**
     * Create Image 
     * Error - missing type 
     */
    public function testImageCreateMissingType()
    {
        $token = $this->getApiToken();

        $data = $this->getImageData("create", ImageTypeEnum::AVATAR->value, User::all()->first(), true);
        unset($data['type']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images', 
            $data
        );

        $this->validateErrorResponse($response, ['type'], 400) ;
    }
    
    /**
     * Create Image 
     * Error - invalid type 
     */
    public function testImageCreateInvalidType()
    {
        $token = $this->getApiToken();

        $data = $this->getImageData("create", ImageTypeEnum::AVATAR->value, User::all()->first(), true);
        $data['type'] = "kutya";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images', 
            $data
        );

        $this->validateErrorResponse($response, ['type'], 404) ;
    }

    /**
     * Create Image 
     * Error - missing imageable object datas
     */
    public function testImageCreateMissingImageableObject()
    {
        $token = $this->getApiToken();

        $data = $this->getImageData("create", ImageTypeEnum::AVATAR->value, null, true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images', 
            $data
        );

        $this->validateErrorResponse($response, ['imageable_type'], 400);

        $data = $this->getImageData("create2", ImageTypeEnum::AVATAR->value, null, true);
        $data['imageable_type'] = 'App\Models\User'; 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images',
            $data
        );

        $this->validateErrorResponse($response, ['imageable_id'], 400);
    }
    
    /**
     * Create Image 
     * Error - wrong imageable object 
     */
    public function testImageCreateWrongImageableObject()
    {
        $token = $this->getApiToken();

        $data = $this->getImageData("create", ImageTypeEnum::AVATAR->value, User::all()->first(), true);
        $data['imageable_id'] = Str::uuid();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/images', 
            $data
        );

        $this->validateErrorResponse($response, ['imageable_id'], 400);
    }

    /**
     * ImageController - list
     * Success
     */
    public function testImageList()
    {
        $token = $this->getApiToken();

        for($i = 1; $i <= 5; $i++) {
            $image = Image::factory(Image::class)->create(
                $this->getImageData("list".$i, ImageTypeEnum::AVATAR->value, User::all()->first())
            );
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/images');

        $response->assertJsonStructure([
            'meta' => ['count', 'entityType', 'headers'],
        ]);
        $this->validateSuccessResponse($response, 'images', 5);
    }

    /**
     * ImageController - Update 
     * Success
     */
    public function testImageUpdateSuccess()
    {
        $token = $this->getApiToken();

        $user = User::all()->first();
        $image = Image::factory(Image::class)->create(
            $this->getImageData("update", ImageTypeEnum::AVATAR->value, $user)
        );

        $data = $this->getImageData("update2", ImageTypeEnum::SCREENSHOT->value, null, true);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/images/'. $image->id->ToString(), 
            $data
        );

        $response->assertJsonPath('data.title', 'Update2 image');
        $response->assertJsonPath('data.type', ImageTypeEnum::SCREENSHOT->value);
        $imagePath = 'images/user/' . $user->id .'/' . $user->username . '_avatar.jpg';
        $response->assertJsonPath('data.path', $imagePath);

        $this->validateSuccessResponse($response, 'images', 1);
    }

    /**
     * ImageController - Update
     * Error - Wrong image type
     */
    public function testImageUpdateWrongType()
    {
        $token = $this->getApiToken();

        $user = User::all()->first();
        $image = Image::factory(Image::class)->create(
            $this->getImageData("update", ImageTypeEnum::AVATAR->value, $user)
        );

        $data = $this->getImageData("update3", ImageTypeEnum::SCREENSHOT->value, null, true);
        $data['type'] = "kutya";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/images/'. $image->id->ToString(), 
            $data
        );
        
        $this->validateErrorResponse($response, ['type'], 404);

    }

    /**
     * ImageControll - Update 
     * Error - Missing parent
     */
    public function testImageUpdateMissingParent()
    {
        $token = $this->getApiToken();

        $user = User::all()->first();
        $image = Image::factory(Image::class)->create(
            $this->getImageData("update", ImageTypeEnum::AVATAR->value, $user)
        );

        $data = $this->getImageData("update3", ImageTypeEnum::SCREENSHOT->value, null, true);
        $data['imageable_id'] = Str::uuid();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/images/'. $image->id->ToString(), 
            $data
        );
        
        $this->validateErrorResponse($response, ['imageable_id'], 400);
    }
    

    /**
     * Delete image
     * Success 
     */
    public function testImageDelete()
    {
        $token = $this->getApiToken();

        $user = User::all()->first();

        $image = Image::factory(Image::class)->create(
            $this->getImageData("delete", ImageTypeEnum::AVATAR->value, User::all()->first())
        );
        $imageId = $image->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/images/'.$imageId);

        $this->validateSuccessResponse($response, 'images', 0); 
    }

    /**
     * Delete image
     * Error - missing image 
     */
    public function testImageDeleteMissingImage()
    {
        $token = $this->getApiToken();

        $image = Image::factory(Image::class)->create(
            $this->getImageData("delete", ImageTypeEnum::AVATAR->value, User::all()->first())
        );
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/images/'."aa-bb-cc");

        $this->validateErrorResponse($response, ['image'], 404);
    }

    /**
     * Generate image data
     */
    protected function getImageData($name, $type, $parent = null, $needFile = false)
    {
        $data = [
            'type' => $type,
            'title' => ucwords($name) . ' image'
        ]; 
        
        if ($parent != null) {
            $data['imageable_type'] = get_class($parent);
            $data['imageable_id'] = $parent->id;
        }
        
        if ($needFile) {
            $data['file'] = UploadedFile::fake()->image('post.jpg');
        } else {
            $data['path'] = $name.'.jpg';
        }

        return $data;
    }
}
