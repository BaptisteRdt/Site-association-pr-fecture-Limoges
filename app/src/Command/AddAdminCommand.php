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
use App\Utils\Validator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add:admin',
    description: 'add a default Admin user',
)]
class AddAdminCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager,private UserPasswordHasherInterface $passwordHasher,private UserRepository $users)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:add:admin')
            ->setDescription('add a default Admin user')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();

        $user->setUsername("admin");
        $user->setFirstname("admin");
        $user->setLastname("admin");
        $user->setMail("admin@admin.fr");
        $user->setAddress("0 rue de l'admin");
        $user->setBirthdate(" ");
        $user->setGender("homme");
        $hashedPassword = $this->passwordHasher->hashPassword($user, "admin");
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCart(array());
        $user->setImageName("");

        try{
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }catch(\Exception $e){
            $io->error('Error while adding the admin');
            return Command::FAILURE;
        }

        $io->success('new admin user added');

        return Command::SUCCESS;
    }
}
