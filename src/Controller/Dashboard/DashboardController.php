<?php

namespace App\Controller\Dashboard;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale}/dashboard', requirements: ['_locale' => 'en|es|fr|de'], name: 'dashboard_')]
final class DashboardController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        // $this->addFlash('success', 'Profile updated successfully');
        /** @var User $user */
        $user = $this->getUser();
        // On ne gère plus la sauvegarde ici, tout passe par le LiveComponent
        return $this->render('dashboard/profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
