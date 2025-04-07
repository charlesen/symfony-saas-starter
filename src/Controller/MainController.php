<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'home_no_locale')]
    public function homeNoLocale(): Response
    {
        return $this->redirectToRoute('home', ['_locale' => 'en']);
    }

    #[Route('/login', name: 'login_no_locale')]
    public function loginNoLocale(): Response
    {
        return $this->redirectToRoute('login', ['_locale' => 'en']);
    }

    #[Route('/register', name: 'register_no_locale')]
    public function registerNoLocale(): Response
    {
        return $this->redirectToRoute('register', ['_locale' => 'en']);
    }

    #[Route('/dashboard', name: 'dashboard_no_locale')]
    public function dashboardNoLocale(): Response
    {
        return $this->redirectToRoute('dashboard_index', ['_locale' => 'en']);
    }
}
