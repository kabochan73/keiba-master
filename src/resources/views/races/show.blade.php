@extends('layouts.app')

@section('title', $race->race_name)

@section('content')

{{-- レース基本情報 --}}
<div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
    <div class="page-title" style="margin:0">{{ $race->race_name }}</div>
    @if ($race->grade)
        <span class="badge badge-{{ strtolower($race->grade) }}" style="font-size:13px; padding:3px 10px;">{{ $race->grade }}</span>
    @endif
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

    {{-- レース情報カード --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">レース情報</div>
        <table style="font-size:13px;">
            <tr>
                <td style="color:#888; width:80px; border:none; padding:4px 0;">日付</td>
                <td style="border:none; padding:4px 0;">{{ $race->race_date?->format('Y年m月d日') }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:4px 0;">開催</td>
                <td style="border:none; padding:4px 0;">{{ $race->venue }} {{ $race->race_number }}R</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:4px 0;">コース</td>
                <td style="border:none; padding:4px 0;">{{ $race->course_type }}{{ $race->distance }}m {{ $race->turn_direction ? '('.$race->turn_direction.'回り)' : '' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:4px 0;">天候/馬場</td>
                <td style="border:none; padding:4px 0;">{{ $race->weather ?? '-' }} / {{ $race->track_condition ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:4px 0;">出走頭数</td>
                <td style="border:none; padding:4px 0;">{{ $race->field_size }}頭</td>
            </tr>
        </table>
    </div>

    {{-- ペース情報カード --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">ペース</div>
        <table style="font-size:13px;">
            <tr>
                <td style="color:#888; width:80px; border:none; padding:4px 0;">前半3F</td>
                <td style="border:none; padding:4px 0;">{{ $race->pace_3f_front ?? '-' }}</td>
                <td style="color:#888; width:80px; border:none; padding:4px 0;">前半5F</td>
                <td style="border:none; padding:4px 0;">{{ $race->pace_5f_front ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:4px 0;">後半3F</td>
                <td style="border:none; padding:4px 0;">{{ $race->pace_3f_back ?? '-' }}</td>
                <td style="color:#888; border:none; padding:4px 0;">後半5F</td>
                <td style="border:none; padding:4px 0;">{{ $race->pace_5f_back ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:4px 0;">前後差</td>
                <td colspan="3" style="border:none; padding:4px 0;">
                    @if ($race->pace_balance !== null)
                        @php
                            $b = $race->pace_balance;
                            $label = $b >= 1.0 ? 'スロー' : ($b <= -1.0 ? 'ハイ' : 'ミドル');
                            $cls   = $b >= 1.0 ? 'slow' : ($b <= -1.0 ? 'high' : 'middle');
                        @endphp
                        {{ number_format($b, 2) }}
                        <span class="badge pace-{{ $cls }}" style="margin-left:6px;">{{ $label }}</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- ラップタイム --}}
@if ($race->lapTimes->count() > 0)
<div class="card" style="margin-bottom:20px; padding:16px;">
    <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">ラップタイム</div>
    <div style="display:flex; flex-wrap:wrap; gap:6px;">
        @foreach ($race->lapTimes as $lap)
            <div style="text-align:center; min-width:48px;">
                <div style="font-size:10px; color:#999;">{{ $lap->lap_number }}F</div>
                <div style="background:#e8f3fc; border-radius:4px; padding:4px 6px; font-size:13px; font-weight:600; color:#2a7bbf;">
                    {{ $lap->lap_time }}
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- 出走馬結果 --}}
@php
    // 人気上位3頭のID
    $top3popular = $race->entries
        ->whereNotNull('popularity')
        ->sortBy('popularity')
        ->take(3)
        ->pluck('id')
        ->toArray();

    // 上がり最速上位3頭のID => 順位
    $top3last3f = $race->entries
        ->whereNotNull('last_3f')
        ->where('last_3f', '>', 0)
        ->sortBy('last_3f')
        ->take(3)
        ->values()
        ->mapWithKeys(fn($e, $i) => [$e->id => $i + 1])
        ->toArray();

    // 枠番カラー（JRA標準）
    $wakuColors = [
        1 => ['bg' => '#f5f5f5', 'color' => '#333',    'border' => '#ccc'],
        2 => ['bg' => '#333333', 'color' => '#fff',    'border' => '#333'],
        3 => ['bg' => '#e03030', 'color' => '#fff',    'border' => '#c02020'],
        4 => ['bg' => '#2060c0', 'color' => '#fff',    'border' => '#1050a0'],
        5 => ['bg' => '#f0c000', 'color' => '#333',    'border' => '#d0a000'],
        6 => ['bg' => '#20a040', 'color' => '#fff',    'border' => '#108030'],
        7 => ['bg' => '#f07020', 'color' => '#fff',    'border' => '#d05010'],
        8 => ['bg' => '#e060a0', 'color' => '#fff',    'border' => '#c04080'],
    ];
@endphp

<div class="card">
    <table>
        <thead>
            <tr>
                <th>着順</th>
                <th>枠</th>
                <th>馬番</th>
                <th>馬名</th>
                <th>性齢</th>
                <th>斤量</th>
                <th>騎手</th>
                <th>タイム</th>
                <th>着差</th>
                <th>人気</th>
                <th>オッズ</th>
                <th>上がり</th>
                <th>コーナー</th>
                <th>厩舎</th>
                <th>馬体重</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($race->entries as $entry)
            @php
                $isTopPopular  = in_array($entry->id, $top3popular);
                $last3fRank    = $top3last3f[$entry->id] ?? null;
                $rowBg = '';
                $waku  = $wakuColors[$entry->post_position] ?? null;
            @endphp
            <tr style="{{ $rowBg }}">
                @php
                    $fp = $entry->finish_position;
                    $fpColor = match(true) {
                        $fp === 1 => '#cc0000', $fp === 2 => '#c07000',
                        $fp === 3 => '#1a7a30', default => '#333',
                    };
                @endphp
                <td><strong style="color:{{ $fpColor }};">{{ $fp ?? '---' }}</strong></td>
                <td>
                    @if ($waku)
                        <span style="
                            display:inline-block; width:24px; height:24px; line-height:24px;
                            text-align:center; border-radius:4px; font-size:12px; font-weight:700;
                            background:{{ $waku['bg'] }}; color:{{ $waku['color'] }};
                            border:1px solid {{ $waku['border'] }};
                        ">{{ $entry->post_position }}</span>
                    @else
                        {{ $entry->post_position ?? '-' }}
                    @endif
                </td>
                <td>{{ $entry->horse_number ?? '-' }}</td>
                <td>
                    @if ($entry->horse)
                        <a href="{{ route('horses.show', $entry->horse) }}" class="link" style="font-weight:600;">{{ $entry->horse->name }}</a>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $entry->horse?->sex ?? '' }}{{ $entry->age ?? '' }}</td>
                <td>{{ $entry->burden_weight ?? '-' }}</td>
                <td>
                    @if ($entry->jockey)
                        <a href="{{ route('jockeys.show', $entry->jockey) }}" class="link">{{ $entry->jockey->name }}</a>
                    @else -
                    @endif
                </td>
                <td>
                    @if ($entry->finish_time)
                        @php
                            $min = floor($entry->finish_time / 60);
                            $sec = $entry->finish_time - $min * 60;
                        @endphp
                        {{ $min }}:{{ number_format($sec, 1) }}
                    @else
                        ---
                    @endif
                </td>
                <td>{{ $entry->time_diff ?? '' }}</td>
                <td>
                    @if ($isTopPopular)
                        <strong style="color:#cc0000;">{{ $entry->popularity }}</strong>
                    @else
                        {{ $entry->popularity ?? '-' }}
                    @endif
                </td>
                <td>{{ $entry->odds ?? '-' }}</td>
                <td>
                    @if ($last3fRank === 1)
                        <strong style="color:#cc0000;">{{ $entry->last_3f }}</strong>
                    @elseif ($last3fRank === 2)
                        <strong style="color:#c07000;">{{ $entry->last_3f }}</strong>
                    @elseif ($last3fRank === 3)
                        <strong style="color:#7a30a0;">{{ $entry->last_3f }}</strong>
                    @else
                        {{ $entry->last_3f ?? '-' }}
                    @endif
                </td>
                <td style="font-size:12px; color:#666;">
                    {{ implode('-', array_filter([
                        $entry->corner_1, $entry->corner_2,
                        $entry->corner_3, $entry->corner_4
                    ], fn($v) => $v !== null)) }}
                </td>
                <td>{{ $entry->trainer?->name ?? '-' }}</td>
                <td>
                    {{ $entry->weight ?? '-' }}
                    @if ($entry->weight_change !== null)
                        ({{ $entry->weight_change >= 0 ? '+' : '' }}{{ $entry->weight_change }})
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- 過去比較セクション --}}
@if ($pastEditions->count() > 0)
<div style="margin-top:24px;">
    <div style="font-size:15px; font-weight:700; color:#2a7bbf; margin-bottom:12px;">
        過去の{{ $race->race_name }}と比較
    </div>

    {{-- タイムインデックス表示 --}}
    @if ($timeIndex !== null)
    <div style="margin-bottom:12px; font-size:13px;">
        <span style="color:#666;">過去{{ $pastEditions->count() }}年平均勝ちタイムとの比較：</span>
        @php
            $tiColor = $timeIndex > 0 ? '#1a7a30' : ($timeIndex < 0 ? '#cc0000' : '#888');
            $tiLabel = $timeIndex > 0 ? '速い' : ($timeIndex < 0 ? '遅い' : '同じ');
        @endphp
        <strong style="color:{{ $tiColor }}; font-size:15px;">
            {{ $timeIndex > 0 ? '+' : '' }}{{ number_format($timeIndex, 2) }}秒
        </strong>
        <span style="color:{{ $tiColor }};">（平均より{{ $tiLabel }}）</span>
        @if ($avgWinTime)
            @php $am = floor($avgWinTime/60); $as = $avgWinTime - $am*60; @endphp
            <span style="color:#999; font-size:12px; margin-left:8px;">過去平均: {{ $am }}:{{ number_format($as,1) }}</span>
        @endif
    </div>
    @endif

    {{-- ペース・勝ちタイム比較テーブル --}}
    <div class="card" style="margin-bottom:16px;">
        <table>
            <thead>
                <tr>
                    <th>年</th>
                    <th>日付</th>
                    <th>馬場</th>
                    <th>天候</th>
                    <th>勝ち馬</th>
                    <th>勝ちタイム</th>
                    <th>上がり平均</th>
                    <th>前半3F</th>
                    <th>後半3F</th>
                    <th>前後差</th>
                    <th>ペース</th>
                </tr>
            </thead>
            <tbody>
                {{-- 今年 --}}
                @php
                    $winner = $race->entries->where('finish_position', 1)->first();
                    $avgL3f = $calcAvgLast3f($race);
                @endphp
                <tr style="background:#f0f7ff;">
                    <td><strong>{{ $race->race_date?->format('Y') }}</strong> <span style="font-size:10px; color:#2a7bbf; background:#ddeeff; padding:1px 5px; border-radius:3px;">今年</span></td>
                    <td>{{ $race->race_date?->format('m/d') }}</td>
                    <td>{{ $race->track_condition ?? '-' }}</td>
                    <td>{{ $race->weather ?? '-' }}</td>
                    <td>{{ $winner?->horse?->name ?? '-' }}</td>
                    <td style="font-family:monospace;">
                        @if ($winner?->finish_time)
                            @php $m = floor($winner->finish_time/60); $s = $winner->finish_time - $m*60; @endphp
                            {{ $m }}:{{ number_format($s,1) }}
                        @else -
                        @endif
                    </td>
                    <td style="font-family:monospace;">{{ $avgL3f ? number_format($avgL3f, 2) : '-' }}</td>
                    <td>{{ $race->pace_3f_front ?? '-' }}</td>
                    <td>{{ $race->pace_3f_back ?? '-' }}</td>
                    <td style="font-family:monospace;">
                        @if ($race->pace_balance !== null)
                            {{ $race->pace_balance >= 0 ? '+' : '' }}{{ number_format($race->pace_balance, 2) }}
                        @else -
                        @endif
                    </td>
                    <td>
                        @if ($race->pace_balance !== null)
                            @php $b = $race->pace_balance; @endphp
                            <span class="badge pace-{{ $b >= 1.0 ? 'slow' : ($b <= -1.0 ? 'high' : 'middle') }}">
                                {{ $b >= 1.0 ? 'スロー' : ($b <= -1.0 ? 'ハイ' : 'ミドル') }}
                            </span>
                        @else -
                        @endif
                    </td>
                </tr>
                {{-- 過去年 --}}
                @foreach ($pastEditions as $past)
                @php
                    $pastWinner = $past->entries->where('finish_position', 1)->first();
                    $pastAvgL3f = $calcAvgLast3f($past);
                @endphp
                <tr>
                    <td>{{ $past->race_date?->format('Y') }}</td>
                    <td>{{ $past->race_date?->format('m/d') }}</td>
                    <td>{{ $past->track_condition ?? '-' }}</td>
                    <td>{{ $past->weather ?? '-' }}</td>
                    <td>
                        @if ($pastWinner?->horse)
                            <a href="{{ route('horses.show', $pastWinner->horse) }}" class="link">{{ $pastWinner->horse->name }}</a>
                        @else -
                        @endif
                    </td>
                    <td style="font-family:monospace;">
                        @if ($pastWinner?->finish_time)
                            @php $m = floor($pastWinner->finish_time/60); $s = $pastWinner->finish_time - $m*60; @endphp
                            {{ $m }}:{{ number_format($s,1) }}
                        @else -
                        @endif
                    </td>
                    <td style="font-family:monospace;">{{ $pastAvgL3f ? number_format($pastAvgL3f, 2) : '-' }}</td>
                    <td>{{ $past->pace_3f_front ?? '-' }}</td>
                    <td>{{ $past->pace_3f_back ?? '-' }}</td>
                    <td style="font-family:monospace;">
                        @if ($past->pace_balance !== null)
                            {{ $past->pace_balance >= 0 ? '+' : '' }}{{ number_format($past->pace_balance, 2) }}
                        @else -
                        @endif
                    </td>
                    <td>
                        @if ($past->pace_balance !== null)
                            @php $b = $past->pace_balance; @endphp
                            <span class="badge pace-{{ $b >= 1.0 ? 'slow' : ($b <= -1.0 ? 'high' : 'middle') }}">
                                {{ $b >= 1.0 ? 'スロー' : ($b <= -1.0 ? 'ハイ' : 'ミドル') }}
                            </span>
                        @else -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ラップタイム比較 --}}
    @php
        $allWithLaps = collect([$race])->concat($pastEditions)->filter(fn($r) => $r->lapTimes->count() > 0);
    @endphp
    @if ($allWithLaps->count() > 0)
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:12px;">ラップタイム比較</div>
        <div style="overflow-x:auto;">
            <table style="font-size:12px; min-width:600px;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:4px 10px 4px 0; border-bottom:1px solid #eee;">年</th>
                        @for ($i = 1; $i <= $allWithLaps->max(fn($r) => $r->lapTimes->count()); $i++)
                            <th style="padding:4px 8px; border-bottom:1px solid #eee; text-align:center; color:#888;">{{ $i }}F</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allWithLaps as $r)
                    @php $isCurrentRace = $r->id === $race->id; @endphp
                    <tr style="{{ $isCurrentRace ? 'background:#f0f7ff; font-weight:700;' : '' }}">
                        <td style="padding:4px 10px 4px 0; border:none; white-space:nowrap;">
                            {{ $r->race_date?->format('Y') }}
                            @if ($isCurrentRace)<span style="font-size:10px; color:#2a7bbf;">★</span>@endif
                        </td>
                        @php $lapMap = $r->lapTimes->keyBy('lap_number'); @endphp
                        @for ($i = 1; $i <= $allWithLaps->max(fn($r2) => $r2->lapTimes->count()); $i++)
                            @php $lap = $lapMap->get($i); @endphp
                            <td style="padding:4px 8px; border:none; text-align:center; font-family:monospace;">
                                {{ $lap ? number_format($lap->lap_time, 1) : '-' }}
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endif

<div style="margin-top:16px; display:flex; justify-content:space-between; align-items:center;">
    <a href="{{ route('races.index') }}" class="link">← レース一覧に戻る</a>
    <a href="{{ route('races.corrections.edit', $race) }}" style="
        background:#2a7bbf; color:#fff; text-decoration:none;
        padding:8px 20px; border-radius:6px; font-size:13px; font-weight:600;
    ">補正入力</a>
</div>

@endsection
