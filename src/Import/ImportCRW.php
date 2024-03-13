<?php

namespace App\Import;

class ImportCRW extends Import
{
    public function import(): void
    {
        foreach ($this->dataFromFile as $row) {

            if(trim($row[6]) == "DEPOT" || trim($row[8]) == "DEPOT" || trim($row[10]) == "DEPOT" || trim($row[12]) == "DEPOT") {

                $rotation = $this->importPriseFinService($row);

                if (!in_array($rotation, $this->data)) {
                    $this->data[] = $rotation;
                }

            }

        }
    }

    private function importPriseFinService(array $row): array
    {
        $rotation = [
            "service" => trim($row[1]),
            "lieuPriseService" => trim($row[6]),
            "heurePriseService" => trim($row[7]),
            "lieuFinService" =>  trim($row[12]),
            "heureFinService" => trim($row[13])
        ];

        return $rotation;

    }
}
