<?php

namespace App\Exports;

use App\Services\TrialBalanceService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Carbon;

class TrialBalanceExport implements FromView, ShouldAutoSize
{
    protected $periode;
    protected $trialBalanceService;

    public function __construct($periode, TrialBalanceService $trialBalanceService)
    {
        $this->periode = $periode;
        $this->trialBalanceService = $trialBalanceService;
    }

    public function view(): View
    {
        $data = $this->trialBalanceService->getTrialBalance($this->periode);
        $periodeFormatted = Carbon::parse($this->periode . '-01')
            ->locale('id')
            ->settings(['formatFunction' => 'translatedFormat'])
            ->format('Y - F');
        return view('finance.excel_neraca', [
            'periode'          => $periodeFormatted,
            'reportData'       => $data['report_data'],
            'grandTotalDebit'  => $data['grand_total_debit'],
            'grandTotalKredit' => $data['grand_total_kredit']
        ]);
    }
}