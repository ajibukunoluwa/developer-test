<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateCSVAction;
use App\Http\Requests\ConvertCsvRequest;

class CsvExport extends Controller {

    const FILE_NAME = "f3groups_csv_generated_file.csv";

    /**
     * Converts the user input into a CSV file and streams the file back to the user
     */
    public function convert(ConvertCsvRequest $request)
    {
        $request->validated();

        return response()->streamDownload(function () use ($request) {
            return (new GenerateCSVAction($request->columns, $request->rows))->execute();
        }, self::FILE_NAME);

    }
}
