<?php
namespace hleo\Validation\Exception;

class ValidationException extends \Exception
{
    const MESSAGE = 'Invalid data';

    private $violations;

    public function __construct(array $violations = [])
    {
        parent::__construct(self::MESSAGE);

        $this->violations = $violations;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}
