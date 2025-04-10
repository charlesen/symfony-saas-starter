<?php

namespace App\Twig\Components;

use App\Entity\PostHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PostGenerator')]
final class PostGenerator
{
    use DefaultActionTrait;

    public const TONES = [
        'Professional',
        'Casual',
        'Inspirational',
        'Humorous',
        'Educational',
    ];

    public const AUDIENCES = [
        'General',
        'Developers',
        'Entrepreneurs',
        'Marketers',
        'Job Seekers',
    ];

    public const STYLES = [
        'Inspiration',
        'Storytelling',
        'Cta',
        'Thread',
        'Stat opinion',
        'Service announcement',
    ];

    public const LANGUAGES = [
        'English',
        'Français',
        'Español',
        'Deutsch',
    ];



    #[LiveProp(writable: true)] public string $topic = '';
    #[LiveProp(writable: true)] public int $tone = 0;
    #[LiveProp(writable: true)] public int $audience = 0;
    #[LiveProp(writable: true)] public int $promptStyle = 0;
    #[LiveProp(writable: true)] public int $language = 0;
    #[LiveProp(writable: true)] public string $keywords = '';
    #[LiveProp(writable: true)] public int $limit = 3;

    public array $results = [];
    public ?string $error = null;

    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $em,
        private Security $security,
    ) {}

    #[LiveAction]
    public function generate(): void
    {
        $prompt = $this->buildPrompt();

        try {
            $response = $this->client->request('POST', $_ENV['OPENAI_API_URL'] . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $_ENV['OPENAI_MODEL'] ?? 'gpt-3.5-turbo',
                    'messages' => [
                        ["role" => "system", "content" => "You are a helpful assistant generating LinkedIn posts."],
                        ["role" => "user", "content" => $prompt],
                    ],
                ],
            ]);

            $data = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '';

            $this->results = array_map('trim', explode('###', $content));

            // Sauvegarde des posts générés
            foreach ($this->results as $result) {
                $history = new PostHistory();
                $history->setContent($result);
                $history->setOwner($this->security->getUser());
                $this->em->persist($history);
            }

            $this->em->flush();
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    #[LiveAction]
    public function loadMore(): void
    {
        $this->limit += 3;
        $this->generate();
    }

    private function buildPrompt(): string
    {
        $template = $this->getPromptTemplate($this->getRealValue(self::STYLES, $this->promptStyle));

        $prompt = strtr($template, [
            '{topic}'    => $this->topic,
            '{tone}'     => $this->getRealValue(self::TONES, $this->tone),
            '{audience}' => $this->getRealValue(self::AUDIENCES, $this->audience),
            '{language}' => $this->getRealValue(self::LANGUAGES, $this->language),
        ]);

        if ($this->keywords) {
            $prompt .= "\n\nKeywords to include: {$this->keywords}";
        }

        $prompt .= "\n\nGenerate 3 different LinkedIn posts in the following language: {$this->getRealValue(self::LANGUAGES,$this->language)}. Separate each post with '###'. Only answer in {$this->getRealValue(self::LANGUAGES,$this->language)}.";

        return $prompt;
    }

    private function getRealValue(array $values, int $index): string
    {
        return $values[$index] ?? '';
    }

    private function getPromptTemplate(string $style): ?string
    {
        return [
            'Inspiration' => "You are a LinkedIn expert helping freelancers and creators post inspiring content.\nGenerate a short post (3-5 lines) on the topic: \"{topic}\".\nThe post should include:\n- An emotion or personal lesson\n- A motivational or engaging twist\n- A closing thought-provoking sentence\n\nLanguage: {language}\nTone: {tone}\nStyle: native LinkedIn, no hashtags",

            'Storytelling' => "You are a LinkedIn coach. Help transform a simple anecdote into a powerful post.\nHere is the anecdote: \"{topic}\"\nStructure:\n- Context (hook)\n- Key moment or challenge\n- Resolution or lesson\n- Reflective or engaging closing\n\nLanguage: {language}\nTone: {tone}\nStyle: narrative, smooth, no hashtags",

            'Cta' => "Write a professional LinkedIn post with a clear call-to-action for: {topic}\nThe post should:\n- Grab attention from the first line\n- Explain how it helps the target\n- End with a simple CTA (e.g., 'DM me', 'Let’s connect')\n\nLanguage: {language}\nTone: {tone}\nStyle: concise, result-oriented",

            'Thread' => "Create an educational LinkedIn thread in 5 points max.\nTopic: \"{topic}\"\nEach point should be clear and accessible to non-experts.\nStart with a strong hook, end with a conclusion encouraging engagement.\n\nLanguage: {language}\nTone: {tone}\nStyle: numbered thread format (e.g., 1., 2., …)",

            'Stat opinion' => "Create a post around this statistic: \"{topic}\"\nGoal: shock, raise awareness, or provoke thought.\nStructure:\n- Shocking stat\n- Context or explanation\n- Personal interpretation\n- Closing with an engaging question\n\nLanguage: {language}\nTone: {tone}",

            'Service announcement' => "You are a consultant announcing a new service offering.\nTopic: \"{topic}\"\nThe post should:\n- Avoid sounding like a promotion\n- Deliver value before selling\n- Naturally invite conversation at the end\n\nLanguage: {language}\nTone: {tone}",
        ][$style] ?? null;
    }
}
