<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'email:tester',
    description: 'Test email sending',
)]
class EmailTesterCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('emailTo', InputArgument::OPTIONAL, 'Email to send the test to', 'charles@edounze.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $to = $input->getArgument('emailTo');
        $email = (new Email())
            ->from('no-reply@edounze.com')
            ->to($to)
            ->subject('✅ Test email depuis Symfony')
            ->html('<p>Ceci est un <strong>e-mail de test</strong> envoyé depuis ta commande Symfony.</p>');

        try {
            $this->mailer->send($email);
            $output->writeln('<info>✅ Email envoyé avec succès !</info>');
        } catch (\Throwable $e) {
            $output->writeln('<error>❌ Erreur lors de l’envoi : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
