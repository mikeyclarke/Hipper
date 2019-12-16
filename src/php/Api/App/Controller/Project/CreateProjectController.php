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
    use \Hipper\Api\ApiControllerTrait;

    const PROJECT_ROUTE_NAME = 'front_end.app.project.show';

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
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse([
            'project_url' => $this->router->generate(
                self::PROJECT_ROUTE_NAME,
                ['project_url_id' => $projectModel->getUrlId()]
            ),
        ], 201);
    }
}
