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
        $session = $request->getSession();
        $userAgentProfile = $session->get('user_agent_profile');
        $enterKeyLabel = 'enter';
        if ($userAgentProfile['is_ios']) {
            $enterKeyLabel = 'return';
        }

        $currentUser = $request->attributes->get('current_user');
        $email = $currentUser->getEmailAddress();
        $emailDomain = substr($email, strrpos($email, '@') + 1);

        $context = [
            'html_title' => 'Invite people',
            'is_approved_email_signup_supported' => !$this->isEmailDomainPersonal($emailDomain),
            'email_domain' => $emailDomain,
            'enter_key_label' => $enterKeyLabel,
        ];

        return new Response(
            $this->twig->render('sign_up_flow/invite.twig', $context)
        );
    }

    private function isEmailDomainPersonal(string $emailDomain): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($emailDomain, [new NotPersonalEmailDomain]);
        return count($violations) > 0;
    }
}
