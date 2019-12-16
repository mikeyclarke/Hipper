<?php
declare(strict_types=1);

namespace Hipper\Api;

use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiControllerTrait
{
    private function createValidationExceptionResponse(ValidationException $exception, int $code = 400): JsonResponse
    {
        return new JsonResponse(
            [
                'name' => $exception->getName(),
                'message' => $exception->getMessage(),
                'violations' => $exception->getViolations(),
            ],
            $code
        );
    }
}
