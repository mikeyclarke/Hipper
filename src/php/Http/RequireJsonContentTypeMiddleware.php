<?php
declare(strict_types=1);

namespace Hipper\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class RequireJsonContentTypeMiddleware
{
    public function before(Request $request)
    {
        if ('json' !== $request->getContentType()) {
            throw new UnsupportedMediaTypeHttpException('Content-Type must be JSON');
        }

        $body = json_decode($request->getContent(), true);
        $request->request->replace(is_array($body) ? $body : []);
    }
}
