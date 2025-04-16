<?php

namespace App\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('auto_format_ia', [$this, 'autoFormatIaContent'], ['is_safe' => ['html']]),
        ];
    }

    public function autoFormatIaContent(string $content): string
    {
        // Titres H1 et H2
        $content = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $content);
        $content = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $content);

        // Blockquotes >
        $content = preg_replace('/^>\s?(.*)$/m', '<blockquote>$1</blockquote>', $content);

        // Conversion **bold** en <strong>
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);

        // Italique *text*
        $content = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*(?!\*)/s', '<em>$1</em>', $content);

        // Séparateur horizontal : --- ou ***
        $content = preg_replace('/^(\-\-\-|\*\*\*)$/m', '<hr>', $content);

        // Gestion des listes "- item" ou "* item"
        $content = preg_replace('/(\n|^)[\-\*]\s(.*?)(\n|$)/', '$1<li>$2</li>$3', $content);


        // Encapsuler les <li> détectés dans <ul>
        $content = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $content);

        // Gestion des numéros "1. item"
        $content = preg_replace('/(\n|^)[0-9]+\.\s(.*?)(\n|$)/', '$1<li>$2</li>$3', $content);

        // Liens automatiques
        $content = preg_replace(
            '/(?<!href=["\'])((https?|ftp):\/\/[^\s<]+)/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $content
        );

        // Remettre dans <ol> si présence de plusieurs <li> numérotés
        if (preg_match_all('/<li>.*<\/li>/s', $content) > 1) {
            $content = preg_replace('/(<li>.*<\/li>)/s', '<ol>$1</ol>', $content, 1);
        }

        // Convertit les sauts de ligne restants en <br>
        $content = nl2br($content);

        return $content;
    }
}
