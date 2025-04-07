<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale}', name: 'dashboard_')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'index')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }
}
