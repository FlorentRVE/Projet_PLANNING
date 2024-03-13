<?php

namespace App\Import;

use PhpOffice\PhpSpreadsheet\Reader\Csv;

abstract class Import implements ImportInterface
{
    protected ?array $dataFromFile = null;
    protected ?array $data = [];

    public function __construct(string $file, string $delimiter)
    {
        $reader = new Csv();
        $reader->setDelimiter($delimiter);
        $spreadsheet = $reader->load($file);
        $this->dataFromFile = $spreadsheet->getActiveSheet()->toArray();
    }

    public function getData(): array
    {
        return $this->data;
    }

}
