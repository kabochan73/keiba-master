<?php

namespace App\Console\Commands;

use App\Models\Horse;
use App\Services\Scraping\HorseScraper;
use App\Services\Scraping\RaceSaveService;
use Illuminate\Console\Command;

class ScrapeHorse extends Command
{
    protected $signature = 'scrape:horse
        {--horse-id= : netkeibaの馬ID（例: 2019106535）}
        {--all       : DB内の全馬の情報を更新する}';

    protected $description = '馬の詳細情報（生産者・父母・厩舎等）をスクレイピング';

    public function handle(
        HorseScraper $horseScraper,
        RaceSaveService $saveService
    ): int {
        $horseId = $this->option('horse-id');
        $all     = $this->option('all');

        if (!$horseId && !$all) {
            $this->error('--horse-id または --all を指定してください');
            return Command::FAILURE;
        }

        if ($horseId) {
            return $this->scrapeOne($horseId, $horseScraper, $saveService);
        }

        return $this->scrapeAll($horseScraper, $saveService);
    }

    private function scrapeOne(string $horseId, HorseScraper $horseScraper, RaceSaveService $saveService): int
    {
        $this->info("馬 {$horseId} の情報を取得します...");

        $data = $horseScraper->scrape($horseId);
        if (!$data) {
            $this->error('取得に失敗しました');
            return Command::FAILURE;
        }

        $saveService->saveHorseDetail($data);
        $this->info("保存完了: {$data['horse']['name']}");

        return Command::SUCCESS;
    }

    private function scrapeAll(HorseScraper $horseScraper, RaceSaveService $saveService): int
    {
        $horses = Horse::all();
        $total  = $horses->count();

        $this->info("DB内の全馬 {$total} 頭の情報を更新します...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $failed = 0;
        foreach ($horses as $horse) {
            $data = $horseScraper->scrape($horse->horse_id);
            if ($data) {
                $saveService->saveHorseDetail($data);
            } else {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("完了。失敗: {$failed} 件");

        return Command::SUCCESS;
    }
}
