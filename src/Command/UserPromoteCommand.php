<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande console utilisée par l'exploitation pour donner (ou retirer avec
 * --revoke) le rôle ROLE_ADMIN à un utilisateur — nécessaire pour créer le
 * premier administrateur, l'interface d'administration étant réservée à ce rôle.
 */
#[AsCommand(
    name: 'app:user:promote',
    description: 'Donne le rôle ROLE_ADMIN à un utilisateur (--revoke pour le retirer)',
)]
class UserPromoteCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addOption('revoke', null, InputOption::VALUE_NONE, 'Retire le rôle ROLE_ADMIN au lieu de l\'ajouter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneByEmail($email);
        if ($user === null) {
            $io->error(sprintf('Aucun utilisateur avec l\'email "%s".', $email));

            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if ($input->getOption('revoke')) {
            $roles = array_values(array_diff($roles, ['ROLE_ADMIN']));
            $message = sprintf('Rôle ROLE_ADMIN retiré à %s.', $email);
        } else {
            $roles = array_values(array_unique([...$roles, 'ROLE_ADMIN']));
            $message = sprintf('Rôle ROLE_ADMIN donné à %s.', $email);
        }
        $user->setRoles($roles);
        $this->userRepository->save($user, true);

        $io->success($message);

        return Command::SUCCESS;
    }
}
