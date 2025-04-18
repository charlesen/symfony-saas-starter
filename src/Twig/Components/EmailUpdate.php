<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\EmailUpdateType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('EmailUpdate')]
class EmailUpdate extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?User $initialFormData = null;

    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private EmailVerifier $emailVerifier
    ) {}

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(EmailUpdateType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function updateEmail(): void
    {
        $this->submitForm();

        if (!$this->form->isValid()) {
            return;
        }

        $user = $this->initialFormData;
        $newEmail = $this->form->get('newEmail')->getData();

        $user->setIsPendingEmail(true);
        $this->em->flush();

        // Envoi de l'email de confirmation
        $this->emailVerifier->sendEmailConfirmation(
            'verify_new_email',
            $user,
            (new TemplatedEmail())
                ->from('no-reply@pg.edounze.com')
                ->to($newEmail)
                ->subject('Confirm your new email address')
                ->htmlTemplate('registration/confirmation_email.html.twig'),
            $newEmail
        );

        $this->addFlash('success', 'A confirmation email has been sent to your new address.');
    }
}
