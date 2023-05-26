<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    /**
     * Create User 
     *
     * @return void
     */
    public function testUserCreate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', [
            'name' => 'Fancy user',
            'username' => 'fancyusername',
            'email' => 'fancy@mail.hu',
            'password' => ('password'),
            'confirmPassword' => 'password'
        ]);

        $response->assertJsonPath('data.username','fancyusername');
        $response->assertStatus(200);
    }

    /**
     * Create user with errors 
     */
    public function testUserCreateDuplicateNickname()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', [
            'name' => 'Fancy user',
            'username' => 'fancyusername',
            'email' => 'fancy2@mail.hu',
            'password' => ('password'),
            'confirmPassword' => 'password'
        ]);
        
        $response->assertJsonStructure([
            'status',
            'error' => ['username']
        ]);
        $response->assertStatus(400);
    }

    public function testUserCreateDuplicateEmail()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', [
            'name' => 'Fancy user',
            'username' => 'fancyusername2',
            'email' => 'fancy@mail.hu',
            'password' => ('password'),
            'confirmPassword' => 'password'
        ]);
        
        $response->assertJsonStructure([
            'status',
            'error' => ['email']
        ]);
        $response->assertStatus(400);
    }

    public function testUserCreatePasswordMissmatch()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', [
            'name' => 'Fancy user',
            'username' => 'fancyusername3',
            'email' => 'fancy3@mail.hu',
            'password' => ('password'),
            'confirmPassword' => 'password123'
        ]);

        $response->assertJsonStructure([
            'status',
            'error' => ['confirmPassword']
        ]);
        $response->assertStatus(400);
    }

    public function testUserCreateShortPassword()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/users', [
            'name' => 'Fancy user',
            'username' => 'fancyusername4',
            'email' => 'fancy4@mail.hu',
            'password' => ('pwd'),
            'confirmPassword' => 'pwd'
        ]);

        $response->assertJsonStructure([
            'status',
            'error' => ['password']
        ]);
        $response->assertStatus(400);
    }

    /**
     * Update user
     */
    public function testUserUpdate()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'update@mail.com',
            'name' => "Update User",
            'username' => 'updateuser',
            'password' => Hash::make('password'),
        ]);
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
        $response->assertStatus(200);
    }

    public function testUserUpdateOnlyPassword()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'update@mail.com',
            'name' => "Update User",
            'username' => 'updateuser',
            'password' => Hash::make('password'),
        ]);
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'password' => 'password1',
            'confirmPassword' => 'password1'
        ]);

        $response->assertJsonPath('data.id',$userId);
        $response->assertStatus(200);
    }

    /**
     * User update with errors 
     */
    public function testUserUpdateDuplicateNickname()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'error1@mail.com',
            'password' => Hash::make('password'),
        ]);
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'name' => 'Fancy user',
            'username' => 'testuser',
        ]);

        $response->assertJsonStructure([
            'status',
            'error' => ['username']
        ]);
        $response->assertStatus(400);
    }

    public function testUserUpdateDuplicateEmail()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'error2@mail.com',
            'password' => Hash::make('password'),
        ]);
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'name' => 'Fancy user',
            'email' => 'fancy@mail.hu',
        ]);

        
        $response->assertJsonStructure([
            'status',
            'error' => ['email']
        ]);
        $response->assertStatus(400);
    }

    public function testUserUpdatePasswordMissmatch()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'error3@mail.com',
            'password' => Hash::make('password'),
        ]);
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'password' => 'egyikjelszo',
            'confirmPassword' => 'masikjelszo',
        ]);

        
        $response->assertJsonStructure([
            'status',
            'error' => ['confirmPassword']
        ]);
        $response->assertStatus(400);
    }

    public function testUserUpdateShortPassword()
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'error4@mail.com',
            'password' => Hash::make('password'),
        ]);
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/users/'.$userId, [
            'password' => 'pwd',
            'confirmPassword' => 'pwd',
        ]);

        
        $response->assertJsonStructure([
            'status',
            'error' => ['password']
        ]);
        $response->assertStatus(400);
    }

    /**
     * User delete
     */
    public function testUserDelete() 
    {
        $token = $this->getApiToken();

        $user = User::factory(User::class)->create([
            'email' => 'delete@mail.com',
            'password' => Hash::make('password'),
        ]);
        $userId = $user->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/users/'.$userId);

        
        $response->assertJsonPath('data',null);
        $response->assertStatus(200);
    }
}
