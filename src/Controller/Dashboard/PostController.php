<?php

namespace App\Controller\Dashboard;

use App\Repository\PostHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale}/dashboard/post', requirements: ['_locale' => 'en|es|fr'], name: 'dashboard_post_')]
final class PostController extends AbstractController
{
    #[Route('/generate', name: 'generate')]
    public function index(): Response
    {
        return $this->render('dashboard/post/index.html.twig');
    }

    #[Route('/history', name: 'history')]
    public function history(Request $request, PostHistoryRepository $postHistoryRepository): Response
    {
        // $posts = $postHistoryRepository->findBy(
        //     ['owner' => $this->getUser()],
        //     ['createdAt' => 'DESC']
        // );


        $user = $this->getUser();
        $limit = 1;
        $currentPage = $request->query->getInt('page', 1);

        $total = $postHistoryRepository->countByUser($user);
        $postHistories = $postHistoryRepository->findPaginatedByUser($user, $currentPage, $limit);

        $totalPages = ceil($total / $limit);

        return $this->render('dashboard/post/history.html.twig', [
            'posts' => $postHistories,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
        ]);
    }
}
