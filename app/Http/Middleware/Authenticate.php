<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return response()->json([
                'status' => 401,
                'meta' => [
                    'count' => 0,
                    'entityType' => 'null',
                ],
                'data' => null,
            ], 401);
            //return route('login');
        }
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     */
    protected function unauthenticated($request, array $guards)
    {
        return response()->json([
            'status' => 401,
            'meta' => [
                'count' => 0,
                'entityType' => 'null',
            ],
            'data' => null,
        ], 401);
        /*
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );*/
    }
}
