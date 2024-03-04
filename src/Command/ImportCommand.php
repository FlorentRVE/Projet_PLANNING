<?php

namespace App\Command;

use App\Service\ImportExcelService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import',
    description: 'Import data from excel',
)]
class ImportCommand extends Command
{
    public function __construct(
        private ImportExcelService $importExcel
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Cette commande permet d\'importer les données d\'un fichier Excel')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->importExcel->importExcel();
        $io->success('Les données ont été importées dans la base de données !');

        return Command::SUCCESS;
    }
}
