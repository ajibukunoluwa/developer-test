<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateCsvAction;
use App\Http\Requests\ConvertCsvRequest;

class CsvExportController
{
    /**
     * Converts the user input into a CSV file and streams the file back to the user
     */
    public function convert(ConvertCsvRequest $request)
    {
        $request->validated();

        return response()->streamDownload(function () use ($request) {
            $generateCsv = (new GenerateCsvAction($request->columns, $request->rows))
                            ->execute();

            echo $generateCsv->getContent();
        }, config('csv.default_filename'));

    }
}
