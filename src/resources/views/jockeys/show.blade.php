@extends('layouts.app')

@section('title', $jockey->name . ' 騎手')

@push('styles')
<style>
    .stat-table th { font-size:11px; color:#888; padding:4px 10px; text-align:center; border-bottom:2px solid #e8f0f8; }
    .stat-table td { padding:5px 10px; text-align:center; border:none; font-size:13px; }
    .stat-table tr:hover { background:#f5faff; }
    .stat-label { text-align:left !important; font-weight:600; color:#333; }
    .win-rate { color:#cc0000; font-weight:700; }
    .place-rate { color:#1a7a30; }
    .record { font-family:monospace; }
</style>
@endpush

@section('content')

{{-- ヘッダー --}}
<div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
    <div>
        <div class="page-title" style="margin-bottom:2px;">{{ $jockey->name }} 騎手</div>
        <div style="font-size:13px; color:#888;">{{ $jockey->affiliation ?? '' }}</div>
    </div>
    {{-- 全体成績バッジ --}}
    <div style="background:#f0f7ff; border:1px solid #cde4f7; border-radius:8px; padding:10px 20px; font-size:13px;">
        <span class="record">{{ $overall['first'] }}-{{ $overall['second'] }}-{{ $overall['third'] }}-{{ $overall['other'] }}</span>
        <span style="margin-left:12px; color:#888;">{{ $overall['total'] }}戦</span>
        <span style="margin-left:12px;" class="win-rate">勝率 {{ $overall['win_rate'] }}%</span>
        <span style="margin-left:8px;" class="place-rate">複勝率 {{ $overall['place_rate'] }}%</span>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

    {{-- グレード別 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">グレード別成績</div>
        <table class="stat-table" style="width:100%;">
            <thead><tr>
                <th class="stat-label" style="text-align:left;">グレード</th>
                <th>成績</th><th>勝率</th><th>複勝率</th>
            </tr></thead>
            <tbody>
                @foreach (['G1','G2','G3'] as $g)
                @if (isset($statsByGrade[$g]))
                @php $s = $statsByGrade[$g]; @endphp
                <tr>
                    <td class="stat-label"><span class="badge badge-{{ strtolower($g) }}">{{ $g }}</span></td>
                    <td class="record">{{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['other'] }}</td>
                    <td class="{{ $s['win_rate'] >= 20 ? 'win-rate' : '' }}">{{ $s['win_rate'] }}%</td>
                    <td class="{{ $s['place_rate'] >= 40 ? 'place-rate' : '' }}">{{ $s['place_rate'] }}%</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 脚質別 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">脚質別成績</div>
        <table class="stat-table" style="width:100%;">
            <thead><tr>
                <th class="stat-label" style="text-align:left;">脚質</th>
                <th>成績</th><th>勝率</th><th>複勝率</th>
            </tr></thead>
            <tbody>
                @foreach ($statsByStyle as $style => $s)
                @php
                    $styleColor = match($style) {
                        '逃げ' => '#cc0000', '先行' => '#c07000',
                        '差し' => '#1a7a30', '追込' => '#2a7bbf', default => '#888'
                    };
                @endphp
                <tr>
                    <td class="stat-label">
                        <span style="color:{{ $styleColor }}; font-weight:700;">{{ $style }}</span>
                    </td>
                    <td class="record">{{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['other'] }}</td>
                    <td class="{{ $s['win_rate'] >= 20 ? 'win-rate' : '' }}">{{ $s['win_rate'] }}%</td>
                    <td class="{{ $s['place_rate'] >= 40 ? 'place-rate' : '' }}">{{ $s['place_rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 競馬場別 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">競馬場別成績</div>
        <table class="stat-table" style="width:100%;">
            <thead><tr>
                <th class="stat-label" style="text-align:left;">競馬場</th>
                <th>成績</th><th>勝率</th><th>複勝率</th>
            </tr></thead>
            <tbody>
                @foreach ($statsByVenue as $venue => $s)
                <tr>
                    <td class="stat-label">{{ $venue }}</td>
                    <td class="record">{{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['other'] }}</td>
                    <td class="{{ $s['win_rate'] >= 20 ? 'win-rate' : '' }}">{{ $s['win_rate'] }}%</td>
                    <td class="{{ $s['place_rate'] >= 40 ? 'place-rate' : '' }}">{{ $s['place_rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 年別 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">年別成績</div>
        <table class="stat-table" style="width:100%;">
            <thead><tr>
                <th class="stat-label" style="text-align:left;">年</th>
                <th>成績</th><th>勝率</th><th>複勝率</th>
            </tr></thead>
            <tbody>
                @foreach ($statsByYear as $year => $s)
                <tr>
                    <td class="stat-label">{{ $year }}年</td>
                    <td class="record">{{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['other'] }}</td>
                    <td class="{{ $s['win_rate'] >= 20 ? 'win-rate' : '' }}">{{ $s['win_rate'] }}%</td>
                    <td class="{{ $s['place_rate'] >= 40 ? 'place-rate' : '' }}">{{ $s['place_rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 距離別 --}}
<div class="card" style="padding:16px; margin-bottom:20px;">
    <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">距離別成績</div>
    <div style="display:flex; flex-wrap:wrap; gap:12px;">
        @foreach ($statsByDistance as $dist => $s)
        <div style="min-width:160px; background:#f8fbff; border:1px solid #e0edf8; border-radius:6px; padding:8px 12px;">
            <div style="font-size:11px; color:#888; margin-bottom:4px;">{{ $dist }}</div>
            <div class="record" style="font-size:13px;">{{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['other'] }}</div>
            <div style="font-size:12px; margin-top:2px;">
                <span class="{{ $s['win_rate'] >= 20 ? 'win-rate' : '' }}">{{ $s['win_rate'] }}%</span>
                <span style="color:#888; margin:0 4px;">/</span>
                <span class="{{ $s['place_rate'] >= 40 ? 'place-rate' : '' }}">{{ $s['place_rate'] }}%</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- 出走履歴 --}}
<div class="card">
    <div style="font-size:13px; font-weight:600; color:#2a7bbf; padding:14px 16px 10px;">出走履歴（{{ $entries->count() }}戦）</div>
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>レース名</th>
                <th>開催</th>
                <th>距離</th>
                <th>馬場</th>
                <th>馬名</th>
                <th>着順</th>
                <th>人気</th>
                <th>タイム</th>
                <th>着差</th>
                <th>上がり</th>
                <th>脚質</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
            @php
                $pos = $entry->finish_position;
                $posColor = match(true) {
                    $pos === 1 => '#cc0000', $pos === 2 => '#c07000',
                    $pos === 3 => '#1a7a30', default => '#333',
                };
                $last3fRank = $last3fRankMap[$entry->id] ?? null;
                $min = $entry->finish_time ? floor($entry->finish_time / 60) : null;
                $sec = $entry->finish_time ? $entry->finish_time - $min * 60 : null;
                $style = app(App\Http\Controllers\JockeyController::class);
                $styleStr = $entry->corner_4 || $entry->corner_3
                    ? (function() use ($entry) {
                        $pos = $entry->corner_4 ?? $entry->corner_3;
                        $total = $entry->race->field_size;
                        if (!$pos || !$total) return '-';
                        $rate = $pos / $total;
                        return match(true) {
                            $rate <= 0.15 => '逃げ', $rate <= 0.35 => '先行',
                            $rate <= 0.60 => '差し', default => '追込',
                        };
                    })()
                    : '-';
                $styleColor = match($styleStr) {
                    '逃げ' => '#cc0000', '先行' => '#c07000',
                    '差し' => '#1a7a30', '追込' => '#2a7bbf', default => '#ccc',
                };
            @endphp
            <tr>
                <td>{{ $entry->race->race_date?->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('races.show', $entry->race) }}" class="link">{{ $entry->race->race_name }}</a>
                    @if ($entry->race->grade)
                        <span class="badge badge-{{ strtolower($entry->race->grade) }}">{{ $entry->race->grade }}</span>
                    @endif
                </td>
                <td>{{ $entry->race->venue }}</td>
                <td>{{ $entry->race->distance }}m</td>
                <td>{{ $entry->race->track_condition ?? '-' }}</td>
                <td>
                    @if ($entry->horse)
                        <a href="{{ route('horses.show', $entry->horse) }}" class="link">{{ $entry->horse->name }}</a>
                    @else -
                    @endif
                </td>
                <td style="font-weight:700; color:{{ $posColor }};">{{ $pos ?? '---' }}</td>
                <td>{{ $entry->popularity ?? '-' }}</td>
                <td style="font-family:monospace;">
                    @if ($min !== null) {{ $min }}:{{ number_format($sec, 1) }} @else --- @endif
                </td>
                <td>{{ $entry->time_diff ?? '-' }}</td>
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
                <td style="color:{{ $styleColor }}; font-weight:600; font-size:12px;">{{ $styleStr }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    <a href="javascript:history.back()" class="link" style="font-size:13px;">← 戻る</a>
</div>

@endsection
