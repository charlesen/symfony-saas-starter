<?php

namespace App\Component;

use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\Component\Form\FormFactoryInterface;

use App\Service\MediaManager;

#[AsLiveComponent('UserProfileLive')]
class UserProfileComponent
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public User $user;
    public $form;

    public function __construct(
        private EntityManagerInterface $em,
        private TokenStorageInterface $tokenStorage,
        private FormFactoryInterface $formFactory,
        private RequestStack $requestStack,
        private MediaManager $mediaManager
    ) {
        $this->user = $this->tokenStorage->getToken()?->getUser();
    }

    public function hydrate(): void
    {
        $this->form = $this->formFactory->create(UserProfileType::class, $this->user);
    }

    #[LiveAction]
    public function save(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $form = $this->formFactory->create(UserProfileType::class, $this->user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion upload image via MediaManager (compatible LiveComponent)
            $uploadedFile = $request->files->get('user_profile')['imageFilename'] ?? null;

            if ($uploadedFile instanceof UploadedFile) {
                $filename = $this->mediaManager->upload($uploadedFile, 'user', [
                    'maxWidth' => 500,
                    'maxHeight' => 500,
                ]);
                // Adapte ce setter si besoin
                if (method_exists($this->user, 'setImage')) {
                    $this->user->setImage($filename);
                } else {
                    $this->user->setImageFilename($filename);
                }
            }
            $this->em->persist($this->user);
            $this->em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            if ($this->user->getLang() !== $request->getLocale()) {
                $this->dispatchBrowserEvent('profile:lang-changed', [
                    'locale' => $this->user->getLang()
                ]);
            }
        }
        $this->form = $form;
    }

    public static function getComponentName(): string
    {
        return 'UserProfileLive';
    }
}
