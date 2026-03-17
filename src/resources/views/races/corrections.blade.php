@extends('layouts.app')

@section('title', $race->race_name . ' 補正入力')

@push('styles')
<style>
    .correction-table th { font-size: 12px; padding: 8px 10px; }
    .correction-table td { padding: 6px 8px; }

    .input-s {
        width: 64px;
        padding: 4px 6px;
        border: 1px solid #cde4f7;
        border-radius: 4px;
        font-size: 13px;
        text-align: right;
        background: #fff;
    }
    .input-s:focus {
        outline: none;
        border-color: #2a7bbf;
        background: #f5faff;
    }
    .input-note {
        width: 160px;
        padding: 4px 6px;
        border: 1px solid #cde4f7;
        border-radius: 4px;
        font-size: 12px;
        background: #fff;
    }
    .input-note:focus {
        outline: none;
        border-color: #2a7bbf;
    }

    .corrected-time {
        font-weight: 700;
        color: #2a7bbf;
        font-size: 13px;
    }
    .corrected-time.faster { color: #1a7a30; }
    .corrected-time.slower { color: #cc0000; }

    .btn-save {
        background: #2a7bbf;
        color: #fff;
        border: none;
        padding: 10px 32px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-save:hover { background: #1a6aae; }

    .alert-success {
        background: #e8ffe8;
        border: 1px solid #90d090;
        color: #1a7a30;
        padding: 10px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-size: 13px;
    }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
    <div>
        <div class="page-title" style="margin-bottom:2px;">補正入力</div>
        <div style="font-size:13px; color:#666;">
            {{ $race->race_name }}
            @if($race->grade) <span class="badge badge-{{ strtolower($race->grade) }}">{{ $race->grade }}</span> @endif
            &nbsp;{{ $race->race_date?->format('Y/m/d') }} &nbsp;{{ $race->venue }} &nbsp;{{ $race->course_type }}{{ $race->distance }}m
        </div>
    </div>
    <a href="{{ route('races.show', $race) }}" class="link" style="font-size:13px;">← レース詳細に戻る</a>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('races.corrections.update', $race) }}">
@csrf

<div class="card" style="margin-bottom:20px;">
    <table class="correction-table">
        <thead>
            <tr>
                <th>着順</th>
                <th>補正順位</th>
                <th>馬名</th>
                <th>騎手</th>
                <th>実タイム</th>
                <th title="外を回った距離ロスなど">距離ロス</th>
                <th title="詰まり・不利">詰まり</th>
                <th title="出遅れ">出遅れ</th>
                <th title="騎手の上乗せ・マイナス補正">騎手補正</th>
                <th title="その他">その他</th>
                <th>合計補正</th>
                <th>補正タイム</th>
                <th>メモ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
            @php
                $c   = $entry->correction;
                $min = $entry->finish_time ? floor($entry->finish_time / 60) : null;
                $sec = $entry->finish_time ? $entry->finish_time - $min * 60 : null;

                $totalCorrection = $c
                    ? $c->distance_loss + $c->interference + $c->slow_start + $c->jockey_correction + $c->other_correction
                    : 0;

                $correctedRank = $correctedRanks[$entry->id] ?? null;
                $rankChanged   = $correctedRank && $entry->finish_position && $correctedRank !== $entry->finish_position;
                $rankUp        = $rankChanged && $correctedRank < $entry->finish_position;
            @endphp
            <tr>
                <td><strong>{{ $entry->finish_position ?? '---' }}</strong></td>
                <td>
                    @if ($correctedRank)
                        @if ($rankChanged)
                            <strong style="color:{{ $rankUp ? '#1a7a30' : '#cc0000' }};">
                                {{ $correctedRank }}
                                <span style="font-size:11px;">{{ $rankUp ? '▲' : '▼' }}</span>
                            </strong>
                        @else
                            <span style="color:#999;">{{ $correctedRank }}</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td style="font-weight:600;">{{ $entry->horse?->name ?? '-' }}</td>
                <td style="color:#555;">{{ $entry->jockey?->name ?? '-' }}</td>
                <td style="font-family:monospace;">
                    @if ($entry->finish_time)
                        {{ $min }}:{{ number_format($sec, 1) }}
                    @else
                        ---
                    @endif
                </td>

                {{-- 補正入力フィールド --}}
                @foreach (['distance_loss' => '0.0', 'interference' => '0.0', 'slow_start' => '0.0', 'jockey_correction' => '0.0', 'other_correction' => '0.0'] as $field => $default)
                <td>
                    <input
                        type="number"
                        step="0.1"
                        class="input-s"
                        name="corrections[{{ $entry->id }}][{{ $field }}]"
                        value="{{ $c ? number_format($c->$field, 1) : $default }}"
                    >
                </td>
                @endforeach

                {{-- 合計補正 --}}
                <td style="font-family:monospace; color:{{ $totalCorrection != 0 ? '#2a7bbf' : '#999' }}; font-weight:600;">
                    {{ $totalCorrection != 0 ? ($totalCorrection > 0 ? '+' : '') . number_format($totalCorrection, 1) : '-' }}
                </td>

                {{-- 補正タイム --}}
                <td style="font-family:monospace;">
                    @if ($c && $c->corrected_time)
                        @php
                            $cm = floor($c->corrected_time / 60);
                            $cs = $c->corrected_time - $cm * 60;
                            $diff = $entry->finish_time ? $c->corrected_time - $entry->finish_time : 0;
                        @endphp
                        <span class="corrected-time {{ $diff < 0 ? 'faster' : ($diff > 0 ? 'slower' : '') }}">
                            {{ $cm }}:{{ number_format($cs, 1) }}
                        </span>
                    @else
                        <span style="color:#ccc;">-</span>
                    @endif
                </td>

                {{-- メモ --}}
                <td>
                    <input
                        type="text"
                        class="input-note"
                        name="corrections[{{ $entry->id }}][note]"
                        value="{{ $c?->note ?? '' }}"
                        placeholder="外を回った等"
                    >
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="text-align:right;">
    <button type="submit" class="btn-save">保存する</button>
</div>

</form>

<div style="margin-top:12px; font-size:12px; color:#999;">
    ※ 補正値はプラスが「有利補正（本来はもっと速かった）」。補正タイム = 実タイム − 合計補正。
</div>

@endsection
