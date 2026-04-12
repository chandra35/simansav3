<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NilaiCbtExport implements WithMultipleSheets
{
    protected $byTingkat;
    protected $mapelByTingkat;
    protected $quizTingkat;
    protected $smartq;

    public function __construct($byTingkat, $mapelByTingkat, $quizTingkat, $smartq)
    {
        $this->byTingkat = $byTingkat;
        $this->mapelByTingkat = $mapelByTingkat;
        $this->quizTingkat = $quizTingkat;
        $this->smartq = $smartq;
    }

    public function sheets(): array
    {
        $sheets = [];

        // One sheet per tingkat
        foreach ($this->byTingkat as $tkt => $tktRows) {
            $tktMapel = collect($this->mapelByTingkat[$tkt] ?? []);
            $sheets[] = new NilaiCbtTingkatSheet($tkt, $tktRows, $tktMapel, $this->smartq);
        }

        // Ringkasan sheet (all tingkat summary)
        $sheets[] = new NilaiCbtRingkasanSheet($this->byTingkat, $this->mapelByTingkat, $this->smartq);

        return $sheets;
    }
}
