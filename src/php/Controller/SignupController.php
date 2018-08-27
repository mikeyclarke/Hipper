<?php
namespace hleo\Controller;

use hleo\Person\PersonCreator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class SignupController
{
    private $personCreator;
    private $twig;

    public function __construct(
        PersonCreator $personCreator,
        Twig_Environment $twig
    ) {
        $this->personCreator = $personCreator;
        $this->twig = $twig;
    }

    public function getAction(Request $request)
    {
        return new Response(
            $this->twig->render('signup.twig')
        );
    }

    public function postAction(Request $request)
    {
        $this->personCreator->create($request->request->all());
        return new RedirectResponse('/await-email-verification');
    }
}
