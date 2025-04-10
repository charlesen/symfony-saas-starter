<?php

namespace App\Twig\Extensions;

use App\Twig\Components\PostGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PostGeneratorExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('postgen_options', [$this, 'getOptions']),
        ];
    }

    public function getOptions(string $type): array
    {
        return match ($type) {
            'tone' => PostGenerator::TONES,
            'audience' => PostGenerator::AUDIENCES,
            'style' => PostGenerator::STYLES,
            'language' => PostGenerator::LANGUAGES,
            default => [],
        };
    }
}
