<?php

namespace App\Console\Commands;

use App\Models\Race;
use App\Services\Scraping\RaceDetailScraper;
use App\Services\Scraping\RaceListScraper;
use App\Services\Scraping\RaceSaveService;
use Illuminate\Console\Command;

class ScrapeRaces extends Command
{
    protected $signature = 'scrape:races
        {--year= : 取得する年（例: 2024）}
        {--date= : 取得する日付（例: 20240407）}';

    protected $description = '指定年または日付の芝・牝馬混合重賞レースをスクレイピング';

    public function handle(
        RaceListScraper $listScraper,
        RaceDetailScraper $detailScraper,
        RaceSaveService $saveService
    ): int {
        $year = $this->option('year');
        $date = $this->option('date');

        if (!$year && !$date) {
            $this->error('--year または --date を指定してください');
            return Command::FAILURE;
        }

        // race_idリストを取得
        if ($date) {
            $this->info("日付 {$date} のレースを取得します...");
            $raceIds = $listScraper->getRaceIdsByDate($date);
        } else {
            $this->info("{$year}年のレースを取得します...");
            $raceIds = $listScraper->getRaceIdsByYear((int) $year);
        }

        $total        = count($raceIds);
        $skipped      = 0;
        $saved        = 0;
        $failed       = 0;
        $femaleOnly   = 0;

        $this->info("対象レース数: {$total} 件");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($raceIds as $raceId) {
            // 既存チェック（スキップ）
            if (Race::where('race_id', $raceId)->exists()) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $data = $detailScraper->scrape($raceId);

            if ($data === false) {
                $femaleOnly++;
                $bar->advance();
                continue;
            }

            if ($data === null) {
                $failed++;
                $bar->advance();
                continue;
            }

            $saveService->save($data);
            $saved++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['項目', '件数'],
            [
                ['対象レース',           $total],
                ['新規保存',             $saved],
                ['スキップ（既存）',     $skipped],
                ['牝馬限定スキップ',     $femaleOnly],
                ['失敗',                 $failed],
            ]
        );

        return Command::SUCCESS;
    }
}
