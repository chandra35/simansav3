<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Services\NisLokalService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NisLokalController extends Controller
{
    public function __construct(private readonly NisLokalService $service)
    {
    }

    public function index()
    {
        $this->authorize('manage-nis-lokal');

        try {
            $data = $this->service->dashboard();
            $generatorError = null;
        } catch (\Throwable $exception) {
            $data = [
                'setting' => \App\Models\AppSetting::getInstance(),
                'activeYear' => TahunPelajaran::query()->active()->first(),
                'generator' => null,
            ];
            $generatorError = $exception->getMessage();
        }

        return view('admin.nis-lokal.index', array_merge($data, compact('generatorError')));
    }

    public function generatorPreview()
    {
        $this->authorize('manage-nis-lokal');

        try {
            $year = TahunPelajaran::query()->active()->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $this->service->generatorPreview($year),
            ]);
        } catch (\Throwable $exception) {
            return $this->error($exception);
        }
    }

    public function confirmGenerator(Request $request)
    {
        $this->authorize('manage-nis-lokal');
        $validated = $request->validate(['token' => ['required', 'uuid']]);

        try {
            $result = $this->service->confirmGenerator($validated['token']);

            return response()->json([
                'success' => true,
                'message' => "{$result['generated']} NIS Lokal berhasil diterbitkan. Nomor absen juga telah disinkronkan.",
                'data' => $result,
            ]);
        } catch (\Throwable $exception) {
            return $this->error($exception);
        }
    }

    public function template()
    {
        $this->authorize('manage-nis-lokal');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('NIS Lokal');
        $sheet->fromArray(['nislokal', 'nisn', 'namalengkap'], null, 'A1');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF007BFF');
        $sheet->getStyle('A:C')->getNumberFormat()->setFormatCode('@');
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(38);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:C1');

        $path = tempnam(sys_get_temp_dir(), 'nis-lokal-');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, 'template-update-nis-lokal.xlsx')->deleteFileAfterSend(true);
    }

    public function importPreview(Request $request)
    {
        $this->authorize('manage-nis-lokal');
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => $this->service->importPreview($validated['file']),
            ]);
        } catch (\Throwable $exception) {
            return $this->error($exception);
        }
    }

    public function confirmImport(Request $request)
    {
        $this->authorize('manage-nis-lokal');
        $validated = $request->validate(['token' => ['required', 'uuid']]);

        try {
            $result = $this->service->confirmImport($validated['token']);

            return response()->json([
                'success' => true,
                'message' => "{$result['updated']} data NIS Lokal berhasil diperbarui.",
                'data' => $result,
            ]);
        } catch (\Throwable $exception) {
            return $this->error($exception);
        }
    }

    private function error(\Throwable $exception)
    {
        report($exception);

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 422);
    }
}
