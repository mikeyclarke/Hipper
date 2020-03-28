<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Organization\OrganizationUpdater;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChooseOrganizationUrlController
{
    use \Hipper\Api\ApiControllerTrait;

    private const FINALIZE_ROUTE = 'front_end.sign_up_flow.finalize';

    private OrganizationUpdater $organizationUpdater;
    private UrlGeneratorInterface $router;

    public function __construct(
        OrganizationUpdater $organizationUpdater,
        UrlGeneratorInterface $router
    ) {
        $this->organizationUpdater = $organizationUpdater;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');

        try {
            $this->organizationUpdater->update(
                $currentUser->getOrganizationId(),
                [
                    'subdomain' => $request->request->get('subdomain', '')
                ]
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->router->generate(self::FINALIZE_ROUTE);
        return new JsonResponse(['url' => $url], 200);
    }
}
