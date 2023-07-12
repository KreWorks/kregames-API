<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Link;
use App\Models\LinkType;
use App\Models\User; 

class LinkControllerTest extends TestCase
{
    /**
     * LinkController - Link show
     * Success
     */
    public function testLinkShow()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('show')
        );

        $linkID = $link->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/links/'.$linkID);

        $response->assertJsonPath('data.display_text', 'show');
        $response->assertJsonPath('data.link', 'https://www.show.hu');
        $response->assertJsonPath('data.id', $linkID);
        $this->validateSuccessResponse($response, 'links', 1);
    }

    /**
     * LinkController - Link show
     * Error - missing link
     */
    public function testLinkShowErrorMissingEntry()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('show')
        );

        $linkID = $link->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/links/'.'aaa-bbb-ccc-ddd');

        $this->validateErrorResponse($response, ['link'], 404);
    }

    /**
     * LinkController - create 
     * Success
     */
    public function testLinkCreate()
    {
        $token = $this->getApiToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $this->getData('create')
        );

        $this->validateSuccessResponse($response, 'links', 1);
        $response->assertJsonPath('data.link', 'https://www.create.hu');
        $response->assertJsonPath('data.display_text', 'create');
    }

    /**
     * LinkController - Create
     * Error - missing linktype
     */
    public function testLinkCreateErrorMissingLinktype()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        unset($data['linktype_id']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['linktype_id'], 400);
    }

    /**
     * LinkController - create
     * Error - wrong linktype id
     */
    public function testLinkCreateErrorWrongLinktype()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        $data['linktype_id'] = 'aaa-bbb-cc-dd';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['linktype_id'], 400);
    }

    /**
     * LinkController - Create
     * Error - missing linkable
     */
    public function testLinkCreateErrorMissingLinkable()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        unset($data['linkable_id']);
        unset($data['linkable_type']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['linkable_id'], 400);
    }

    /**
     * LinkController - Create
     * Error - wrong linkable
     */
    public function testLinkCreateErrorWrongLinkable()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        $data['linkable_id'] = "aaa-bbb-ccc-ddd";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['linkable_id'], 400);
    }

    /**
     * LinkController - create
     * Error - missing link
     */
    public function testLinkCreateErrorMissingLink()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        unset($data['link']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['link'], 400);
    }

    /**
     * LinkController - create 
     * error - wrong link format 
     */
    public function testLinkCreateErrorWrongLinkFormat()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        $data['link'] = "alma.hu";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['link'], 400);
    }

    /**
     * LinkController - create 
     * Error - missing visible
     */
    public function testLinkCreateErrorMissingVisible()
    {
        $token = $this->getApiToken();

        $data = $this->getData('create');
        unset($data['visible']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->post('/api/cms/links/', 
            $data
        );

        $this->validateErrorResponse($response, ['visible'], 400);
    }

    /**
     * LinkController - list
     * Success 
     */
    public function testLinkList()
    {
        $token = $this->getApiToken();

        for($i = 0; $i < 5; $i++) {
            $link = Link::factory(Link::class)->create(
                $this->getData('list'.$i, true)
            );
        }
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/links/');

        $this->validateSuccessResponse($response, 'links', 5);
        $response->assertJsonStructure([
            'meta' => ['count', 'entityType', 'headers'],
        ]);
    }

    /**
     * LinkController - update 
     * Success
     */
    public function testLinkUpdate()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('update', true)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/links/'.$link->id->ToString(), [
            'linkable_type' => get_class(User::all()->first()),
            'linkable_id' => User::all()->first()->id,
            'link' => "https://www.update2.hu",
            'display_text' => "update2",
            'visible' => 0,
        ]);

        $this->validateSuccessResponse($response, 'links', 1);
        $response->assertJsonPath('data.link', 'https://www.update2.hu');
        $response->assertJsonPath('data.display_text', 'update2');
        $response->assertJsonPath('data.visible', 0);
    }

    /**
     * LinkController - update 
     * error - missing entry 
     */
    public function testLinkUpdateMissingEntry()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('update', true)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/links/'.'aaa-bbb-ccc-dd', [
            'linkable_type' => get_class(User::all()->first()),
            'linkable_id' => User::all()->first()->id,
            'link' => "https://www.update2.hu",
            'display_text' => "update2",
            'visible' => 0,
        ]);

        $this->validateErrorResponse($response, ['link'], 404);
    }

    /**
     * LinkConroller - update 
     * Error - wrong linktype 
     */
    public function testLinkUpdateWrongLinktype()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('update', true)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/links/'.$link->id->ToString(), [
            'linktype_id' => 'aaa-bbb-ccc-ddd',
            'link' => "https://www.update2.hu",
            'display_text' => "update2",
            'visible' => 0,
        ]);

        $this->validateErrorResponse($response, ['linktype'], 404);
    }

    /**
     * LinkController - Update 
     * Error - wrong linkable
     */
    public function testLinkUpdateWrongLinkable()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('update', true)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/links/'.$link->id->ToString(), [
            'linkable_type' => get_class(User::all()->first()),
            'linkable_id' => 'aaa-bbb-cc-dd',
            'link' => "https://www.update2.hu",
            'display_text' => "update2",
            'visible' => 0,
        ]);

        $this->validateErrorResponse($response, ['linkable_id'], 400);
    }

    /**
     * LinkController - Update 
     * Error - wrong link format 
     */
    public function testLinkUpdateWrongLinkFormat()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('update', true)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->patch('/api/cms/links/'.$link->id->ToString(), [
            'linkable_type' => get_class(User::all()->first()),
            'linkable_id' => User::all()->first()->id,
            'link' => "//www.update",
            'display_text' => "update2",
            'visible' => 0,
        ]);

        $this->validateErrorResponse($response, ['link'], 400);
    }

    /**
     * LinkController - Delete 
     * Success
     */
    public function testLinkDelete()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('delete', true)
        );

        $linkID = $link->id->ToString();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/links/'.$linkID);

        $this->validateSuccessResponse($response, 'links', 1);
    }
    
    /**
     * LinkController - delete 
     * Error - missing entry
     */
    public function testLinkDeleteMissingEntry()
    {
        $token = $this->getApiToken();

        $link = Link::factory(Link::class)->create(
            $this->getData('delete', true)
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->get('/api/cms/links/'.'aaa-bbb-ccc-ddd');

        $this->validateErrorResponse($response, ['link'], 404);
    }

    protected function getData($name)
    {
        $linktype = LinkType::factory(LinkType::class)->create([
            'name' => $name,
            'font_awesome' => 'fa fa-'.$name,
            'color' => "#aabbcc"
        ]);

        return [
            'linktype_id' => $linktype->id->ToString(),
            'linkable_type' => get_class(User::all()->first()),
            'linkable_id' => User::all()->first()->id,
            'link' => "https://www.".$name.".hu",
            'display_text' => $name,
            'visible' => 1,
        ];
    }
}