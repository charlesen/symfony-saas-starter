<?php

namespace App\Controller\Dashboard;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Service\MediaManager;

// Fichier obsolète : toute la logique a été migrée dans DashboardController.
// Ce contrôleur peut être supprimé.
#[Route('/{_locale}/dashboard/profile', requirements: ['_locale' => 'en|es|fr|de'], name: 'dashboard_profile_')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        // On ne gère plus la sauvegarde ici, tout passe par le LiveComponent
        return $this->render('dashboard/profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
