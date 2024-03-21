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
                    $dataRow[] = $rotation;
                }

            }

        }

        $this->miseForme($dataRow);
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

    private function miseForme(array $data): void
    {
        foreach ($data as $crw) {

            $newRotation = [];
            $newRotation['service'] = $crw['service'];

            if ($crw['lieuPriseService'] == 'DEPOT') {
                $newRotation['heurePriseService'] = $crw['heurePriseService'];
            }

            if ($crw['lieuFinService'] == 'DEPOT') {
                $newRotation['heureFinService'] = $crw['heureFinService'];
            }

            if (isset ($newRotation['heurePriseService'])) {

                if (intval($newRotation['heurePriseService']) < 11) {

                    $newRotation['matinSoir'] = 1;
                } else {

                    $newRotation['matinSoir'] = 2;
                }
            }

            if (isset ($newRotation['heurePriseService']) && isset ($newRotation['heureFinService']) && !in_array($newRotation, $this->data)) {
                $this->data[$newRotation['service'] . '(' . $newRotation['matinSoir'] . ')'] = $newRotation;
            }
        }
        
    }
}
