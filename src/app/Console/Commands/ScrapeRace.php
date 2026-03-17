<?php

namespace App\Console\Commands;

use App\Services\Scraping\NetkeibaClient;
use App\Services\Scraping\RaceDetailScraper;
use App\Services\Scraping\RaceSaveService;
use Illuminate\Console\Command;

class ScrapeRace extends Command
{
    protected $signature = 'scrape:race
        {--race-id= : netkeibaのレースID（例: 202309050811）}
        {--force    : 既存データを上書きする}';

    protected $description = '特定のレースをrace_id指定でスクレイピング';

    public function handle(
        RaceDetailScraper $detailScraper,
        RaceSaveService $saveService
    ): int {
        $raceId = $this->option('race-id');

        if (!$raceId) {
            $this->error('--race-id を指定してください');
            return Command::FAILURE;
        }

        $this->info("レース {$raceId} を取得します...");

        $data = $detailScraper->scrape($raceId);

        if (!$data) {
            $this->error('レースデータの取得に失敗しました');
            return Command::FAILURE;
        }

        $race = $saveService->save($data);

        $this->info("保存完了: {$race->race_name} ({$race->race_date})");
        $this->info("出走馬: {$race->field_size} 頭");

        return Command::SUCCESS;
    }
}
