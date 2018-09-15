<?php
declare(strict_types=1);

namespace hleo\Onboarding;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NameTeamController
{
    public function __construct(

    ) {
        
    }

    public function getAction(Request $request): Response
    {
        return new Response(null);
    }

    public function postAction(Request $request): JsonResponse
    {
        if (!$request->hasPreviousSession()) {
            return new JsonResponse(null, 401);
        }

        $session = $request->getSession();
    }
}
