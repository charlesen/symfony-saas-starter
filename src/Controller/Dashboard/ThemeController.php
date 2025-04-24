<?php

namespace App\Controller\Dashboard;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale}/dashboard/theme', requirements: ['_locale' => 'en|es|fr'], name: 'dashboard_theme_')]
final class ThemeController extends AbstractController
{
    #[Route('/update', name: 'update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $theme = $request->request->get('theme', 'system');
        
        if (!in_array($theme, ['system', 'dark', 'light'])) {
            return new JsonResponse(['error' => 'Invalid theme'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        $user->setPreferedTheme($theme);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
