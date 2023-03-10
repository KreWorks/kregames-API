<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::all();
        
        return response()->json([
            'status' => 'success',
            'count' => count($users),
            'data' => $users,
        ]);
    }

    /**
     * Return a form format for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        // Check if laravel has a form helper 
        return respone()->json([
            'name' => ['type' => 'text', 'label' => 'label.name'],
            'email' => ['type' => 'text', 'label' => 'label.email'],
            'username' => ['type' => 'text', 'label' => 'label.username'],
            'password' => ['type' => 'password', 'label' => 'label.password'],
            'password_confirm' => ['type' => 'password', 'label' => 'label.password_confirm']
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
        $error = [];
        if (!$request->has('name')) {
            $error[] = 'A név megadása kötelező.';
        }
        if (!$request->has('email')) {
            $error[] = 'Az email megadása kötelező.';
        }
        if (!$request->has('username')) {
            $error[] = 'A felhasználónév megadása kötelező.';
        }
        if (!$request->has('passowrd') && !$request->has('password_confirm')) {
            $error[] = 'A jelszó megadása kötelező.';
        }
        if ($request->get('passord') != $request->get('password_confirm')) {
            $error[] = 'A két jelszó nem egyezik.';
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);
        
        return response()->json([
            'status' => 'success',
            'count' => count($user),
            'data' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        //
    }
}
