<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization\Join;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class JoinOrganizationController
{
    private const TERMS_URL = 'https://usehipper.com/terms-of-use';

    private Twig $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');

        $approvedEmailDomains = [];
        if (null !== $organization->getApprovedEmailDomains()) {
            $approvedEmailDomains = $organization->getApprovedEmailDomains();
        }

        $context = [
            'approved_email_domains' => $approvedEmailDomains,
            'html_title' => sprintf('Join %s', $organization->getName()),
            'terms_url' => self::TERMS_URL,
        ];

        return new Response(
            $this->twig->render('organization/join_organization.twig', $context)
        );
    }
}
