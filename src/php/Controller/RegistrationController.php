<?php
namespace hleo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    public function post()
    {
        return $this->render('base.html.twig');
    }
}
