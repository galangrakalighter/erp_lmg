<?php

namespace App\Support;

final class MarketResearchCrudDefinitions
{
    public const MAP = [
        'four_p' => ['table' => 'analize_4p', 'fields' => ['nama_analisis','type','platform'], 'required' => ['nama_analisis','type','platform']],
        'swot' => ['table' => 'analize_swot', 'fields' => ['strenght','weakness','oportunity','threat','platform'], 'required' => []],
        'segmentation' => ['table' => 'segmentasi', 'fields' => ['usia','gender','negara','wilayah','gaya_hidup','status_sosial','minat','penggunaan','loyalitas','manfaat','platform'], 'required' => []],
        'target_audience' => ['table' => 'target_audience', 'fields' => ['indikator','usia','gender','negara','wilayah','pekerjaan','gaya_hidup','status_sosial','penggunaan','manfaat','platform'], 'required' => []],
        'positioning' => ['table' => 'positioning', 'fields' => ['indikator','deskripsi','platform'], 'required' => []],
    ];
    public static function get(string $entity): array { abort_unless(isset(self::MAP[$entity]), 404, 'Entitas market research tidak dikenal.'); return self::MAP[$entity]; }
}
