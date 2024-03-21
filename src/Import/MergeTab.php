<?php

namespace App\Import;


class MergeTab
{
    private ?array $dataAT = [];
    private ?array $dataCRW = [];
    private ?array $mergedData = [];

    public function __construct(
        array $dateAT,
        array $dateCRW,
    ) {
        $this->dataAT = $dateAT;
        $this->dataCRW = $dateCRW;
    }

    public function merge(): array
    {

        foreach ($this->dataAT as $at) {
            foreach ($this->dataCRW as $key => $crw) {

                if ($at['service'] == $crw['service']) {

                    $nomService = $at['service'];
                    $matricule = $at['matricule'];
                    $nomAgent = $at['nom'];
                    $priseService = $crw['heurePriseService'];
                    $finService = $crw['heureFinService'];

                    $this->mergedData[$key] = [
                        "service" => $nomService,
                        "matricule" => $matricule,
                        "agent" => $nomAgent,
                        "priseService" => $priseService,
                        "finService" => $finService
                    ];
                }
            }
        }

        return $this->mergedData;
    }

}
