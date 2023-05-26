<?php

namespace App\Http\Controllers\Cms;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class GameController extends Controller
{

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
        
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => count($games),
                'entityType' => 'games',
                'headers' => [
                    [ 'entityKey' => 'name', 'type' => 'text', 'value' => 'Cím'], 
                    [ 'entityKey' => 'slug', 'type' => 'text', 'value' => 'Slug'], 
                    [ 'entityKey' => 'publish_date', 'type' => 'date',  'value' => 'Megjelenés dátuma'],
                    [ 'entityKey' => 'visible', 'type' => 'visible', 'value' => 'Láthatóság']
                ]
            ],
            'data' => $games,
        ]);
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
                /*'jam_id' => 'required|exists:jams,id',*/
                'visible' => 'required|boolean',
            ]);
        } catch(ValidationException $ve) {

            return response()->json([
                'status' => 400,
                'error' => $ve->errors()
            ], 400);
        }

        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
        } else {
            return response()->json([
                'status' => 400,
                'error' => ['user_id' => "User is missing."]
            ], 400);
        }

        $game = $user->games()->create([
            'name' => $request->name, 
            'slug' => $request->slug, 
            'publish_date' => $request->publish_date,
            'visible' => $request->visible ? 1 : 0
        ]);
        
        $user->games()->save($game);

        return response()->json([
            'status' => 200,
            'message' => 'Sikeres mentés',
            'meta' => [
                'count' => 1,
                'entityType' => 'games',
            ],
            'data' => $game
        ]);

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
        
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'games',
            ],
            'data' => $games,
        ]);
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

        try {
            $request->validate($validateRules);
        } catch(ValidationException $ve) {
            return response()->json([
                'status' => 400,
                'error' => $ve->errors()
            ], 400);
        }

        if ($request->input('user_id') && $request->user_id != $game->user->id) {
            $user = User::find($request->input('user_id'));
            if ($user) {
                $game->user($user);
                //$user->games()->save($game);
            } else {
                return response()->json([
                    'status' => 400,
                    'error' => ['user_id' => "User is missing."]
                ], 400);
            }
        }


        $game->save();

        return response()->json([
            'status' => 200,
            'message' => 'Sikeres frissítés',
            'data' => $game
        ]);
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

        $game->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Game was deleted.',
            'meta' => [],
            'data' => null
        ]);
    }
}
