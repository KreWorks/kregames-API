<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Enums\ImageTypeEnum;
use App\Models\User; 
use App\Models\Game;

class GameControllerTest extends TestCase
{
    /**
     * GameController - Game show
     * Success
     */
    public function testGameShow()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create(
            $this->getGameData('create', true)
        );

        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/games/'.$gameId);

        $response->assertJsonPath('data.slug', 'create_game');
        $response->assertJsonPath('data.id', $gameId);
        $this->validateSuccessResponse($response, 'games', 1);
    }

    /**
     * GameController - Game show
     * Error - Game not found
     */
    public function testGameShowNotFound()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create(
            $this->getGameData('create', true)
        );

        $gameId = substr($game->id->ToString(), 0, -5)."sdfbc";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/games/'.$gameId);

        $this->validateErrorResponse($response, ['game'], 404);
    }


    /**
     * GameController - Game create 
     * Success
     */
    public function testGameCreate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', 
            $this->getGameData('create', true, true)
        );

        $response->assertJsonPath('data.slug','create_game');
        $response->assertStatus(200);
    }

    /**
     * GameController - Game create 
     * Error - missing icon
     */
    public function testGameCreateMissingIcon()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', 
            $this->getGameData('create', true, false)
        );

        $this->validateErrorResponse($response, ['icon'], 400);
    }

    /**
     * GameController - Game create
     * Error - duplicate slug
     */
    public function testGameCreateDuplicateSlug()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create(
            $this->getGameData('create', true)
        );


        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', 
            $this->getGameData('create', true, true, true)
        );
        
        $this->validateErrorResponse($response, ['slug'], 400);
    }

    /**
     * GameController - Game create
     * Error - user missing
     */
    public function testGameCreateWithoutUser()
    {
        $token = $this->getApiToken();

        $data = $this->getGameData('create', true, true);
        unset($data['user_id']);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', 
            $data
        );
        
        $this->validateErrorResponse($response, ['user_id'], 400);
    }

    /**
     * GameController - Game create
     * Error - Date missing
     */
    public function testGameCreateMissingDate()
    {
        $token = $this->getApiToken();

        $data = $this->getGameData('create', true, true);
        unset($data['publish_date']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', 
            $data
        );
        
        $this->validateErrorResponse($response, ['publish_date'], 400);
    }

    /**
     * GameController - Game create
     * Error - wrong user
     */
    public function testGameCreateUserNotExists()
    {
        $token = $this->getApiToken();

        $data = $this->getGameData('create', true, true);
        $data['user_id'] = 'aa-bb-ccc';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/games', 
           $data
        );
        
        $this->validateErrorResponse($response, ['user_id'], 400);
    }

    /**
     * GameController - Game list 
     * Success
     */
    public function testGameList()
    {
        $token = $this->getApiToken();

        for($i = 0; $i < 5; $i++) {
            $game = Game::factory(Game::class)->create(
                $this->getGameData('list'.$i, true)
            );
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/games');

        $response->assertJsonStructure([
            'meta' => ['count', 'entityType', 'headers'],
        ]);
        $this->validateSuccessResponse($response, 'games', 5); 
    }

    /**
     * GameControoler - Update Game
     * Success
     */
    public function testGameUpdate()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create(
            $this->getGameData('update', true)
        );

        $game->images()->create([
            'type' => ImageTypeEnum::ICON, 
            'path' => 'icon_'.$game->slug.".jpg",
            'title' => $game->slug. " icon"
        ]);

        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/games/'.$gameId, 
            $this->getGameData('update2', false, true)
        );

        $response->assertJsonPath('data.id',$gameId);
        $response->assertJsonPath('data.slug','update2_game');
        $response->assertJsonPath('data.name','Update2 Game');
        $response->assertJsonPath('data.visible',0);
        $this->validateSuccessResponse($response, 'games', 1);
    }

    /**
     * Game update with errors 
     */
    public function testGameUpdateDuplicateSlug()
    {
        $token = $this->getApiToken();

        $game1 = Game::factory(Game::class)->create(
            $this->getGameData('update', true)
        );
        $game2 = Game::factory(Game::class)->create(
            $this->getGameData('update2', true)
        );
        $gameId = $game2->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/games/'.$gameId, [
            'slug' => 'update_game',
        ]);
        
        $this->validateErrorResponse($response, ['slug'], 400);
    }

    public function testGameUpdateUserNotExists()
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create(
            $this->getGameData('update', true)
        );
        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/games/'.$gameId, [
            'user_id' => '123123-123123-123123',
        ]);

        $this->validateErrorResponse($response, ['user'], 404);
    }

    /**
     * Game delete
     */
    public function testGameDelete() 
    {
        $token = $this->getApiToken();

        $game = Game::factory(Game::class)->create(
            $this->getGameData('update', true)
        );
        $gameId = $game->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/games/'.$gameId);
        
        $response->assertJsonPath('data',null);
        $this->validateSuccessResponse($response, 'games', 0);
    }

    /**
     * Create a game 
     */
    private function getGameData($name, $visible = true, $needFile = false)
    {
        $data = [
            'name' => ucwords($name) . " Game",
            'slug' => $name.'_game',
            'publish_date' => date('Y-m-d H:i:s', time()),
            'user_id' => User::first()->id,
            'visible' => $visible
        ];

        if ($needFile){
            $data['icon'] = UploadedFile::fake()->image('icon.jpg');
        }

        return $data;
    }
}
