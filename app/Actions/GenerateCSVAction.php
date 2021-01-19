<?php
declare(strict_types=1);

namespace App\Actions;

use League\Csv\Writer as CsvWriter;

class GenerateCsvAction
{

    protected array $columns;

    protected array $rows;

    /**
     * @var  \League\Csv\Writer
     */
    private $csvWriter;

    public function __construct(array $columns, array $rows)
    {
        $this->columns = $columns;
        $this->rows = $rows;
        $this->csvWriter = CsvWriter::createFromString('');
    }

    public function execute(): CsvWriter
    {
        $this->addColumns();

        $this->addRows();

        return $this->csvWriter;

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
        $this->csvWriter->insertOne($data);
    }

}
