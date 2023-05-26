<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; 
use App\Models\Game;

class GameControllerTest extends TestCase
{
    /**
     * Create Game 
     *
     * @return void
     */
    public function testGameCreate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', [
            'name' => 'Game Create',
            'slug' => 'game_create',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => User::first()->id,
            'visible' => true
        ]);

        $response->assertJsonPath('data.slug','game_create');
        $response->assertStatus(200);
    }

    /**
     * Create Game with errors 
     */
    public function testGameCreateDuplicateSlug()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', [
            'name' => 'Game Create',
            'slug' => 'game_create',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => User::first()->id,
            'visible' => true
        ]);
        
        $response->assertJsonStructure([
            'status',
            'error' => ['slug']
        ]);
        $response->assertStatus(400);
    }

    public function testGameCreateWithoutUser()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', [
            'name' => 'Game Create',
            'slug' => 'game_create_2',
            'publish_date' => '2023-05-25 10:00:00',
            'visible' => true
        ]);
        
        $response->assertJsonStructure([
            'status',
            'error' => ['user_id']
        ]);
        $response->assertStatus(400);
    }

    public function testGameCreateMissingDate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', [
            'name' => 'Game Create',
            'slug' => 'game_create',
            'user_id' => User::first()->id,
            'visible' => true
        ]);
        
        $response->assertJsonStructure([
            'status',
            'error' => ['publish_date']
        ]);
        $response->assertStatus(400);
    }

    public function testGameCreateUserNotExists()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', [
            'name' => 'Game Create',
            'slug' => 'game_create',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => 'asdasd-asdasdas-dasdasd',
            'visible' => true
        ]);
        
        $response->assertJsonStructure([
            'status',
            'error' => ['user_id']
        ]);
        
        $response->assertStatus(400);
    }

    /**
     * Update Game
     */
    public function testGameUpdate()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create([
            'name' => "Update game",
            'slug' => 'update_game',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => User::all()->first()->id,
            'visible' => true
        ]);
        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/games/'.$gameId, [
            'name' => 'Update Game 2',
            'slug' => 'update_game_updated',
            'publish_date' => '2023-05-15 10:00:00',
            'user_id' => User::all()->random()->first()->id,
            'visible' => false
        ]);

        $response->assertJsonPath('data.id',$gameId);
        $response->assertJsonPath('data.slug','update_game_updated');
        $response->assertJsonPath('data.name','Update Game 2');
        $response->assertJsonPath('data.visible',0);
        $response->assertStatus(200);
    }

    /**
     * Game update with errors 
     */
    public function testGameUpdateDuplicateSlug()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create([
            'name' => "Update game 2",
            'slug' => 'update_game_2',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => User::first()->id,
            'visible' => true
        ]);
        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/games/'.$gameId, [
            'slug' => 'update_game_updated',
        ]);

        $response->assertJsonStructure([
            'status',
            'error' => ['slug']
        ]);
        $response->assertStatus(400);
    }

    public function testGameUpdateUserNotExists()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create([
            'name' => "Update game 3",
            'slug' => 'update_game_3',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => User::first()->id,
            'visible' => false
        ]);
        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/games/'.$gameId, [
            'user_id' => '123123-123123-123123',
        ]);

        $response->assertJsonStructure([
            'status',
            'error' => ['user_id']
        ]);
        $response->assertStatus(400);
    }


    /**
     * Game delete
     */
    public function testGameDelete() 
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create([
            'name' => "Delete game",
            'slug' => 'delete_game',
            'publish_date' => '2023-05-25 10:00:00',
            'user_id' => User::first()->id,
            'visible' => false
        ]);
        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/games/'.$gameId);

        
        $response->assertJsonPath('data',null);
        $response->assertStatus(200);
    }
}
