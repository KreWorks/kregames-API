<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\LinkType;

class LinkController extends Controller
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
        $links = Link::orderBy('created_at')->get();
        
        return response()->json($this->handleListResponseSuccess(
            count($links), 
            'links', 
            $links, 
            [
                [ 'entityKey' => 'linktype', 'type' => 'linktype', 'value' => 'Típus'], 
                [ 'entityKey' => 'linkable', 'type' => 'linkable', 'value' => 'Tulaj'], 
                [ 'entityKey' => 'link', 'type' => 'text',  'value' => 'Link'],
                [ 'entityKey' => 'display_text', 'type' => 'text',  'value' => 'Megjelenítő szöveg'],
                [ 'entityKey' => 'visible', 'type' => 'boolean',  'value' => 'Láthatóság'],
            ], 
            'id', 
            'display_text'
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
        $parent = $this->validateParent($request);

        if (get_class($parent) == 'Illuminate\Http\JsonResponse') {
            return $parent;
        }

        try {
            $validatedData = $request->validate([
                'linktype_id' => 'required|exists:linktypes,id',
                'link' => 'required|url',
                'visible' => 'required'
            ]);
        } catch(ValidationException $ve) {

            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $link = Link::create([
            'linktype_id' => $request->linktype_id,
            'link' => $request->link, 
            'display_text' => $request->display_text, 
            'visible' => $request->visible,
            'linkable_type' => get_class($parent),
            'linkable_id' => $parent->id,
        ]);

        return response()->json($this->handleResponseSuccess(1, 'links', $link));
    }

    /**
     * Display the specified resource.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $link = Link::find($id);

        $response = $this->handleEntityExist($link, 'links', 'link');

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
        $link = Link::find($id);
        $resp = $this->handleEntityExist($link, 'links', 'link');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        }

        $validateRules = []; 
        if ($this->isAttributeChanged($request, 'link', $link)) {
            $validateRules['link'] = 'required|url';
            $link->link = $request->link;
        }
        if ($this->isAttributeChanged($request, 'display_text', $link)) {
            $validateRules['display_text'] = 'required'; 
            $link->display_text = $request->display_text; 
        }
        if ($this->isAttributeChanged($request, 'visible', $link)) {
            $validateRules['visible'] = 'required'; 
            $link->visible = $request->visible; 
        }

        if ($this->isAttributeChanged($request, 'linktype_id', $link)) {
            $linktype = LinkType::find($request->input('linktype_id'));
            
            $resp = $this->handleEntityExist($linktype, 'linktypes', 'linktype');
            if ($resp['status'] != 200 ) {
                
                return response()->json($resp, $resp['status']);
            } else {
                $link->linktype($linktype);
            }
        }

        $parent = $link->linkable;
        if ($request->has('linkable_id') &&
            $request->get('linkable_id') != $link->linkable->id) {
            $parent = $this->validateParent($request);

            if (get_class($parent) == 'Illuminate\Http\JsonResponse') {
                return $parent;
            }
            
            $link->linkable_type = get_class($parent);
            $link->linkable_id = $parent->id;
        }

        try {
            $request->validate($validateRules);
        } catch(ValidationException $ve) {
            
            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $link->save();

        return response()->json($this->handleResponseSuccess(1, 'links', $link));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $link = Link::find($id);
        $resp = $this->handleEntityExist($link, 'links', 'link');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        } 

        $link->delete();

        return response()->json($this->handleResponseSuccess(0, 'links', null));
    }

    protected function validateParent(Request $request) 
    {
        try {
            $validatedData = $request->validate([
                'linkable_type' => 'required|string',
                'linkable_id' => 'required|uuid'
            ]);
            
            $parent = ($request->linkable_type)::find($request->linkable_id);
    
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
