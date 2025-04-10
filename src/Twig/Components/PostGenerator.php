<?php

namespace App\Twig\Components;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;

#[AsLiveComponent(name: 'PostGenerator')]
final class PostGenerator
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $topic = '';

    #[LiveProp(writable: true)]
    public string $tone = 'Professional';

    #[LiveProp(writable: true)]
    public string $audience = 'General';

    #[LiveProp(writable: true)]
    public string $promptStyle = 'inspiration';

    #[LiveProp(writable: true)]
    public string $language = 'English';

    public ?string $result = null;
    public ?string $error = null;

    #[LiveAction]
    public function generate(): void
    {
        $client = HttpClient::create();

        $template = $this->getPromptTemplate($this->promptStyle);

        if (!$template) {
            $this->error = 'Invalid prompt style';
            return;
        }

        $prompt = strtr($template, [
            '{topic}' => $this->topic,
            '{tone}' => $this->tone,
            '{audience}' => $this->audience,
            '{language}' => $this->language,
        ]);

        try {
            $response = $client->request('POST', $_ENV['OPENAI_API_URL'] ?? 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($_ENV['OPENAI_API_KEY'] ?? ''),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $_ENV['OPENAI_MODEL'] ?? 'gpt-3.5-turbo',
                    'messages' => [
                        ["role" => "system", "content" => "You are a helpful assistant that generates LinkedIn posts."],
                        ["role" => "user", "content" => $prompt],
                    ],
                ],
            ]);

            $content = $response->toArray();
            $this->result = $content['choices'][0]['message']['content'] ?? 'No content returned';
        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
        }
    }

    private function getPromptTemplate(string $style): ?string
    {
        return [
            'inspiration' => "You are a LinkedIn expert helping freelancers and creators post inspiring content.\nGenerate a short post (3-5 lines) on the topic: \"{topic}\".\nThe post should include:\n- An emotion or personal lesson\n- A motivational or engaging twist\n- A closing thought-provoking sentence\n\nLanguage: {language}\nTone: {tone}\nStyle: native LinkedIn, no hashtags",

            'storytelling' => "You are a LinkedIn coach. Help transform a simple anecdote into a powerful post.\nHere is the anecdote: \"{topic}\"\nStructure:\n- Context (hook)\n- Key moment or challenge\n- Resolution or lesson\n- Reflective or engaging closing\n\nLanguage: {language}\nTone: {tone}\nStyle: narrative, smooth, no hashtags",

            'cta' => "Write a professional LinkedIn post with a clear call-to-action for: {topic}\nThe post should:\n- Grab attention from the first line\n- Explain how it helps the target\n- End with a simple CTA (e.g., 'DM me', 'Let’s connect')\n\nLanguage: {language}\nTone: {tone}\nStyle: concise, result-oriented",

            'thread' => "Create an educational LinkedIn thread in 5 points max.\nTopic: \"{topic}\"\nEach point should be clear and accessible to non-experts.\nStart with a strong hook, end with a conclusion encouraging engagement.\n\nLanguage: {language}\nTone: {tone}\nStyle: numbered thread format (e.g., 1., 2., …)",

            'stat_opinion' => "Create a post around this statistic: \"{topic}\"\nGoal: shock, raise awareness, or provoke thought.\nStructure:\n- Shocking stat\n- Context or explanation\n- Personal interpretation\n- Closing with an engaging question\n\nLanguage: {language}\nTone: {tone}",

            'service_announcement' => "You are a consultant announcing a new service offering.\nTopic: \"{topic}\"\nThe post should:\n- Avoid sounding like a promotion\n- Deliver value before selling\n- Naturally invite conversation at the end\n\nLanguage: {language}\nTone: {tone}",
        ][$style] ?? null;
    }
}
