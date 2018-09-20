<?php
declare(strict_types=1);

namespace Lithos\Onboarding;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InviteTeamController
{
    public function __construct(

    ) {
        
    }

    public function getAction(Request $request): Response
    {
        return new Response();
    }

    public function postAction(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}
