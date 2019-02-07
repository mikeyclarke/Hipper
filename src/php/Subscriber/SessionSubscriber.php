<?php
declare(strict_types=1);

namespace Lithos\Subscriber;

use Lithos\Person\PersonModelMapper;
use Lithos\Person\PersonRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionSubscriber implements EventSubscriberInterface
{
    private $personModelMapper;
    private $personRepository;

    public function __construct(
        PersonModelMapper $personModelMapper,
        PersonRepository $personRepository
    ) {
        $this->personModelMapper = $personModelMapper;
        $this->personRepository = $personRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
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
            $response = new Response(null, 401);
            $event->setResponse($response);
            return;
        }

        $person = $this->personRepository->findById($session->get('_personId'));

        if (!$this->passesAuthentication($session, $person)) {
            $response = new Response(null, 401);
            $event->setResponse($response);
            return;
        }
        unset($person['password']);

        $personModel = $this->personModelMapper->createFromArray($person);
        $request->attributes->set('person', $personModel);
    }

    private function passesAuthentication(SessionInterface $session, array $person): bool
    {
        return $session->get('_password') === $person['password'];
    }

    private function hasValidSession(SessionInterface $session): bool
    {
        return $session->has('_password') && $session->has('_personId');
    }

    private function isUnsecuredRoute(array $routeParams): bool
    {
        return isset($routeParams['unsecured']) && (bool) $routeParams['unsecured'];
    }
}
