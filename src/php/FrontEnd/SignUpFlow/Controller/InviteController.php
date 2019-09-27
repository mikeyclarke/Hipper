<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\Validation\Constraints\NotPersonalEmailDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;
use Twig\Environment as Twig;

class InviteController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $person = $request->attributes->get('person');
        $email = $person->getEmailAddress();
        $emailDomain = substr($email, strrpos($email, '@') + 1);

        $context = [
            'html_title' => 'Invites',
            'isApprovedEmailSignupSupported' => !$this->isEmailDomainPersonal($emailDomain),
            'emailDomain' => $emailDomain,
        ];

        return new Response(
            $this->twig->render('onboarding/invite.twig', $context)
        );
    }

    private function isEmailDomainPersonal(string $emailDomain): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($emailDomain, [new NotPersonalEmailDomain]);
        return count($violations) > 0;
    }
}
