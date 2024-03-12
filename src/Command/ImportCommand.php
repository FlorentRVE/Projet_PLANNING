<?php

namespace App\Command;

use App\Service\ImportTxtService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import',
    description: 'Importe les données depuis les fichiers ABC et CRW',
)]
class ImportCommand extends Command
{
    public function __construct(
        private ImportTxtService $importTxt,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Cette commande permet d\'importer les données des fichiers ABC et CRW afin de créer les roulements dans la base de données.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->importTxt->importTxt();
        $io->success('Les données ont été importées dans la base de données !');

        return Command::SUCCESS;
    }
}
