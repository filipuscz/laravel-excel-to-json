<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CsvToJsonController extends Controller
{
    public function convert(Request $request)
    {
        // Validasi file CSV yang diunggah
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        // Ambil file yang diunggah
        $file = $request->file('csv_file');

        // Baca file CSV dengan pemisah ','
        $csvData = array_map(function ($line) {
            return str_getcsv(trim($line), ','); // Pisahkan menggunakan ','
        }, file($file->getRealPath()));

        // Ubah header menjadi huruf kecil
        $header = array_map('trim', array_map('strtolower', array_shift($csvData)));
        // Konversi data CSV ke array asosiatif
        $jsonData = [];
        foreach ($csvData as $row) {
            $combinedRow = array_combine($header, $row);
            // Konversi nilai menjadi tipe yang sesuai
            foreach ($combinedRow as $key => $value) {
                // Ubah string "NULL" menjadi null
                if (strcasecmp($value, 'NULL') === 0) {
                    $combinedRow[$key] = null;
                } elseif (is_numeric($value)) {
                    $combinedRow[$key] = (int) $value; // Ubah menjadi integer
                }
            }

            $jsonData[] = $combinedRow;
        }

        // Konversi array ke JSON
        return response()->json($jsonData, 200); // Kembalikan respon JSON
    }

}
