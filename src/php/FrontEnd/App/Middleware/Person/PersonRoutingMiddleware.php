<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Person;

use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonRoutingMiddleware
{
    private PersonRepository $personRepository;
    private UrlGeneratorInterface $router;

    public function __construct(
        PersonRepository $personRepository,
        UrlGeneratorInterface $router
    ) {
        $this->personRepository = $personRepository;
        $this->router = $router;
    }

    public function before(Request $request)
    {
        $username = $request->attributes->get('username');
        $urlId = $request->attributes->get('url_id');

        $organization = $request->attributes->get('organization');
        $organizationId = $organization->getId();

        $result = $this->personRepository->findOneByUrlId($urlId, $organizationId);
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        if ($username === $result['username']) {
            $person = PersonModel::createFromArray($result);
            $request->attributes->set('person', $person);
        }
    }
}
