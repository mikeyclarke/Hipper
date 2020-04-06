<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization\Join;

use Hipper\Invite\InviteModel;
use Hipper\Invite\InviteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validation;
use Twig\Environment as Twig;

class JoinByInvitationController
{
    private const TERMS_URL = 'https://usehipper.com/terms-of-use';

    private InviteRepository $inviteRepository;
    private Twig $twig;

    public function __construct(
        InviteRepository $inviteRepository,
        Twig $twig
    ) {
        $this->inviteRepository = $inviteRepository;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $inviteId = null;
        $inviteToken = null;
        $invite = null;

        if ($request->query->has('i') && $request->query->has('t') && $this->isUuid($request->query->get('i'))) {
            $inviteId = $request->query->get('i');
            $inviteToken = $request->query->get('t');

            $result = $this->inviteRepository->find($inviteId, $organization->getId(), $inviteToken);
            if (null !== $result) {
                $invite = InviteModel::createFromArray($result);
            }
        }

        $context = [
            'html_title' => sprintf('Join %s', $organization->getName()),
            'invite' => $invite,
            'invite_id' => $inviteId,
            'invite_token' => $inviteToken,
            'terms_url' => self::TERMS_URL,
        ];

        return new Response(
            $this->twig->render('organization/join_by_invite.twig', $context)
        );
    }

    private function isUuid(string $inviteId): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($inviteId, [new Uuid]);
        return count($violations) === 0;
    }
}
