<?php
declare(strict_types=1);

namespace Lithos\Listener;

use Lithos\Person\PersonModelMapper;
use Lithos\Person\PersonRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionListener implements EventSubscriberInterface
{
    const UNAUTHENTICATED_ROUTES = [
        '/',
        '/sign-up',
        '/_/sign-up',
    ];

    private $personModelMapper;
    private $personRepository;
    private $session;

    public function __construct(
        PersonModelMapper $personModelMapper,
        PersonRepository $personRepository,
        SessionInterface $session
    ) {
        $this->personModelMapper = $personModelMapper;
        $this->personRepository = $personRepository;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 512],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        if ($this->isUnauthenticatedRoute($request)) {
            return;
        }

        // Only throw a 401 if route exists

        if (!$this->hasValidSession()) {
            $response = new Response(null, 401);
            $event->setResponse($response);
            return;
        }

        $person = $this->personRepository->findById($this->session->get('_personId'));
        if ($this->session->get('_password') !== $person['password']) {
            $response = new Response(null, 401);
            $event->setResponse($response);
            return;
        }
        unset($person['password']);

        $personModel = $this->personModelMapper->createFromArray($person);
        $request->attributes->set('person', $personModel);
    }

    private function isUnauthenticatedRoute(Request $request)
    {
        $parts = explode('.', $request->getHttpHost());
        if (count($parts) === 3) {
            return false;
        }
        return in_array($request->getPathInfo(), self::UNAUTHENTICATED_ROUTES);
    }

    private function hasValidSession(): bool
    {
        return $this->session->has('_password') && $this->session->has('_personId');
    }
}
