<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Constraints\NotPersonalEmailDomain;
use Hipper\Validation\Constraints\NotReservedSubdomain;
use Hipper\Validation\Constraints\UniqueSubdomain;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationValidator
{
    // Pattern taken from HTML5 email validator minus the local-part
    // https://bit.ly/2IrHgZR
    const EMAIL_DOMAIN_PATTERN =
        '/[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/';

    private $validatorInterface;

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
            'name' => [
                new Optional([
                    new NotBlank,
                    new Length([
                        'min' => 3,
                        'max' => 50,
                    ]),
                ]),
            ],
            'subdomain' => [
                new Optional([
                    new NotBlank,
                    new Length([
                        'min' => 3,
                        'max' => 63,
                    ]),
                    new Regex([
                        'pattern' => '/[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?/',
                    ]),
                    new NotReservedSubdomain,
                    new UniqueSubdomain,
                ]),
            ],
            'approved_email_domain_signup_allowed' => [
                new Optional([
                    new NotBlank,
                    new Type([
                        'type' => 'bool',
                    ]),
                ]),
            ],
            'approved_email_domains' => [
                new Optional([
                    new All([
                        new NotBlank,
                        new NotPersonalEmailDomain,
                        new Regex([
                            'pattern' => self::EMAIL_DOMAIN_PATTERN,
                        ]),
                    ]),
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
