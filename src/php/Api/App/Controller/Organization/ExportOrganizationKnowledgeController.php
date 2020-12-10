<?php

declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Organization\RequestKnowledgeExport;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ExportOrganizationKnowledgeController
{
    use \Hipper\Api\ApiControllerTrait;

    private RequestKnowledgeExport $requestKnowledgeExport;

    public function __construct(
        RequestKnowledgeExport $requestKnowledgeExport
    ) {
        $this->requestKnowledgeExport = $requestKnowledgeExport;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        try {
            $this->requestKnowledgeExport->createRequest($organization, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 202);
    }
}
