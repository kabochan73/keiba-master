@extends('layouts.app')

@section('title', $horse->name)

@section('content')

{{-- ヘッダー --}}
<div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
    <div class="page-title" style="margin:0;">{{ $horse->name }}</div>
    @if ($horse->running_style)
        @php
            $styleColor = match($horse->running_style) {
                '逃げ' => '#cc0000',
                '先行' => '#c07000',
                '差し' => '#1a7a30',
                '追込' => '#2a7bbf',
                default => '#888',
            };
        @endphp
        <span style="
            background:{{ $styleColor }}20; color:{{ $styleColor }};
            border:1px solid {{ $styleColor }}60;
            padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600;
        ">{{ $horse->running_style }}</span>
    @endif
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

    {{-- 基本情報 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">基本情報</div>
        <table style="font-size:13px;">
            <tr>
                <td style="color:#888; width:80px; border:none; padding:3px 0;">性別</td>
                <td style="border:none; padding:3px 0;">{{ $horse->sex ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:3px 0;">毛色</td>
                <td style="border:none; padding:3px 0;">{{ $horse->coat_color ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:3px 0;">生年月日</td>
                <td style="border:none; padding:3px 0;">{{ $horse->birth_date?->format('Y年m月d日') ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:3px 0;">厩舎</td>
                <td style="border:none; padding:3px 0;">{{ $horse->trainer?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:3px 0;">生産者</td>
                <td style="border:none; padding:3px 0;">{{ $horse->breeder?->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- 血統 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">血統</div>
        <table style="font-size:13px;">
            <tr>
                <td style="color:#888; width:80px; border:none; padding:3px 0;">父</td>
                <td style="border:none; padding:3px 0;">{{ $horse->father ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:3px 0;">母</td>
                <td style="border:none; padding:3px 0;">{{ $horse->mother ?? '-' }}</td>
            </tr>
            <tr>
                <td style="color:#888; border:none; padding:3px 0;">母父</td>
                <td style="border:none; padding:3px 0;">{{ $horse->mother_father ?? '-' }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- 成績サマリー --}}
@if ($entries->count() > 0)
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

    {{-- 距離別 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">距離別成績</div>
        <table style="font-size:12px; width:100%;">
            <thead>
                <tr style="color:#888;">
                    <th style="text-align:left; padding:3px 6px 3px 0; border-bottom:1px solid #eee;">距離</th>
                    <th style="padding:3px 6px; border-bottom:1px solid #eee;">成績</th>
                    <th style="padding:3px 6px; border-bottom:1px solid #eee;">勝率</th>
                    <th style="padding:3px 6px; border-bottom:1px solid #eee;">連対率</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statsByDistance as $dist => $s)
                <tr>
                    <td style="padding:4px 6px 4px 0; border:none;">{{ $dist }}m</td>
                    <td style="padding:4px 6px; border:none; font-family:monospace;">
                        {{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['total'] - $s['first'] - $s['second'] - $s['third'] }}
                    </td>
                    <td style="padding:4px 6px; border:none; color:{{ $s['win_rate'] >= 30 ? '#cc0000' : '#333' }}; font-weight:{{ $s['win_rate'] >= 30 ? '700' : '400' }};">
                        {{ $s['win_rate'] }}%
                    </td>
                    <td style="padding:4px 6px; border:none; color:{{ $s['place_rate'] >= 50 ? '#1a7a30' : '#333' }};">
                        {{ $s['place_rate'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- グレード別 --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:13px; font-weight:600; color:#2a7bbf; margin-bottom:10px;">グレード別成績</div>
        <table style="font-size:12px; width:100%;">
            <thead>
                <tr style="color:#888;">
                    <th style="text-align:left; padding:3px 6px 3px 0; border-bottom:1px solid #eee;">グレード</th>
                    <th style="padding:3px 6px; border-bottom:1px solid #eee;">成績</th>
                    <th style="padding:3px 6px; border-bottom:1px solid #eee;">勝率</th>
                    <th style="padding:3px 6px; border-bottom:1px solid #eee;">連対率</th>
                </tr>
            </thead>
            <tbody>
                @foreach (['G1','G2','G3'] as $g)
                    @if (isset($statsByGrade[$g]))
                    @php $s = $statsByGrade[$g]; @endphp
                    <tr>
                        <td style="padding:4px 6px 4px 0; border:none;">
                            <span class="badge badge-{{ strtolower($g) }}">{{ $g }}</span>
                        </td>
                        <td style="padding:4px 6px; border:none; font-family:monospace;">
                            {{ $s['first'] }}-{{ $s['second'] }}-{{ $s['third'] }}-{{ $s['total'] - $s['first'] - $s['second'] - $s['third'] }}
                        </td>
                        <td style="padding:4px 6px; border:none; color:{{ $s['win_rate'] >= 30 ? '#cc0000' : '#333' }}; font-weight:{{ $s['win_rate'] >= 30 ? '700' : '400' }};">
                            {{ $s['win_rate'] }}%
                        </td>
                        <td style="padding:4px 6px; border:none; color:{{ $s['place_rate'] >= 50 ? '#1a7a30' : '#333' }};">
                            {{ $s['place_rate'] }}%
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 過去成績テーブル --}}
<div class="card">
    <div style="font-size:13px; font-weight:600; color:#2a7bbf; padding:14px 16px 10px;">過去成績（{{ $entries->count() }}戦）</div>
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>レース名</th>
                <th>開催</th>
                <th>距離</th>
                <th>馬場</th>
                <th>着順</th>
                <th>人気</th>
                <th>タイム</th>
                <th>補正T</th>
                <th>上がり</th>
                <th>コーナー</th>
                <th>騎手</th>
                <th>体重</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
            @php
                $pos = $entry->finish_position;
                $posColor = match(true) {
                    $pos === 1 => '#cc0000',
                    $pos === 2 => '#c07000',
                    $pos === 3 => '#1a7a30',
                    default    => '#333',
                };
                $min = $entry->finish_time ? floor($entry->finish_time / 60) : null;
                $sec = $entry->finish_time ? $entry->finish_time - $min * 60 : null;
                $c = $entry->correction;
                $cm = $c?->corrected_time ? floor($c->corrected_time / 60) : null;
                $cs = $c?->corrected_time ? $c->corrected_time - $cm * 60 : null;
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
                <td style="font-weight:700; color:{{ $posColor }};">
                    {{ $pos ?? '---' }}
                </td>
                <td>{{ $entry->popularity ?? '-' }}</td>
                <td style="font-family:monospace;">
                    @if ($min !== null)
                        {{ $min }}:{{ number_format($sec, 1) }}
                    @else
                        ---
                    @endif
                </td>
                <td style="font-family:monospace; color:#2a7bbf;">
                    @if ($cm !== null)
                        {{ $cm }}:{{ number_format($cs, 1) }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $entry->last_3f ?? '-' }}</td>
                <td style="font-size:12px; color:#666;">
                    {{ implode('-', array_filter([$entry->corner_1, $entry->corner_2, $entry->corner_3, $entry->corner_4], fn($v) => $v !== null)) }}
                </td>
                <td>{{ $entry->jockey?->name ?? '-' }}</td>
                <td style="font-size:12px;">
                    {{ $entry->weight ?? '-' }}
                    @if ($entry->weight_change !== null)
                        <span style="color:{{ $entry->weight_change > 0 ? '#cc0000' : ($entry->weight_change < 0 ? '#1a7a30' : '#999') }};">
                            ({{ $entry->weight_change >= 0 ? '+' : '' }}{{ $entry->weight_change }})
                        </span>
                    @endif
                </td>
            </tr>
            @if ($c?->note)
            <tr>
                <td colspan="13" style="font-size:12px; color:#666; padding:2px 12px 8px; border-top:none;">
                    📝 {{ $c->note }}
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card" style="text-align:center; color:#999; padding:40px;">
    成績データがありません
</div>
@endif

<div style="margin-top:16px;">
    <a href="javascript:history.back()" class="link" style="font-size:13px;">← 戻る</a>
</div>

@endsection
