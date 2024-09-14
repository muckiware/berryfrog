<?php declare(strict_types=1);
/**
 * @package    Berryfrog
 * @copyright  Copyright (c) 2024 by muckiware
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'app_default_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
