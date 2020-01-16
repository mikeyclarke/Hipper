<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SignOutController
{
    public function signOut(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $session->invalidate();

        return new RedirectResponse('/login');
    }
}
