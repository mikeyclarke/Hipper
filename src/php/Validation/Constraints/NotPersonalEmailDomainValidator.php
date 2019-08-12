<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NotPersonalEmailDomainValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotPersonalEmailDomain) {
            throw new UnexpectedTypeException($constraint, NotPersonalEmailDomain::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $blacklist = require __DIR__ . '/../../data/webmail_domains.php';

        foreach ($blacklist['regexPatterns'] as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value)
                    ->addViolation();
                return;
            }
        }

        if (in_array($value, $blacklist['domains'])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
