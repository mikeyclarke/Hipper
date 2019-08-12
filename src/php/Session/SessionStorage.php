<?php
declare(strict_types=1);

namespace Hipper\Session;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionStorage extends NativeSessionStorage
{
    public function __construct(
        array $options,
        $handler = null,
        MetadataBag $metaBag = null,
        RequestStack $requestStack
    ) {
        $host = $requestStack->getMasterRequest()->getHttpHost();
        $options['name'] = 'a-s';

        $parts = explode('.', $host);
        if (count($parts) === 2) {
            $options['name'] = 'o-s';
        }

        parent::__construct($options, $handler, $metaBag);
    }
}
