<?php

namespace App\Command;

use App\Service\ImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import',
    description: 'Création des roulements à partir des données des fichiers ABC et CRW',
)]
class ImportCommand extends Command
{
    public function __construct(
        private ImportService $importService,
    ) {
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

        $this->importService->createRoulementFromImport();
        $io->success('La base de données a été mis à jour !');

        return Command::SUCCESS;
    }
}
