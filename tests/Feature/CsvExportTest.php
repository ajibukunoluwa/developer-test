<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Response;
use League\Csv\Reader as CsvReader;

class CsvExportTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUserCanExportCsv()
    {
        $columns = [
            [
              "key" => "first_name",
              "title" => "First Name"
            ],
            [
              "key" => "last_name",
              "title" => "Last Name"
            ],
            [
              "key" => "email_address",
              "title" => "Email Address"
            ]
        ];

        $rows = [
            [
              "first_name" => "John",
              "last_name" => "Doe",
              "email_address" => "john.doe@example.com"
            ],
            [
              "first_name" => "Ajimoti",
              "last_name" => "John",
              "email_address" => "john.ajimoti@google.com"
            ]
        ];

        $response = $this->json('PATCH', '/api/csv-export', [
            "rows"  => $rows,
            "columns"  => $columns,
        ]);

        $csvReader = CsvReader::createFromString($response->streamedContent());

        foreach ($csvReader->getRecords() as $key => $record) {
            if ($key === 0) {
                // Assert that the titles are present in
                // the first line of the records
                $columnTitles = array_column($columns, 'title');
                $this->assertEquals($columnTitles, $record);
            } else {
                // Assert that the values in the $rows array
                // are present in each line of the csv
                $this->assertEquals(array_values($rows[--$key]), $record);
            }

        }

        return $response->assertStatus(200)
                    ->assertHeader('Content-Disposition', 'attachment; filename=' . config('csv.default_filename'));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testThrowValidationErrorWhenColumnKeyIsMissingInRows()
    {
        $columns = [
            [
              "key" => "first_name",
              "title" => "First Name"
            ],
            [
              "key" => "last_name",
              "title" => "Last Name"
            ],
            [
              "key" => "email_address",
              "title" => "Email Address"
            ]
        ];

        $rows = [
            [
              "first_nam" => "John", // Intentional send a wrong key
              "last_name" => "Doe",
              "email_address" => "john.doe@example.com"
            ],
            [
              "first_name" => "John",
              "last_name" => "Doe",
              "email_address" => "john.doe@example.com"
            ]
        ];

        $response = $this->json('PATCH', '/api/csv-export', [
            "rows"  => $rows,
            "columns"  => $columns,
        ]);

        return $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
