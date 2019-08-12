<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Hipper\Security\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CsrfTokenSubscriber implements EventSubscriberInterface
{
    const SESSION_ATTRIBUTE = 'csrf_token';
    const SAFE_HTTP_METHODS = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];
    const TOKEN_HEADER_NAME = 'X-CSRF-Token';
    const TOKEN_BODY_NAME = 'csrf_token';

    private $tokenGenerator;

    public function __construct(
        TokenGenerator $tokenGenerator
    ) {
        $this->tokenGenerator = $tokenGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -5]
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        $csrfToken = $this->createOrRetrieveToken($session);

        if (!$this->requestRequiresToken($request)) {
            return;
        }

        $this->validateToken($event, $request, $csrfToken);
    }

    private function createInvalidTokenResponse(RequestEvent $event, Request $request, string $csrfToken): void
    {
        if ($request->getContentType() === 'json') {
            $response = new JsonResponse(null, 419, ['X-CSRF-Reset' => $csrfToken]);
        } else {
            $response = new Response(null, 419);
        }
        $event->setResponse($response);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        if ($request->getContentType() === 'json') {
            return $request->headers->get(self::TOKEN_HEADER_NAME);
        }
        return $request->request->get(self::BODY_TOKEN_NAME);
    }

    private function validateToken(RequestEvent $event, Request $request, string $csrfToken): void
    {
        $requestToken = $this->getTokenFromRequest($request);
        if (null === $requestToken || $requestToken !== $csrfToken) {
            $this->createInvalidTokenResponse($event, $request, $csrfToken);
        }
    }

    private function requestRequiresToken(Request $request): bool
    {
        return !in_array($request->getMethod(), self::SAFE_HTTP_METHODS);
    }

    private function createOrRetrieveToken(SessionInterface $session): string
    {
        if ($session->has(self::SESSION_ATTRIBUTE)) {
            return $session->get(self::SESSION_ATTRIBUTE);
        }

        $token = $this->tokenGenerator->generate();
        $session->set(self::SESSION_ATTRIBUTE, $token);
        return $token;
    }
}
