<?php

namespace App\Twig\Components;

use App\Entity\PostHistory;
use App\Repository\PostHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PostGenerator')]
final class PostGenerator extends AbstractController
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
    #[LiveProp(writable: true)] public ?int $editingPostId = null;

    public array $results = [];
    public ?string $error = null;

    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $em,
        private Security $security,
        private PostHistoryRepository $postHistoryRepository
    ) {}

    public function mount(): void
    {
        $this->results = $this->postHistoryRepository->findPaginatedByUser($this->security->getUser());
    }

    #[LiveAction]
    public function edit(int $id): void
    {
        $this->editingPostId = $id;
    }

    #[LiveAction]
    public function cancelEdit(): void
    {
        $this->editingPostId = null;
    }

    #[LiveAction]
    public function saveEdit(int $id, string $content): void
    {
        $post = $this->postHistoryRepository->find($id);

        if (!$post || $post->getOwner() !== $this->security->getUser()) {
            $this->addFlash('error', 'Not allowed.');
            return;
        }

        $post->setContent($content);
        $this->em->flush();

        $this->editingPostId = null;
        $this->loadMore();
    }


    #[LiveAction]
    public function generate()
    {
        if (!$this->security->getUser()) {
            return $this->redirectToRoute('login');
        }
        $prompt = $this->buildOptimizedPrompt();

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

            $results = array_map('trim', explode('###', $content));

            // Sauvegarde des posts générés
            foreach ($results as $result) {
                if (!empty($result)) {
                    $history = new PostHistory();
                    $history->setContent($result);
                    $history->setOwner($this->security->getUser());
                    $this->em->persist($history);
                    $this->results[] = $history;
                }
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

    private function buildOptimizedPrompt(): string
    {
        $tone = $this->getRealValue(self::TONES, $this->tone);
        $audience = $this->getRealValue(self::AUDIENCES, $this->audience);
        $language = $this->getRealValue(self::LANGUAGES, $this->language);

        return <<<PROMPT
        Role: You are a Senior Data Scientist specialized in social media analysis and content strategy with over 10 years of experience optimizing LinkedIn posts for top creators. You excel in creating viral and qualitative hooks, with posts generating an average of 500 likes, and 30% of your content exceeding 1,000 likes.

        Objective: Create a LinkedIn post in {$language} that is engaging, concise, and likely to go viral, based on the topic provided.

        Topic: "{$this->topic}"

        Tone: {$tone}
        Audience: {$audience}
        Keywords (optional): {$this->keywords}

        Constraints:
        - Length between 1500 to 2000 characters.
        - Structure:
        1. Hook (max 49 characters)
        2. Re-Hook (max 51 characters)
        3. Body (structured, clear, examples, stats, lists)
        4. End of Body (short conclusion)
        5. CTA (interaction)
        6. 2nd CTA (share / comment)

        Important:
        - Write ONLY in {$language}
        - Separate each generated post with '###'
        - Generate {$this->limit} different posts
        - Maximize engagement, readability, and authenticity
        - No hashtags except if highly relevant
        - Use bullet points / lists if appropriate
        PROMPT;
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
