<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Get an api JWT token
     *
     * @return string
     */
    protected function getApiToken()
    {
        $user = User::where('email','test@mail.com') -> first();
        if (!$user) {
            $user = User::factory(User::class)->create([
                'email' => 'test@mail.com',
                'username' => 'testuser',
                'password' => Hash::make('password'),
            ]);
        }

        $loginResponse = $this->post('/api/login', [
            'email' => 'test@mail.com',
            'password' => 'password',
        ]);

        return $loginResponse->getData()->authorisation->token;
    }

    protected function validateErrorResponse($response, $errorLabels, $status = 400) 
    {
        $response->assertStatus($status);
        if ($errorLabels) {
            $response->assertJsonStructure([
                'status',
                'error' => $errorLabels
            ]);
        } else {
            $response->assertJsonStructure([
                'status',
                'error'
            ]);
        }
    }

    protected function validateSuccessResponse($response, $entityType, $entityCount) 
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'meta' => ['count', 'entityType'],
            'data'
        ]);
        $response->assertJsonPath('meta.entityType', $entityType);
        $response->assertJsonPath('meta.count', $entityCount);
    }
}
