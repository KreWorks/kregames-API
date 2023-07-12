<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        $images = Image::orderBy('created_at')->get();
        
        return response()->json($this->handleListResponseSuccess(
            count($images), 
            'images', 
            $images, 
            [
                [ 'entityKey' => 'path', 'type' => 'text', 'value' => 'Elérési út'], 
                [ 'entityKey' => 'type', 'type' => 'text', 'value' => 'Típus'], 
                [ 'entityKey' => 'parentString', 'type' => 'text', 'value' => 'Szülő']
            ], 
            'id', 
            'path'
        ));
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

        if (get_class($parent) == 'Illuminate\Http\JsonResponse') {
            return $parent;
        }

        try {
            $validatedData = $request->validate([
                'file' => 'required|file',
                'type' => 'required|string|max:50',
                'title' => 'string',
            ]);
        } catch(ValidationException $ve) {

            return response()->json(
                $this->handleResponseError(400, $ve->errors()),
                400
            );
        }

        if (!$this->validateImageType($request->get('type')))
        {
            return response()->json(
                $this->handleResponseError(404, ['type' => 'Invalid image type']), 
                404
            );
        }

        $path = $this->handleImage($request, $parent);
        
        $image = Image::create([
            'type' => $request->type,
            'imageable_type' => get_class($parent),
            'imageable_id' => $parent->id,
            'path' => $path,
            'title' => $request->title,
        ]);

        return response()->json(
            $this->handleResponseSuccess(1, 'images', $image)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $image = Image::find($id);
        
        $resp = $this->handleEntityExist($image, 'images', 'image');

        return response()->json($resp, $resp['status']);
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
        $image = Image::find($id);
        
        $resp = $this->handleEntityExist($image, 'images', 'image');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        }
        
        // Parameters
        $validationRules = [];
        if ($request->get('title')) {
            $image->title = $request->title;
            $validationRules['title'] = 'string';
        }
        
        if($request->get('type')) {
            if (!$this->validateImageType($request->get('type')))
            {
                return response()->json(
                    $this->handleResponseError(404, ['type' => 'Invalid image type']), 
                    404
                );
            }
            $image->type = $request->type;
            $validationRules['type'] = 'required|string|max:50';
        }
        
        if ($request->file('file')) {
            $validationRules['file'] = 'required|file';
        }
        
        try {
            $request->validate($validationRules);
        } catch(ValidationException $ve) {
            
            return response()->json(
                $this->handleResponseError(400, $ve->errors()), 
                400
            );
        }
        
        $parent = $image->imageable;
        if ($request->has('imageable_id') && 
            $request->get('imageable_id') != $image->imageable->id) {
            $parent = $this->validateParent($request);

            if (get_class($parent) == 'Illuminate\Http\JsonResponse') {
                return $parent;
            }
            
            $image->imageable_type = get_class($parent);
            $image->imageable_id = $parent->id;
        }

        if ($request->file('file')) {
            $path = $this->handleImage($request, $parent);
            $image->path = $path;
        }

        $image->save();

        return response()->json($this->handleResponseSuccess(1, 'images', $image));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $image = Image::find($id);

        $resp = $this->handleEntityExist($image, 'images', 'image');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        }

        $image->delete();

        return response()->json($this->handleResponseSuccess(0, 'images', null));
    }

    protected function validateParent(Request $request) 
    {
        try {
            $validatedData = $request->validate([
                'imageable_type' => 'required|string',
                'imageable_id' => 'required|uuid'
            ]);
            
            $parent = ($request->imageable_type)::find($request->imageable_id);
    
            if (!$parent) {
                return response()->json(
                    $this->handleResponseError(400, ["parent" => "Parent entity not found."]), 
                    400
                );
            }

            return $parent;

        } catch(ValidationException $ve) {
            return response()->json(
                $this->handleResponseError(400, $ve->errors()),
                400
            );
        }
    }

}
