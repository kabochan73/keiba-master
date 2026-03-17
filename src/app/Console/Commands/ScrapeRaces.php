<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScrapeRaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:races {--date= : 取得する日付 (YYYYMMDD形式)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '競馬レース情報をスクレイピングして取得します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ?? now()->format('Ymd');

        $this->info("レース情報のスクレイピングを開始します: {$date}");

        // TODO: スクレイピング処理を実装する
        // 例: netkeiba.comなどからレース情報を取得

        $this->info('スクレイピングが完了しました。');

        return Command::SUCCESS;
    }
}
