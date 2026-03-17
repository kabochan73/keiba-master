<?php

namespace App\Services\Scraping;

use App\Models\Breeder;
use App\Models\Horse;
use App\Models\Jockey;
use App\Models\LapTime;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\Trainer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RaceSaveService
{
    /**
     * スクレイピングデータをDBに保存
     */
    public function save(array $data): ?Race
    {
        return DB::transaction(function () use ($data) {
            $race    = $this->saveRace($data['race']);
            $lapData = $data['laps'] ?? [];

            $this->saveLapTimes($race, $lapData);
            $this->updateRacePaceData($race, $lapData);

            foreach ($data['entries'] as $entryData) {
                $this->saveEntry($race, $entryData);
            }

            return $race;
        });
    }

    /**
     * レース基本情報を保存（既存なら更新）
     */
    private function saveRace(array $data): Race
    {
        return Race::updateOrCreate(
            ['race_id' => $data['race_id']],
            $data
        );
    }

    /**
     * ラップタイムを保存
     */
    private function saveLapTimes(Race $race, array $laps): void
    {
        foreach ($laps as $lap) {
            LapTime::updateOrCreate(
                ['race_id' => $race->id, 'lap_number' => $lap['lap_number']],
                ['lap_time' => $lap['lap_time']]
            );
        }
    }

    /**
     * ラップからペースデータを計算してRaceに保存
     */
    private function updateRacePaceData(Race $race, array $laps): void
    {
        if (empty($laps)) {
            return;
        }

        $lapTimes = array_column($laps, 'lap_time');
        $totalLaps = count($lapTimes);

        if ($totalLaps < 3) {
            return;
        }

        // 前半3F（最初の3ラップ合計）
        $pace3fFront = array_sum(array_slice($lapTimes, 0, 3));

        // 前半5F（最初の5ラップ合計、距離が短い場合はスキップ）
        $pace5fFront = $totalLaps >= 5 ? array_sum(array_slice($lapTimes, 0, 5)) : null;

        // 後半3F（最後の3ラップ合計）
        $pace3fBack = array_sum(array_slice($lapTimes, -3, 3));

        // 後半5F
        $pace5fBack = $totalLaps >= 5 ? array_sum(array_slice($lapTimes, -5, 5)) : null;

        // 前後バランス
        $paceBalance = round($pace3fFront - $pace3fBack, 2);

        $race->update([
            'pace_3f_front' => round($pace3fFront, 1),
            'pace_5f_front' => $pace5fFront ? round($pace5fFront, 1) : null,
            'pace_3f_back'  => round($pace3fBack, 1),
            'pace_5f_back'  => $pace5fBack ? round($pace5fBack, 1) : null,
            'pace_balance'  => $paceBalance,
        ]);
    }

    /**
     * 出走馬エントリーを保存
     */
    private function saveEntry(Race $race, array $data): void
    {
        // 調教師
        $trainerId = null;
        if (!empty($data['trainer_id'])) {
            $trainer = Trainer::firstOrCreate(
                ['trainer_id' => $data['trainer_id']],
                ['name' => $data['trainer_name'] ?? '']
            );
            $trainerId = $trainer->id;
        }

        // 騎手
        $jockeyId = null;
        if (!empty($data['jockey_id'])) {
            $jockey = Jockey::firstOrCreate(
                ['jockey_id' => $data['jockey_id']],
                ['name' => $data['jockey_name'] ?? '']
            );
            $jockeyId = $jockey->id;
        }

        // 馬（基本情報のみ。詳細は scrape:horse で取得）
        if (empty($data['horse_id'])) {
            return;
        }
        $horse = Horse::firstOrCreate(
            ['horse_id' => $data['horse_id']],
            [
                'name'       => $data['horse_name'] ?? '',
                'sex'        => $data['sex'] ?? null,
                'trainer_id' => $trainerId,
            ]
        );

        // 出走馬エントリー
        RaceEntry::updateOrCreate(
            ['race_id' => $race->id, 'horse_id' => $horse->id],
            [
                'jockey_id'       => $jockeyId,
                'trainer_id'      => $trainerId,
                'post_position'   => $data['post_position'],
                'horse_number'    => $data['horse_number'],
                'finish_position' => $data['finish_position'],
                'finish_time'     => $data['finish_time'],
                'time_diff'       => ($data['time_diff'] ?? '') !== '' ? $data['time_diff'] : null,
                'last_3f'         => $data['last_3f'] ?: null,
                'corner_1'        => $data['corner_1'],
                'corner_2'        => $data['corner_2'],
                'corner_3'        => $data['corner_3'],
                'corner_4'        => $data['corner_4'],
                'weight'          => $data['weight'],
                'weight_change'   => $data['weight_change'],
                'age'             => $data['age'],
                'burden_weight'   => $data['burden_weight'] ?: null,
                'odds'            => $data['odds'] ?: null,
                'popularity'      => $data['popularity'] ?: null,
            ]
        );
    }

    /**
     * 馬の詳細情報を保存
     */
    public function saveHorseDetail(array $data): void
    {
        $horseData    = $data['horse'];
        $breederData  = $data['breeder'] ?? null;
        $trainerData  = $data['trainer'] ?? null;

        $breederId = null;
        if ($breederData && !empty($breederData['breeder_id'])) {
            $breeder   = Breeder::firstOrCreate(
                ['breeder_id' => $breederData['breeder_id']],
                ['name' => $breederData['name'] ?? '']
            );
            $breederId = $breeder->id;
        }

        $trainerId = null;
        if ($trainerData && !empty($trainerData['trainer_id'])) {
            $trainer   = Trainer::firstOrCreate(
                ['trainer_id' => $trainerData['trainer_id']],
                ['name' => $trainerData['name'] ?? '']
            );
            $trainerId = $trainer->id;
        }

        Horse::updateOrCreate(
            ['horse_id' => $horseData['horse_id']],
            [
                'name'          => $horseData['name'],
                'sex'           => $horseData['sex'],
                'birth_date'    => $horseData['birth_date'],
                'coat_color'    => $horseData['coat_color'],
                'father'        => $horseData['father'],
                'mother'        => $horseData['mother'],
                'mother_father' => $horseData['mother_father'],
                'trainer_id'    => $trainerId,
                'breeder_id'    => $breederId,
            ]
        );
    }
}
