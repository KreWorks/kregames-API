<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AuthController - User register
     * Success
     */
    public function testRegister()
    {
        $response = $this->post('/api/register', [
            'name' => 'Auth Test',
            'username' => 'authtestname',
            'email' => 'auth_test@mail.hu',
            'password' => 'password'
        ]);

        $this->validateSuccessResponse($response, 'users', 1);
    }

    /**
     * AuthController - User register 
     * Error - unique 
     */
    public function testRegisterUniqueUsername()
    {
        $user = User::factory(User::class)->create([
            'name' => 'Auth Test',
            'username' => 'authtestname',
            'email' => 'auth_test@mail.hu',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/api/register', [
            'name' => 'Auth Test 2',
            'username' => 'authtestname',
            'email' => 'auth_test_2@mail.hu',
            'password' => 'password'
        ]);
        
        $this->validateErrorResponse($response, ['username']); 
    }

    public function testRegisterUniqueEmail()
    {
        $user = User::factory(User::class)->create([
            'name' => 'Auth Test',
            'username' => 'authtestname',
            'email' => 'auth_test@mail.hu',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/api/register', [
            'name' => 'Auth Test 3',
            'username' => 'authtestname_3',
            'email' => 'auth_test@mail.hu',
            'password' => ('password')
        ]);

        $this->validateErrorResponse($response, ['email']); 
    }

    /**
     * AuthController - User register 
     * Error - missing param
     */
    public function testRegisterMissingParam()
    {
        $response = $this->post('/api/register', [
            'name' => 'Auth Test 4',
            'username' => 'authtestname_4',
            'password' => ('password')
        ]);

        $this->validateErrorResponse($response, ['email']); 
    }

    /**
     * AuthController - User register 
     * Error - too short password
     */
    public function testRegisterTooShortPassword()
    {
        $response = $this->post('/api/register', [
            'name' => 'Auth Test 4',
            'username' => 'authtestname_4',
            'email' => 'auth_test_5@mail.hu',
            'password' => ('pwd')
        ]);

        $this->validateErrorResponse($response, ['password']); 
    }


    /**
     * AuthController - User login 
     * Success
     */
    public function testLogin()
    { 
        $user = User::factory(User::class)->create([
            'name' => 'Auth Login ',
            'username' => 'authloginname',
            'email' => 'auth_login@mail.hu',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/api/login', [
            'email' => 'auth_login@mail.hu',
            'password' => 'password',
        ]);

        $response->assertJsonStructure([
            'authorisation'
        ]);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->uuid, Auth::user()->uuid);
        $this->validateSuccessResponse($response, 'users', 1);
    }

    /**
     * AuthController - User Login
     * Error - fail login
     */
    public function testLoginFail()
    {
        $user = User::factory(User::class)->create([
            'name' => 'Auth Login 2',
            'username' => 'authloginname_2',
            'email' => 'auth_login_2@mail.hu',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/api/login', [
            'email' => 'auth_login_2@mail.hu',
            'password' => 'passwor',
        ]);

        $this->validateErrorResponse($response, null, 401);
    }

    /**
     * AuthController - UserLogin
     * Error - Missing param
     */
    public function testLoginMissingParam()
    {
        $user = User::factory(User::class)->create([
            'name' => 'Auth Login 3',
            'username' => 'authloginname_3',
            'email' => 'auth_login_3@mail.hu',
            'password' => Hash::make('password')
        ]);

        $response = $this->post('/api/login', [
            'email' => 'auth_login_3@mail.hu'
        ]);
        $this->validateErrorResponse($response, ['password'], 400);

        $response2 = $this->post('/api/login', [
            'password' => 'password'
        ]);
        $this->validateErrorResponse($response2, ['email'], 400);
    }

    /**
     * AuthController - Userlogout
     * Success
     */
    public function testLogout()
    {
        $token = $this->getApiToken(); 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/logout');

        $this->validateSuccessResponse($response, 'users', 0);
    }

    /**
     * AuthController - User logout
     * Error - invalid token
     */
    public function testLogoutInvalidToken()
    {
        $token = $this->getApiToken(); 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '."aaa.bb.cc",
        ])->post('/api/logout');

        $this->validateErrorResponse($response, ['token'], 401);
    }

    public function testRefreshToken()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/refresh');
        
        $response->assertJsonStructure([
            'authorisation'
        ]);
        $this->validateSuccessResponse($response, 'users', 1);
    }
}
