<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    /**
     * Get an api JWT token
     *
     * @return string
     */
    protected function getApiToken()
    {
        $user = User::where('email','admin@admin.hu') -> first();
        if (!$user) {
            $user = User::factory(User::class)->create([
                'email' => 'admin@admin.hu',
                'username' => 'admin',
                'password' => Hash::make('4dm1n'),
            ]);
        }

        $loginResponse = $this->post('/api/login', [
            'email' => 'admin@admin.hu',
            'password' => '4dm1n',
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
