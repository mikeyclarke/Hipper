<?php
declare(strict_types=1);

namespace Hipper\Team;

use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonProfileImageValidator
{
    private const MAX_SIZE = '10M';
    private const MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];

    private ValidatorInterface $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input): void
    {
        $this->validateInput($input);
    }

    private function validateInput(array $input): void
    {
        $constraints = [
            'file' => [
                new Image([
                    'maxSize' => self::MAX_SIZE,
                    'mimeTypes' => self::MIME_TYPES,
                ]),
            ],
        ];

        $collectionConstraint = new Collection($constraints);
        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
