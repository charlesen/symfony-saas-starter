<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('FlashMessage')]
class FlashMessage
{
    use DefaultActionTrait;

    public function __construct(){}
}
