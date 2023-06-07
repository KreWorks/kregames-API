<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Traits\ImageableTrait;

class ImageController extends Controller
{
    use ImageableTrait;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $parent = $this->validateParent($request);

        try {
            $validatedData = $request->validate([
                'file' => 'required|file',
                'type' => 'required|string|max:50',
                'title' => 'string',
            ]);
        } catch(ValidationException $ve) {
            
            return response()->json([
                'status' => 400,
                'error' => $ve->errors()
            ], 400);
        }
        $path = $this->handleImage($request, $parent);
        
        $image = Image::create([
            'type' => $request->type,
            'imageabble_type' => get_class($parent),
            'imageable_id' => $parent->id,
            'path' => $path,
            'title' => $request->title,
        ]);

        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'images',
            ],
            'data' => $image
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    protected function validateParent(Request $request) 
    {
        try {
            $validatedData = $request->validate([
                'file' => 'required|file',
                'type' => 'required|string|max:50'
            ]);
            $parent = ($request->imageable_type)::find($request->imageable_id)->first();

            if (!$parent) {
                return response()->json([
                    'status' => 400,
                    'error' => ["parent" => "Parent entity not found."]
                ], 400);
            }

            return $parent;

        } catch(ValidationException $ve) {
            
            return response()->json([
                'status' => 400,
                'error' => $ve->errors()
            ], 400);
        }
    }

}
