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
     * Test user register feature in AuthController
     *
     * @return void
     */
    public function testRegister()
    {
        $response = $this->post('/api/register', [
            'name' => 'Test Name',
            'username' => 'TestUsername',
            'email' => 'test@mail.hu',
            'password' => Hash::make('password')
        ]);

        $response->assertStatus(200);

    }
    
    /**
     * Test user login feature in AuthController
     *
     * @return void
     */
    public function testLogin()
    { 
        $user = User::factory(User::class)->create([
            'email' => 'test@mail.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/api/login', [
            'email' => 'test@mail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->uuid, Auth::user()->uuid);
    }

    /**
     * Test logout feature in AuthController
     *
     * @return void
     */
    public function testLogout()
    {
        $token = $this->getApiToken(); 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/logout');

        $response->assertStatus(200);
    }

    public function testRefreshToken()
    {
        $token = $this->getApiToken();

        /*
        use PHPOpenSourceSaver\JWTAuth\JWTAuth;

$token = // Get the JWT token from the request headers or wherever you store it
$jwtAuth = new JWTAuth('your_secret_key');

$payload = $jwtAuth->decodeToken($token);
$expirationTime = $payload['exp'];

$currentTimestamp = time();

if ($expirationTime <= $currentTimestamp) {
    // Token has expired, you may need to refresh it
    // Perform token refresh logic here
} else {
    // Token is still valid
} */

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/refresh');
        
        $response->assertStatus(200);
    }
}
