<?php
declare(strict_types=1);

namespace Hipper\Validation\Exception;

class ValidationException extends \Exception
{
    const MESSAGE = 'Invalid data';
    const NAME = 'invalid_request_payload';

    private $violations;

    public function __construct(array $violations = [])
    {
        parent::__construct(self::MESSAGE);

        $this->violations = $violations;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}
