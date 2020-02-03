<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Section;

use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Section\Section;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateSectionController
{
    use \Hipper\Api\ApiControllerTrait;

    private $knowledgebaseRouteUrlGenerator;
    private $section;

    public function __construct(
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Section $section
    ) {
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->section = $section;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');

        try {
            list($model, $route, $knowledgebaseOwner) = $this->section->create($currentUser, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);
        return new JsonResponse(['section_url' => $url], 201);
    }
}
