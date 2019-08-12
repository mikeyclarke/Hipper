<?php
declare(strict_types=1);

namespace Hipper\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function index()
    {
        return $this->render('base.html.twig');
    }
}
