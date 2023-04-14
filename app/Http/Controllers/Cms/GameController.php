<?php

namespace App\Http\Controllers\Cms;

use App\Models\Game;
use Illuminate\Http\Request;
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
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:games|max:255',
            'published_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            /*'jam_id' => 'required|exists:jams,id',*/
            'visible' => 'required|boolean',
        ]);


        $game = Game::create([
            'name' => $request->name, 
            'slug' => $request->slug, 
            'published_date' => $request->published_date,
            'visible' => $request->visible
        ]);
        
        $game = Game::create($validatedData);

        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
            $user->games()->save($game);
        }

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
        
        $validate = [
            'name' => 'required|max:255',
            'published_date' => 'required|date',
            'visible' => 'required|boolean',
        ];
        if ($request->slug != $game->slug) {
            $validate['slug'] = 'required|unique:games|max:255';
        }

        $validatedData = $request->validate($validate);

        $game->name = $request->name; 
        $game->slug = $request->slug;
        $game->date = $request->date; 
        $game->user_id = $request->user_id;
        $game->visible = $request->visible;

        $game->save();

        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
            $user->games()->save($game);
        }

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
