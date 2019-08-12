<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareSubscriber implements EventSubscriberInterface
{
    private $container;

    public function __construct(
        Container $container
    ) {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -1024],
            KernelEvents::RESPONSE => ['onKernelResponse', 128],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $routeParams = $request->attributes->get('_route_params');

        if (isset($routeParams['_before_middlewares'])) {
            foreach ($routeParams['_before_middlewares'] as $callback) {
                list($serviceId, $method) = explode(':', $callback);
                $service = $this->container->get($serviceId);
                $callback = [$service, $method];
                if (!is_callable($callback)) {
                    return;
                }
                $return = call_user_func($callback, $request);
                if ($return instanceof Response) {
                    $event->setResponse($return);
                }
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $routeParams = $request->attributes->get('_route_params');

        if (isset($routeParams['_after_middlewares'])) {
            foreach ($routeParams['_after_middlewares'] as $callback) {
                list($serviceId, $method) = explode(':', $callback);
                $service = $this->container->get($serviceId);
                $callback = [$service, $method];
                if (!is_callable($callback)) {
                    return;
                }
                $return = call_user_func($callback, $request, $event->getResponse());
                if ($return instanceof Response) {
                    $event->setResponse($return);
                }
            }
        }
    }
}
