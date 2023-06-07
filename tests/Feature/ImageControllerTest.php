<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Enums\ImageTypeEnum;
use App\Models\User;

class ImageControllerTest extends TestCase
{

    /**
     * Create Image
     * Success
     */
    public function testImageCreate()
    {
        $token = $this->getApiToken();

        $user = User::all()->first();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
            ])->post('/api/cms/images', [
                'file' => $file = UploadedFile::fake()->image('post.jpg'),
                'type' => ImageTypeEnum::AVATAR->value,
                'imageable_type' => 'App\Models\User',
                'imageable_id' => $user->id,
                'title' => 'Tesz image',
            ]);

            $this->validateSuccessResponse($response, 'images', 1); 
        }


    /*c
v1
      $this->actingAs($admin)
        ->post(route('posts.store'), [
            'title' => 'how to write a clean code',
            'desc' => 'description of the post',
            'image' => $file = UploadedFile::fake()->image('post.jpg'),
            'tag_id' => Tag::factory()->create()->id,
        ]);
v2
        Storage::fake('avatars');
 
        $file = UploadedFile::fake()->image('avatar.jpg');
 
        $response = $this->post('/avatar', [
            'avatar' => $file,
        ]);

v3
    $file = UploadedFile::fake()->image('image_one.jpg');
Storage::fake('public');

// Somewhere in your controller
$image = Image::make($file)
        ->resize(1200, null)
        ->encode('jpg', 80);

Storage::disk('public')->put('images/' . $file->hashName(), $image);

// back in your test
Storage::disk('public')->assertExists('images/' . $file->hashName());
*/
    /**
     * Create Image
     * Error - missing file content
     */
    public function testImageCreateMissingFileContent()
    {

    }

    /**
     * Create Image 
     * Error - missing type 
     */
    public function testImageCreateMissingType()
    {

    }

    /**
     * Create Image 
     * Error - missing imageable object 
     */
    public function testImageCreateMissingImageableObject()
    {

    }

}
