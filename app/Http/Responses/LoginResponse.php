<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): Response
    {
        $home = $request->user()->isAdmin() ? '/admin' : '/dashboard';

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : redirect()->intended($home);
    }
}
