<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
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
        $users = User::orderBy('created_at')->get();
        
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => count($users),
                'entityType' => 'users',
                'headers' => [
                    [ 'entityKey' => 'name', 'type' => 'text', 'value' => 'Név'], 
                    [ 'entityKey' => 'username', 'type' => 'text', 'value' => 'Felhasználónév'], 
                    [ 'entityKey' => 'email', 'type' => 'text', 'value' => 'Email']
                ],
                'key' => 'id', 
                'value' => 'name'
            ],
            'data' => $users,
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
            'name' => 'required|string|max:200',
            'email' => 'required|string|email|max:200|unique:users',
            'username' => 'required|string|max:100|unique:users',
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|same:password'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'users',
            ],
            'data' => $user
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
        $user = User::find($id);
        
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'users',
            ],
            'data' => $user,
        ]);
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
        $user = User::find($id);

        if ($request->get('password')) {
            $request->validate([
                'password' => 'required|string|min:6',
                'confirmPassword' => 'required|string|same:password'
            ]);

            $user->password = Hash::make($request->password);
        }
        if ($request->get('email')) {
            $emailValidator =  'required|string|email|max:200';
            if ($request->email != $user->email) {
                $emailValidator =  'required|string|email|max:200|unique:users';
            }
            $usernameValidator = 'required|string|max:100'; 
            if ($request->username != $user->username) {
                $usernameValidator = 'required|string|max:100|unique:users'; 
            }
            $request->validate([
                'name' => 'required|string|max:200',
                'email' => $emailValidator,
                'username' => $usernameValidator,
            ]);

            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'User was deleted.',
            'meta' => [],
            'data' => null
        ]);
    }

    public function unique(string $key, string $value = '') 
    {
        $user = User::firstWhere($key, $value);

        if ($user == null && $value !== '') {
            return response()->json([
                'status' => 200,
                'is_unique' => true
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'is_unique' => false
            ]);
        }

        return response()->json([
            'status' => 200,
            'is_unique' => false,
            'messqge' => 'Something went wrong'
        ]);
    }
}
