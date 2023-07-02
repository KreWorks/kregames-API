<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Traits\ImageableTrait;
use App\Enums\ImageTypeEnum;
use App\Models\User;
use App\Models\Image;

class UserController extends Controller
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
        $users = User::orderBy('created_at')->get();
        
        return response()->json($this->handleListResponseSuccess(
            count($users), 
            'users', 
            $users, 
            [
                [ 'entityKey' => 'name', 'type' => 'text', 'value' => 'Név'], 
                [ 'entityKey' => 'username', 'type' => 'text', 'value' => 'Felhasználónév'], 
                [ 'entityKey' => 'email', 'type' => 'text', 'value' => 'Email']
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
                'name' => 'required|string|max:200',
                'email' => 'required|string|email|max:200|unique:users',
                'username' => 'required|string|max:100|unique:users',
                'password' => 'required|string|min:6',
                'confirmPassword' => 'required|string|same:password',
                'avatar' => 'required|file'
            ]);
        } catch(ValidationException $ve) {

            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $path = $this->handleImage($request, $user, 'avatar');
        
        $image = Image::create([
            'type' => ImageTypeEnum::AVATAR,
            'imageabble_type' => get_class($user),
            'imageable_id' => $user->id,
            'path' => $path,
            'title' => $request->username." avatar",
        ]);

        return response()->json($this->handleResponseSuccess(1, 'users', $user));
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

        $response = $this->handleEntityExist($user, 'users', 'user');

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
        $user = User::find($id);

        $resp = $this->handleEntityExist($user, 'users', 'user');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        }

        if ($request->get('password')) {
            try {
                $request->validate([
                    'password' => 'required|string|min:6',
                    'confirmPassword' => 'required|string|same:password'
                ]);
            } catch(ValidationException $ve) {

                return response()->json($this->handleResponseError(400, $ve->errors()), 400);
            }
    
            $user->password = Hash::make($request->password);
        }
        
        $validationRules = [];
        if ($request->get('email')) {
            $emailValidator =  'required|string|email|max:200';
            if ($request->email != $user->email) {
                $emailValidator =  'required|string|email|max:200|unique:users';
            }
            $user->email = $request->email;
            $validationRules['email'] = $emailValidator;
        }
        if($request->get('username')) {
            $usernameValidator = 'required|string|max:100'; 
            if ($request->username != $user->username) {
                $usernameValidator = 'required|string|max:100|unique:users'; 
            }
            $user->username = $request->username;
            $validationRules['username'] = $usernameValidator;

        }
        if ($request->get('name')) {
            $user->name = $request->name;
            $validationRules['name'] = 'required|string|max:200';
        }

        if ($request->has('avatar') && $request->hasFile('avatar')) {
            $validationRules['avatar'] = "required|file";

            $path = $this->handleImage($request, $user, 'avatar');
        
            $image = Image::create([
                'type' => ImageTypeEnum::AVATAR,
                'imageabble_type' => get_class($user),
                'imageable_id' => $user->id,
                'path' => $path,
                'title' => $user->username." avatar",
            ]);
        }

        try {
            $request->validate($validationRules);
        } catch(ValidationException $ve) {

            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $user->save();

        return response()->json($this->handleResponseSuccess(1, 'users', $user));
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

        $resp = $this->handleEntityExist($user, 'users', 'user');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        }

        $user->delete();

        return response()->json($this->handleResponseSuccess(0, 'users', null));
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
            'message' => 'Something went wrong'
        ]);
    }
}
