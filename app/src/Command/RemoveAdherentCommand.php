<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;

#[AsCommand(
    name: 'app:remove:adherent',
    description: 'remove the role adherent',
)]
class RemoveAdherentCommand extends Command
{
    protected function configure(): void
    {
        $this
        ->setName('app:remove:adherent')
        ->setDescription('remove the role adherent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
 
        $userRepository = $this->getUserRepository();

        //supprime le role adherent de tou les utilisateurs
        $users = $userRepository->findAll();
        foreach($users as $user){
            $user->removeRole('ROLE_ADHERENT');
        }

        $this->getEntityManager()->flush();

        $io->success('The adherent role has been removed');

        return Command::SUCCESS;
    }
}
