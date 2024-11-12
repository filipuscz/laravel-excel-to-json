<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;

class ExcelToJsonSingleController extends Controller
{
    public function convert(Request $request)
    {
        // Validasi file Excel yang diunggah
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        // Mendapatkan file yang diunggah
        $file = $request->file('excel_file');

        // Mendapatkan nama file asli dan mengganti ekstensi menjadi .json
        $fileNameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $jsonFileName = $fileNameWithoutExtension . '.json';

        // Membaca file Excel menjadi array
        $data = Excel::toArray(new class implements ToArray {
            public function array(array $array)
            {
                return $array;
            }
        }, $file);

        // Meratakan array (jika perlu)
        $data = array_merge(...$data);

        // Menyiapkan data yang diformat
        $formattedData = [];
        if (!empty($data)) {
            // Mengambil header dari baris pertama
            $headers = array_map('trim', array_shift($data));

            foreach ($data as $row) {
                $rowData = [];
                foreach ($row as $index => $value) {
                    // Mengonversi string "NULL" menjadi null
                    if (strcasecmp($value, 'NULL') === 0) {
                        $value = null;
                    }
                    if ($value === '-') {
                        $value = null;
                    }
                    if ($value === '') {
                        $value = null;
                    }
                    // Menyusun data baris dengan header yang sesuai
                    $rowData[$headers[$index]] = $value;
                }
                $formattedData[] = $rowData; // Menambahkan baris ke data yang diformat
            }
        }

        // Mengonversi data menjadi JSON
        $jsonContent = json_encode($formattedData, JSON_PRETTY_PRINT);

        // Menyimpan file JSON sementara di server
        $tempPath = storage_path('app/public/' . $jsonFileName);
        file_put_contents($tempPath, $jsonContent);

        // Mengunduh file JSON dengan nama yang sama seperti file Excel
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
