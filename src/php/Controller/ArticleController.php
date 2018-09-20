<?php
declare(strict_types=1);

namespace Lithos\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    public function get(?string $id): Response
    {
        return new Response('hleo world');
    }
}
