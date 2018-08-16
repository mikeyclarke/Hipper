<?php
namespace hleo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SignupController extends AbstractController
{
    public function index()
    {
        return $this->render('signup.html.twig');
    }

    public function post()
    {
        return $this->render('base.html.twig');
    }
}
