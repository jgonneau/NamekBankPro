<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Master;

class CreateAdminMasterCommand extends Command
{
    protected static $defaultName = 'CreateAdminMaster';
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager ;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creation d\'un compte ADMIN Master.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email pour le compte administrateur.')
            ->addArgument('firstname', InputArgument::REQUIRED, 'Prénom pour le compte administrateur.')
            ->addArgument('lastname', InputArgument::REQUIRED, 'Nom de famille pour le compte administrateur.')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle( $input, $output);
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        $email = $input->getArgument( 'email');
        $io->note(sprintf('Creation d\'un admin avec cette email: %s', $email));
        $user = new Master();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setRoles([ 'ROLE_ADMIN','ROLE_USER']);
        $this->entityManager->persist( $user);
        $this->entityManager->flush();
        $io->success( sprintf('Administrateur créé avec cette e-mail: %s', $email));
    }
}
