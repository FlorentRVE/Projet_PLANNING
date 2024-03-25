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
        return [
            "service" => trim($row[1]),
            "lieuPriseService" => trim($row[6]),
            "heurePriseService" => trim($row[7]),
            "lieuFinService" =>  trim($row[12]),
            "heureFinService" => trim($row[13])
        ];
    }

    private function miseForme(array $data): void
    {
        foreach ($data as $crw) {

            $newRotation = ['service' => $crw['service']];

            if ($crw['lieuPriseService'] == 'DEPOT') {
                $newRotation['heurePriseService'] = $crw['heurePriseService'];
            }

            if ($crw['lieuFinService'] == 'DEPOT') {
                $newRotation['heureFinService'] = $crw['heureFinService'];
            }

            if (isset ($newRotation['heurePriseService'])) {
                $heurePriseService = intval($newRotation['heurePriseService']);
                $newRotation['matinSoir'] = $heurePriseService < 11 ? 1 : 2;
            }

            if (isset ($newRotation['heurePriseService']) && isset ($newRotation['heureFinService']) && !in_array($newRotation, $this->data)) {
                $this->data[$newRotation['service'] . '(' . $newRotation['matinSoir'] . ')'] = $newRotation;
            }
        }
        
    }
}
