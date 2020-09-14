<?php

namespace App\Command;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\TodoPlanner;
use Mailgun\Mailgun;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendNotificationsCommand extends Command
{
    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var TodoPlanner
     */
    private $todoPlanner;

    public function __construct(string $name = null, UserRepository $userRepo, TodoPlanner $todoPlanner)
    {
        parent::__construct($name);

        $this->userRepo = $userRepo;
        $this->todoPlanner = $todoPlanner;
    }

    protected function configure()
    {
        $this->setName('app:send-notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->sendNotifications();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }

    public function sendNotifications(): void
    {
        $users = $this->userRepo->findAll();

        foreach ($users as $user) {
            $tasks = $this->todoPlanner->getTasksDueToday($user);

            if (empty($tasks)) {
                continue;
            }

            $subject = 'Tasks due today';
            $message = $this->composeMessage($tasks);

            $this->sendEmail($user, $subject, $message);
        }
    }

    private function sendEmail(User $user, string $subject, string $message): void
    {
        $mgClient = new Mailgun(getenv('MAILGUN_API_KEY'));
        $domain = getenv("MAILGUN_DOMAIN");

        $mgClient->sendMessage("$domain", [
            'from'    => 'Recur <' . getenv('MAILGUN_FROM') . '>',
            'to'      => "{$user->getName()}  <{$user->getEmail()}>",
            'subject' => $subject,
            'text'    => $message
        ]);
    }

    /**
     * @param Task[] $tasks
     * @return string
     */
    private function composeMessage(array $tasks): string
    {
        $message = "Tasks due today: \n\n";

        foreach ($tasks as $task) {
            $message .= " - {$task->getName()}\n";
        }

        return $message;
    }
}
