<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Service\MediaManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;



#[AsLiveComponent]
final class UserProfile extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?User $initialFormData = null;

    #[LiveProp]
    public ?string $singleFileUploadError = null;

    public function __construct(
        private EntityManagerInterface $em,
        private MediaManager $mediaManager,
        private RequestStack $requestStack,
        private ValidatorInterface $validator
    ) {}

    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(UserProfileType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save(Request $request): void
    {
        $this->submitForm();

        if (!$this->form->isValid()) {
            return;
        }

        // Récupération manuelle du fichier uploadé
        $uploadedFile = $request->files->get('user_profile')['imageFilename'] ?? null;

        if ($uploadedFile instanceof UploadedFile) {
            $filename = $this->mediaManager->upload($uploadedFile, 'user', [
                'maxWidth' => 500,
                'maxHeight' => 500,
            ]);

            /** @var User $user */
            $user = $this->form->getData();
            $user->setImage($filename);
            $this->initialFormData = $user;
        }

        $this->em->flush();
        $this->addFlash('success', 'Profile updated successfully');
    }

    #[LiveAction]
    public function deleteAccount(): RedirectResponse
    {
        $user = $this->initialFormData;

        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash('success', 'Your account has been deleted');

        return $this->redirectToRoute('logout'); // ou page de logout
    }
}
