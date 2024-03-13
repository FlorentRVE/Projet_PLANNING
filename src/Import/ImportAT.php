<?php

namespace App\Import;

use App\Helper\TimeHelper;
use DateTime;

class ImportAT extends Import
{
    private ?DateTime $date = null;

    public function import(): void
    {
        foreach ($this->dataFromFile as $row) {
            $rowId = (int)$row[0];
            switch($rowId) {
                case 0:
                    $this->importDate($row);
                    break;
                case 1:
                    $this->importServiceMatricule($row);
                    break;
                case 2:
                    $this->importSortieDepot($row);
                    break;
                case 4:
                    $this->importConducteurMatricule($row);
                    break;
            }
        }
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    private function importDate(array $row): void
    {
        $this->date = DateTime::createFromFormat('d/m/Y', $row[1]);
    }

    private function importServiceMatricule(array $row): void
    {
        $service = $row[1];
        $matricule = $row[2];

        // on index par service
        $this->data[$service]["service"] = $service;
        $this->data[$service]["matricule"] = $matricule ;
    }

    private function importSortieDepot(array $row): void
    {
        $service = $row[3];
        $lieu_sortie = $row[4];
        $heure_sortie = TimeHelper::convertSecondsToHoursMinutes($row[6]);
        $lieu_rentree = $row[5];
        $heure_rentree = TimeHelper::convertSecondsToHoursMinutes($row[7]);

        if ($lieu_sortie == 'DEPOT') {
            $this->data[$service]["lieu_sortie"] = $lieu_sortie;
            $this->data[$service]["heure_sortie"] = $heure_sortie;
        }

        if ($lieu_rentree == 'DEPOT') {
            $this->data[$service]["lieu_rentree"] = $lieu_rentree;
            $this->data[$service]["heure_rentree"] = $heure_rentree;
        }

    }
    private function importConducteurMatricule(array $row): void
    {
        $matricule = $row[1];
        $conducteur = $row[2];

        foreach($this->data as $key => $service) {
            if ($service['matricule'] == $matricule) {
                $this->data[$key]['nom'] = $conducteur;
            }
        }
    }

}
