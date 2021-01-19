<?php
declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerateCSVAction {

    const DELIMITER = ",";

    private $file;

    protected array $columns;

    protected array $rows;

    public function __construct(array $columns, array $rows)
    {
        $this->columns = $columns;
        $this->rows = $rows;
    }

    public function execute()
    {
        $this->openFile();

        $this->addColumns();

        $this->addRows();

        $this->closeFile();

        return $this->file;
    }

    private function openFile(): void
    {
        $this->file = fopen('php://output', 'w');
    }

    private function addColumns(): void
    {
        $columnTitles = [];

        foreach ($this->columns as $column) {
            $columnTitles[] = $column['title'];
        }

        $this->addLine($columnTitles);
    }

    private function addRows(): void
    {
        foreach ($this->rows as $row) {
            $newLine = [];

            foreach ($this->columns as $column) {
                $newLine[] = $row[$column['key']];
            }

            $this->addLine($newLine);
        }
    }

    private function addLine(array $data): void
    {
        fputcsv($this->file, $data, self::DELIMITER);
    }

    private function closeFile()
    {
        fclose($this->file);
    }

}
