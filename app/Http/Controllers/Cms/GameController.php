<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Traits\ImageableTrait;
use App\Enums\ImageTypeEnum;
use App\Models\User;
use App\Models\Game;
use App\Models\Image;

class GameController extends Controller
{
    use ImageableTrait;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['unique']]);
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $games = Game::orderBy('created_at')->get();
        
        return response()->json($this->handleListResponseSuccess(
            count($games), 
            'games', 
            $games, 
            [
                [ 'entityKey' => 'name', 'type' => 'text', 'value' => 'Cím'], 
                [ 'entityKey' => 'slug', 'type' => 'text', 'value' => 'Slug'], 
                [ 'entityKey' => 'publish_date', 'type' => 'date',  'value' => 'Megjelenés dátuma'],
                [ 'entityKey' => 'visible', 'type' => 'visible', 'value' => 'Láthatóság']
            ], 
            'id', 
            'name'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:255',
                'slug' => 'required|unique:games|max:255',
                'publish_date' => 'required|date',
                'user_id' => 'required|exists:users,id',
                'visible' => 'required|boolean',
                'icon' => 'required|file'
            ]);
        } catch(ValidationException $ve) {

            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $user = User::find($request->input('user_id')); 
        $resp = $this->handleEntityExist($user, 'users', 'user');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        }

        $game = $user->games()->create([
            'name' => $request->name, 
            'slug' => $request->slug, 
            'publish_date' => $request->publish_date,
            'visible' => $request->visible ? 1 : 0
        ]);
        
        
        $path = $this->handleImage($request, $game, 'icon');
        
        $image = Image::create([
            'type' => ImageTypeEnum::ICON,
            'imageabble_type' => get_class($game),
            'imageable_id' => $game->id,
            'path' => $path,
            'title' => $game->slug." icon",
        ]);
        
        $user->games()->save($game);

        return response()->json($this->handleResponseSuccess(1, 'games', $game));
    }

    /**
     * Display the specified resource.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $game = Game::find($id);

        $response = $this->handleEntityExist($game, 'games', 'game');

        return response()->json($response, $response['status']);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $game = Game::find($id);
        $resp = $this->handleEntityExist($game, 'games', 'game');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        } 
        
        $validateRules = []; 
        if ($request->get('name')) {
            $game->name = $request->name;
            $validateRules['name'] = 'required|max:255';
        }
        if ($request->get('slug')) {
            $validateRules['slug'] = 'required|max:255'; 
            if ($game->slug != $request->slug) {
                $validateRules['slug'] = 'required|unique:games|max:255';
                $game->slug = $request->slug; 
            }
        }
        if ($request->get('publish_date')) {
            $game->publish_date = $request->publish_date; 
            $validateRules['publish_date'] = 'required|date'; 
        }

        if ($request->get('visible') !== null) {
            $game->visible = $request->visible ? 1 : 0;
        }

        if ($request->has('icon') && $request->hasFile('icon')) {
            $validationRules['icon'] = "required|file";

            $path = $this->handleImage($request, $game, 'icon');
        
            $image = Image::create([
                'type' => ImageTypeEnum::ICON,
                'imageabble_type' => get_class($game),
                'imageable_id' => $game->id,
                'path' => $path,
                'title' => $game->slug." icon",
            ]);
        }

        try {
            $request->validate($validateRules);
        } catch(ValidationException $ve) {
            
            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        if ($request->input('user_id') && $request->user_id != $game->user->id) {
            $user = User::find($request->input('user_id'));
            
            $resp = $this->handleEntityExist($user, 'users', 'user');
            if ($resp['status'] != 200 ) {
                
                return response()->json($resp, $resp['status']);
            } else {
                $game->user($user);
            }
        }

        $game->save();

        return response()->json($this->handleResponseSuccess(1, 'games', $game));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $game = Game::find($id);
        $resp = $this->handleEntityExist($game, 'games', 'game');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        } 

        $game->delete();

        return response()->json($this->handleResponseSuccess(0, 'games', null));
    }
}
