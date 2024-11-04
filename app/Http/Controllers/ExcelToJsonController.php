<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\ToArray;

class ExcelToJsonController extends Controller
{
    public function convert(Request $request)
    {
        // Validate the uploaded Excel file
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        // Get the uploaded file
        $file = $request->file('excel_file');

        // Read the Excel file into an array
        $data = Excel::toArray(new class implements ToArray {
            public function array(array $array)
            {
                return $array;
            }
        }, $file);

        // Flatten the array (if necessary)
        $data = array_merge(...$data);

        // Extract headers and prepare the formatted data
        $formattedData = [];
        if (!empty($data)) {
            // Get headers from the first row
            $headers = array_map('trim', array_shift($data));

            foreach ($data as $row) {
                $rowData = [];
                foreach ($row as $index => $value) {
                    // Convert "NULL" string to null
                    if (strcasecmp($value, 'NULL') === 0) {
                        $value = null;
                    }
                    // Convert numeric strings to integers (optional)
                    if (is_numeric($value)) {
                        $value = (int) $value; // Change this to (float) if you want to handle decimals
                    }
                    // Associate header with value
                    $rowData[$headers[$index]] = $value;
                }
                $formattedData[] = $rowData; // Add the row to the formatted data
            }
        }
        // Return the JSON response
        return response()->json($formattedData, 200);
    }
}
