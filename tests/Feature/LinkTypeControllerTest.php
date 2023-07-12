<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\LinkType;

class LinkTypeControllerTest extends TestCase
{

    // ########## SHOW ##########
    /**
     * LinkTypeController - Linktype show
     * Success
     */
    public function testLinktypeShow()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('show')
        );

        $linktypeID = $linktype->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/linktypes/'.$linktypeID);

        $response->assertJsonPath('data.name', 'Show LinkType');
        $response->assertJsonPath('data.id', $linktypeID);
        $this->validateSuccessResponse($response, 'linktypes', 1);
    }

    /**
     * LinktypeController - LinkType show
     * Error - LinkType not found
     */
    public function testLinktypeShowNotFound()
    {
        $token = $this->getApiToken();

        $linktype = Linktype::factory(Linktype::class)->create(
            $this->getData('show')
        );


        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/linktypes/'.'aa-bb-cc-dd');

        $this->validateErrorResponse($response, ['linktype'], 404);
    }

    // ########## CREATE ##########
    /**
     * LinkTypeController - Create 
     * Success
     */
    public function testLinktypeCreate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $this->getData('create')
        );

        $response->assertJsonPath('data.name', 'Create LinkType');
        $this->validateSuccessResponse($response, 'linktypes', 1);
    }

    /**
     * LinkTypeController - Create 
     * Error - missing name
     */
    public function testLinktypeCreateMissingName()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        unset($data['name']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['name'], 400);
    }

    /**
     * LinkTypeController - Create 
     * Error - Too long name 
     */
    public function testLinktypeCreateTooLongName()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        $data['name'] = "almamentesalmamentesalmamentesalmamentes
        almamentesalmamentesalmamentesalmamentesalmamentesalmamentesalmamentes";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['name'], 400);
    }

    /**
     * LinkTypeController - Create 
     * Error - Too short name
     */
    public function testLinktypeCreateTooShortName()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        $data['name'] = "alma";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['name'], 400);
    }

    /**
     * LinkTypeController - Create 
     * Error - Missing font awesome
     */
    public function testLinktypeCreateMissingFontawesome()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        unset($data['font_awesome']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['font_awesome'], 400);
    }

    /**
     * LinkTypeController - Create 
     * Error - Wrong font awesome format
     */
    public function testLinktypeCreateWrongFormatFontawesome()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        $data['font_awesome'] = "fakabat";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['font_awesome'], 400);
    }

    /**
     * LinkTypeController - Create 
     * Error - Missing color
     */
    public function testLinktypeCreateMissingColor()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        unset($data['color']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['color'], 400);
    }

    /**
     * LinkTypeController - Create 
     * Error - wrong color format
     */
    public function testLinktypeCreateWrongFormatColor()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create'); 
        $data['color'] = "#aw";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/linktypes/', 
            $data
        );

        $this->validateErrorResponse($response, ['color'], 400);
    }

    // ########## LIST ##########
    /**
     * LinkTypeController - Create 
     * Error - 
     */
    public function testLinktypeList()
    {
        $token = $this->getApiToken();

        for($i = 0; $i < 5; $i++) {
            $linktype = LinkType::factory(LinkType::class)->create(
                $this->getData('List '.$i)
            );
        }

        $linktypeID = $linktype->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/linktypes/');

        $this->validateSuccessResponse($response, 'linktypes', 5);
    }

    // ########## UPDATE ##########
    /**
     * LinkTypeController - Update 
     * Success
     */
    public function testLinktypeUpdate()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('Update')
        );

        $linktypeID = $linktype->id->ToString();

        $data = $this->getData('update2');
        $data['font_awesome'] = "fa fa-korte";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/linktypes/'.$linktypeID,
            $data
        );

        $this->validateSuccessResponse($response, 'linktypes', 1);
    }
    
    /**
     * LinkTypeController - Update 
     * Error - Missing entry
     */
    public function testLinktypeUpdateMissingEntry()
    {
        $token = $this->getApiToken();

        $data = $this->getData('update2');
        $data['font_awesome'] = "fa fa-korte";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/linktypes/'.'aa-bb-acc-dd',
            $data
        );

        $this->validateErrorResponse($response, ['linktype'], 404);
    }

    /**
     * LinkTypeController - Update 
     * Error - Too long name
     */
    public function testLinktypeUpdateTooLongName()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('Update')
        );

        $linktypeID = $linktype->id->ToString();

        $data = $this->getData('update2');
        $data['name'] = 
        "almakeveroalmakeveroalmakeveroalmakeveroalmakeveroalmakeveroalmakeveroalmakeveroalmakeveroalmakeveroalmakevero";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/linktypes/'.$linktypeID,
            $data
        );

        $this->validateErrorResponse($response, ['name'], 400);
    }
    
    /**
     * LinkTypeController - Update 
     * Error - Too short name 
     */
    public function testLinktypeUpdateTooShortName()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('Update')
        );

        $linktypeID = $linktype->id->ToString();

        $data = $this->getData('update2');
        $data['name'] = "alma";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/linktypes/'.$linktypeID,
            $data
        );

        $this->validateErrorResponse($response, ['name'], 400);
    }
    
    /**
     * LinkTypeController - Update 
     * Error - wrong font awesome format
     */
    public function testLinktypeUpdateWrongFormatFontawesome()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('Update')
        );

        $linktypeID = $linktype->id->ToString();

        $data = $this->getData('update2');
        $data['font_awesome'] = "fa falap";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/linktypes/'.$linktypeID,
            $data
        );

        $this->validateErrorResponse($response, ['font_awesome'], 400);
    }
    
    /**
     * LinkTypeController - Update 
     * Error - wrong color format
     */
    public function testLinktypeUpdateWrongFormatColor()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('Update')
        );

        $linktypeID = $linktype->id->ToString();

        $data = $this->getData('update2');
        $data['color'] = "#ds";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/linktypes/'.$linktypeID,
            $data
        );

        $this->validateErrorResponse($response, ['color'], 400);
    }

    // ########## DELETE ##########
    /**
     * LinkTypeController - Delete 
     * Success 
     */
    public function testLinktypeDelete()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('delete')
        );

        $linktypeID = $linktype->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/linktypes/'.$linktypeID);

        $this->validateSuccessResponse($response, 'linktypes', 0);
    }
    
    /**
     * LinkTypeController - Delete 
     * Error - Missing entity
     */
    public function testLinktypeDeleteMissingEntry()
    {
        $token = $this->getApiToken();

        $linktype = LinkType::factory(LinkType::class)->create(
            $this->getData('delete')
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->delete('/api/cms/linktypes/'.'aaa-bbb-cc');

        $this->validateErrorResponse($response, ['linktype'], 404);
    }

    /**
     * Create a linktype data 
     */
    private function getData($name)
    {
        return [
            'name' => ucwords($name) . " LinkType",
            'font_awesome' => 'fa fa-'.$name,
            'color' => '#'.'aabbcc',
        ];
    }
}
