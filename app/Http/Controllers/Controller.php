<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function handleResponseSuccess($count, $entityType, $data, $token = null)
    {
        $responseData = [
            'status' => 200,
            'meta' => [
                'count' => $count,
                'entityType' => $entityType,
            ],
            'data' => $data
        ];

        if ($token) {
            $responseData['authorisation'] = [
                'token' => $token,
                'type' => 'bearer',
            ];
        }

        return $responseData;
    }

    protected function handleResponseError($status, $errors = [])
    {
        return [
            'status' => $status,
            'error' => $errors,
        ];
    }

    protected function handleListResponseSuccess($count, $entityType, $data, $headers, $key, $value)
    {
        $responseData = $this->handleResponseSuccess($count, $entityType, $data);
        $responseData['meta']['headers'] = $headers;
        $responseData['meta']['key'] = $key;
        $responseData['meta']['value'] = $value;

        return $responseData;
    }

    
    protected function handleEntityExist($entity, $entityType, $entityNameOnError)
    {
        if (!$entity) {
            
            return [
                'status' => 404,
                'error' => [$entityNameOnError => [ ucwords($entityNameOnError).' not found.']]
            ];
        }

        return [
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => $entityType,
            ],
            'data' => $entity,
        ];
    }


}
