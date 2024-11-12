<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Facades\Storage;

class ExcelToJsonController extends Controller
{
    public function convert(Request $request)
    {
        ini_set('memory_limit', '1G'); // Menambah batas memori menjadi 512MB
        // Validasi file Excel yang diunggah
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        // Mendapatkan file yang diunggah
        $file = $request->file('excel_file');

        // Mendapatkan nama file asli dan mengganti ekstensi menjadi .json
        $fileNameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

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
                    elseif ($value === '0') {
                        $value = null;
                    }
                    elseif ($value === '-') {
                        $value = null;
                    }
                    elseif ($value === '') {
                        $value = null;
                    }
                    elseif ($value === 'N') {
                        $value = null;
                    }
                    elseif ($value === '#N/A') {
                        $value = null;
                    }
                    // Menyusun data baris dengan header yang sesuai
                    $rowData[$headers[$index]] = $value;
                }
                $formattedData[] = $rowData; // Menambahkan baris ke data yang diformat
            }
        }

        // Pisahkan data ke dalam batch 1000 baris
        $batchSize = 1000;
        $batches = array_chunk($formattedData, $batchSize);

        // Menyimpan setiap batch sebagai file JSON terpisah
        $batchFilePaths = [];
        foreach ($batches as $index => $batch) {
            // Mengonversi data batch menjadi JSON
            $jsonContent = json_encode($batch, JSON_PRETTY_PRINT);

            // Menyimpan file JSON sementara di server
            $batchFileName = $fileNameWithoutExtension . "_batch_" . ($index + 1) . ".json";
            $tempPath = storage_path('app/public/' . $batchFileName);
            file_put_contents($tempPath, $jsonContent);

            // Simpan path file batch untuk diunduh
            $batchFilePaths[] = $tempPath;
        }

        // Membuat file ZIP untuk mengunduh semua batch sekaligus
        $zipFileName = $fileNameWithoutExtension . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);
        $zip = new \ZipArchive();

        if ($zip->open($zipFilePath, \ZipArchive::CREATE) === TRUE) {
            // Menambahkan setiap batch ke dalam file ZIP
            foreach ($batchFilePaths as $filePath) {
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();
        }

        // Menghapus file JSON sementara setelah membuat ZIP
        foreach ($batchFilePaths as $filePath) {
            unlink($filePath);
        }

        // Mengunduh file ZIP
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}