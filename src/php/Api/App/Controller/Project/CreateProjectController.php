<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Project;

use Hipper\Project\ProjectCreator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CreateProjectController
{
    use \Hipper\Api\ApiControllerTrait;

    const PROJECT_ROUTE_NAME = 'front_end.app.project.show';

    private $projectCreator;
    private $router;

    public function __construct(
        ProjectCreator $projectCreator,
        UrlGeneratorInterface $router
    ) {
        $this->projectCreator = $projectCreator;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');

        try {
            $projectModel = $this->projectCreator->create($currentUser, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse([
            'project_url' => $this->router->generate(
                self::PROJECT_ROUTE_NAME,
                [
                    'project_url_id' => $projectModel->getUrlId(),
                    'subdomain' => $organization->getSubdomain(),
                ]
            ),
        ], 201);
    }
}
