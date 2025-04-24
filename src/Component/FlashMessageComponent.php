<?php

namespace App\Component;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[AsLiveComponent('FlashMessage')]
class FlashMessageComponent
{
    use DefaultActionTrait;

    private array $flashes = [];

    public function __construct(private RequestStack $requestStack)
    {
        $this->initFlashes();
    }

    public function getFlashes(): array
    {
        return $this->flashes;
    }

    private function initFlashes(): void
    {
        $session = $this->requestStack->getSession();
        if ($session instanceof SessionInterface) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $session->getFlashBag();
            $this->flashes = [
                'success' => $flashBag->get('success'),
                'info' => $flashBag->get('info'),
                'danger' => $flashBag->get('danger'),
                'warning' => $flashBag->get('warning'),
            ];
        }
    }
}
