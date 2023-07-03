<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Enums\ImageTypeEnum;
use App\Models\User;
use App\Models\Image;

class UserControllerTest extends TestCase
{
    /**
     * UserController - User show
     * Success
     */
    public function testUserShow()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create($this->getUserData('show', true, false));

        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/users/'.$userId);

        $response->assertJsonPath('data.username', 'showuser');
        $response->assertJsonPath('data.id', $userId);
        $this->validateSuccessResponse($response, 'users', 1);
    }

    /**
     * UserController - User show
     * Error - user not found
     */
    public function testUserShowNotFound()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create($this->getUserData("show", true, false));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/users/'."abc-abc-abc");

        $this->validateErrorResponse($response, ['user'], 404);
    }

    /**
     * UserController - Create User 
     * Success
     */
    public function testUserCreate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', 
            $this->getUserData('fancy', false, true, true)
        );

        $user = User::find($response->getData()->data->id);

        $this->assertEquals(get_class($user->avatar), Image::class);
        $this->validateSuccessResponse($response, 'users', 1);
        $response->assertJsonPath('data.username','fancyuser');
    }

    /**
     * UserController - Create user 
     * Error - missing image 
     */
    public function testUserCreateMissingImage()
    {
        $token = $this->getApiToken();

        $data = $this->getUserData('fancy', false, true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', 
            $data
        );
        
        $this->validateErrorResponse($response, ['avatar'], 400);
    }

    /**
     * UserController - Create user 
     * Error - duplicate nickanme 
     */
    public function testUserCreateDuplicateNickname()
    {
        $token = $this->getApiToken();

        $data = $this->getUserData('fancy', false, true, true);
        $data['username'] = 'admin';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', 
            $data
        );
        
        $this->validateErrorResponse($response, ['username'], 400);
    }

    /**
     * UserController - Create user 
     * Error - duplicate email
     */
    public function testUserCreateDuplicateEmail()
    {
        $token = $this->getApiToken();

        $data = $this->getUserData('fancy', false, true, true);
        $data['email'] = 'admin@admin.hu';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', 
            $data
        );
        
        $this->validateErrorResponse($response, ['email'], 400);
    }

    /**
     * UserController - Create user 
     * Error - password missmatch
     */
    public function testUserCreatePasswordMissmatch()
    {
        $token = $this->getApiToken();

        $data = $this->getUserData('fancy', false, true, true);
        $data['confirmPassword'] = 'notfancy';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', 
            $data
        );

        $this->validateErrorResponse($response, ['confirmPassword'], 400);
    }

    /**
     * UserController - Create User 
     * Error - short password
     */
    public function testUserCreateShortPassword()
    {
        $token = $this->getApiToken();

        $data = $this->getUserData('fancy', false, true, true);
        $data['password'] = 'pwd';
        $data['confirmPassword'] = "pwd";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', 
            $data
        );

        $this->validateErrorResponse($response, ['password'], 400);
    }

    /**
     * UserContrller - User List 
     * Success
     */
    public function testUserList() 
    {
        $token = $this->getApiToken();

        for($i = 1; $i <= 5; $i++) {
            $user = User::factory(User::class)->create(
                $this->getUserData("list".$i, true, false)
            );

            $user->images()->create([
                'type' => ImageTypeEnum::AVATAR,
                'path' => 'bla.jpg',
                'title' => $user->username. " avatar"
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/users');

        $response->assertJsonStructure([
            'meta' => ['count', 'entityType', 'headers'],
        ]);
        $this->validateSuccessResponse($response, 'users', 6);
    }

    /**
     * UserController - Update user
     * Success - user data update
     */
    public function testUserUpdate()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'name' => 'Update User 2',
            'username' => 'updateuser2',
            'email' => 'update2@mail.hu'
        ]);

        $response->assertJsonPath('data.id',$userId);
        $response->assertJsonPath('data.username','updateuser2');
        $response->assertJsonPath('data.name','Update User 2');
        $response->assertJsonPath('data.email','update2@mail.hu');
        $this->validateSuccessResponse($response, 'users', 1);
    }

    /**
     * UserController - Update user
     * Success - user data update
     */
    public function testUserUpdateImage()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );

        $user->images()->create([
            'type' => ImageTypeEnum::AVATAR,
            'path' => 'bla.jpg',
            'title' => $user->username. " avatar"
        ]);

        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'avatar' => UploadedFile::fake()->image('avatar.jpg')
        ]);

        $this->assertEquals(count($user->images), 1);

        $this->validateSuccessResponse($response, 'users', 1);
    }

    /**
     * UserController - Update user 
     * Success - only password
     */
    public function testUserUpdateOnlyPassword()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'password' => 'password1',
            'confirmPassword' => 'password1'
        ]);

        $response->assertJsonPath('data.id',$userId);
        $this->validateSuccessResponse($response, 'users', 1);
    }

    /**
     * UserController - update user
     * Error - user not found
     */
    public function testUserUpdateNotFound()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'."aa-bb-cc", [
            'name' => 'Fancy user',
            'username' => 'admin',
        ]);

        $this->validateErrorResponse($response, ['user'], 404);
    }

    /**
     * UserController - update user
     * Error - duplicate nickname 
     */
    public function testUserUpdateDuplicateNickname()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'name' => 'Fancy user',
            'username' => 'admin',
        ]);

        $this->validateErrorResponse($response, ['username'], 400);
    }

    /**
     * UserController - update user 
     * Error - duplicate mail
     */
    public function testUserUpdateDuplicateEmail()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'name' => 'Fancy user',
            'email' => 'admin@admin.hu',
        ]);

        $this->validateErrorResponse($response, ['email'], 400);
    }

    /**
     * UserController - update user 
     * Error - password missmatch
     */
    public function testUserUpdatePasswordMissmatch()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'password' => 'egyikjelszo',
            'confirmPassword' => 'masikjelszo',
        ]);

        $this->validateErrorResponse($response, ['confirmPassword'], 400);
    }

    /**
     * UserController - Update user
     * Error - short password 
     */
    public function testUserUpdateShortPassword()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("update", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'password' => 'pwd',
            'confirmPassword' => 'pwd',
        ]);

        $this->validateErrorResponse($response, ['password'], 400);
    }

    /**
     * UserController - User delete
     * Success 
     */
    public function testUserDelete() 
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create(
            $this->getUserData("delete", true, false)
        );
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/users/'.$userId);

        
        $response->assertJsonPath('data',null);
        $this->validateSuccessResponse($response, 'users', 0);
    }

    /**
     * UserController - User delete
     * Error - missing user 
     */
    public function testUserDeleteMissingUser()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/users/'."aa-bb-cc");

        $this->validateErrorResponse($response, ['user'], 404);
    }

    /**
     * Create a user 
     */
    private function getUserData($name, $isFactory = false, $needConfirm = false, $needFile = false)
    {
        $data = [
            'email' => $name.'user@mail.com',
            'name' => ucwords($name) . " User",
            'username' => $name . 'user'
        ];
        $password = md5($name);
        if ($isFactory) {
            $password = Hash::make($password);
        }
        $data['password'] = $password;
        if ($needConfirm) {
            $data['confirmPassword'] = $password;
        }

        if ($needFile) {
            $data['avatar'] = UploadedFile::fake()->image('avatar.jpg');
        }

        return $data;
    }
}
