<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    private $personRepository;

    public function __construct(
        PersonRepository $personRepository
    ) {
        $this->personRepository = $personRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $routeParams = $request->attributes->get('_route_params');

        if ($this->isUnsecuredRoute($routeParams)) {
            return;
        }

        if (!$request->hasPreviousSession() || !$this->hasValidSession($session)) {
            $this->createUnauthorizedResponse($request, $event);
            return;
        }

        $person = $this->personRepository->findById($session->get('_personId'));
        if (null === $person) {
            $this->createUnauthorizedResponse($request, $event);
            return;
        }

        if ($this->isForeignOrganizationContext($request, $person)) {
            $this->createUnauthorizedResponse($request, $event);
            return;
        }

        $personModel = PersonModel::createFromArray($person);
        $request->attributes->set('current_user', $personModel);
    }

    private function isForeignOrganizationContext(Request $request, array $person): bool
    {
        if ($request->attributes->get('isOrganizationContext') === false) {
            return false;
        }

        $organization = $request->attributes->get('organization');
        return $organization->getId() !== $person['organization_id'];
    }

    private function createUnauthorizedResponse(Request $request, RequestEvent $event): void
    {
        if ($request->headers->has('X-Requested-With') && $request->headers->get('X-Requested-With') === 'Fetch') {
            $event->setResponse(new JsonResponse(null, 401));
            return;
        }

        $url = $this->getUnauthorizedResponseRedirectUrl($request);

        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }

    private function getUnauthorizedResponseRedirectUrl(Request $request): string
    {
        if ($request->attributes->get('isOrganizationContext') === false) {
            return '/';
        }

        $requestUri = $request->getRequestUri();
        $url = '/login';

        if ($request->isMethod('GET') && $requestUri !== '/') {
            $url = $url . '?r=' . rawurlencode($requestUri);
        }

        return $url;
    }

    private function hasValidSession(SessionInterface $session): bool
    {
        return $session->has('_personId');
    }

    private function isUnsecuredRoute(array $routeParams): bool
    {
        return isset($routeParams['unsecured']) && (bool) $routeParams['unsecured'];
    }
}
