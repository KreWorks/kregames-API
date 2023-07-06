<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\LinkType;

class LinkTypeController extends Controller
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
        $linktypes = LinkType::orderBy('created_at')->get();
        
        return response()->json($this->handleListResponseSuccess(
            count($linktypes), 
            'linktypes', 
            $linktypes, 
            [
                [ 'entityKey' => 'name', 'type' => 'text', 'value' => 'Név'], 
                [ 'entityKey' => 'font_awesome', 'type' => 'text', 'value' => 'FontAwesome'], 
                [ 'entityKey' => 'color', 'type' => 'color',  'value' => 'Szín'],
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
                'name' => 'required|min:5|max:100',
                'font_awesome' => [
                    'required', 
                    "regex:/.*[\ \"\']+(fa-[a-zA-z0-9]*).*/",
                    'max:100'
                ],
                'color' => [
                    "required",
                    "regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/i"
                ],
            ]);
        } catch(ValidationException $ve) {

            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $linktype = LinkType::create([
            'name' => $request->name, 
            'font_awesome' => $request->font_awesome, 
            'color' => $request->color,
        ]);

        return response()->json($this->handleResponseSuccess(1, 'linktypes', $linktype));
    }

    /**
     * Display the specified resource.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $linktype = LinkType::find($id);

        $response = $this->handleEntityExist($linktype, 'linktypes', 'linktype');

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
        $linktype = LinkType::find($id);
        $resp = $this->handleEntityExist($linktype, 'linktypes', 'linktype');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        } 
        
        $validateRules = []; 
        if ($this->isAttributeChanged($request, 'name', $linktype)) {
            $validateRules['name'] = 'required|min:5|max:100';
            $linktype->name = $request->name;
        }
        if ($this->isAttributeChanged($request, 'font_awesome', $linktype)) {
            $validateRules['font_awesome'] = [
                'required', 
                "regex:/.*[\ \"\']+(fa-[a-zA-z0-9]*).*/",
                'max:100'
            ]; 
            $linktype->font_awesome = $request->font_awesome; 
        }
        if ($this->isAttributeChanged($request, 'color', $linktype)) {
            $validateRules['color'] = [
                'required',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/i'
            ]; 
            $linktype->color = $request->color; 
        }

        try {
            $request->validate($validateRules);
        } catch(ValidationException $ve) {
            
            return response()->json($this->handleResponseError(400, $ve->errors()), 400);
        }

        $linktype->save();

        return response()->json($this->handleResponseSuccess(1, 'linktypes', $linktype));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $linktype = Linktype::find($id);
        $resp = $this->handleEntityExist($linktype, 'linktypes', 'linktype');
        if ($resp['status'] != 200 ) {
            
            return response()->json($resp, $resp['status']);
        } 

        $linktype->delete();

        return response()->json($this->handleResponseSuccess(0, 'linktypes', null));
    }
}
