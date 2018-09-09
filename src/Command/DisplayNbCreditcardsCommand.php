<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Repository\CreditcardRepository;

class DisplayNbCreditcardsCommand extends Command
{
    protected static $defaultName = 'DisplayNbCreditcards';
    private $creditcardRepository;

    public function __construct(CreditcardRepository $creditcardRepository)
    {
        $this->creditcardRepository = $creditcardRepository ;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Affiche le nombre de carte de crédits.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        //L'on recupere toutes les creditcards
        $creditcards = $this->creditcardRepository->findAll();

        //L'on affiche le nombre de lignes recuperées
        $io->text(sprintf('Il existe actuellement %s de cartes de crédit.\n', count($creditcards)));
    }
}
