<?php

namespace App\Services\Scraping;

use Illuminate\Support\Facades\Log;

class HorseScraper
{
    public function __construct(private NetkeibaClient $client) {}

    /**
     * 馬の基本情報と過去成績を取得
     */
    public function scrape(string $horseId): ?array
    {
        $url = "https://db.netkeiba.com/horse/{$horseId}";
        $crawler = $this->client->fetch($url);

        if (!$crawler) {
            Log::error("馬情報取得失敗: {$horseId}");
            return null;
        }

        return [
            'horse'    => $this->parseHorseInfo($crawler, $horseId),
            'breeder'  => $this->parseBreeder($crawler),
            'trainer'  => $this->parseTrainer($crawler),
        ];
    }

    /**
     * 馬の基本情報をパース
     */
    private function parseHorseInfo($crawler, string $horseId): array
    {
        $info = [
            'horse_id'   => $horseId,
            'name'       => '',
            'sex'        => null,
            'birth_date' => null,
            'coat_color' => null,
            'father'     => null,
            'mother'     => null,
            'mother_father' => null,
        ];

        try {
            // 馬名
            $info['name'] = trim($crawler->filter('.horse_title h1')->text(''));

            // プロフィールテーブル
            $crawler->filter('.db_prof_table tr')->each(function ($row) use (&$info) {
                $th = trim($row->filter('th')->text(''));
                $td = trim($row->filter('td')->text(''));

                switch ($th) {
                    case '生年月日':
                        // 例: "2019年4月10日"
                        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/', $td, $m)) {
                            $info['birth_date'] = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                        }
                        break;
                    case '性別':
                    case '性齢':
                        $info['sex'] = mb_substr($td, 0, 1);
                        break;
                    case '毛色':
                        $info['coat_color'] = $td;
                        break;
                    case '父':
                        $info['father'] = $td;
                        break;
                    case '母':
                        $info['mother'] = $td;
                        break;
                    case '母父':
                        $info['mother_father'] = $td;
                        break;
                }
            });

        } catch (\Exception $e) {
            Log::error("馬情報パース失敗: {$horseId}", ['error' => $e->getMessage()]);
        }

        return $info;
    }

    /**
     * 生産者情報をパース
     */
    private function parseBreeder($crawler): ?array
    {
        try {
            $link = $crawler->filter('a[href*="/breeder/"]')->first();
            if ($link->count() > 0) {
                preg_match('/\/breeder\/(\w+)/', $link->attr('href') ?? '', $m);
                return [
                    'breeder_id' => $m[1] ?? null,
                    'name'       => trim($link->text('')),
                ];
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * 調教師情報をパース
     */
    private function parseTrainer($crawler): ?array
    {
        try {
            $link = $crawler->filter('a[href*="/trainer/"]')->first();
            if ($link->count() > 0) {
                preg_match('/\/trainer\/result\/recent\/(\w+)/', $link->attr('href') ?? '', $m);
                return [
                    'trainer_id' => $m[1] ?? null,
                    'name'       => trim($link->text('')),
                ];
            }
        } catch (\Exception $e) {}

        return null;
    }
}
