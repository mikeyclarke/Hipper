<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Project;

use Hipper\Project\Project;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CreateProjectController
{
    private $project;
    private $router;

    public function __construct(
        Project $project,
        UrlGeneratorInterface $router
    ) {
        $this->project = $project;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $projectModel = $this->project->create($person, $request->request->all());
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        }

        return new JsonResponse([
            'project_url' => $this->router->generate('project.get', ['project_url_id' => $projectModel->getUrlId()]),
        ], 201);
    }
}
