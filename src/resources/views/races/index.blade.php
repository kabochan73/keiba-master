@extends('layouts.app')

@section('title', 'レース一覧')

@section('content')
<div class="page-title">レース一覧</div>

{{-- 検索フォーム --}}
<form method="GET" action="{{ route('races.index') }}" style="margin-bottom:16px;">
<div class="card" style="padding:14px 16px;">
    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

        {{-- キーワード --}}
        <div>
            <div style="font-size:11px; color:#888; margin-bottom:4px;">レース名</div>
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="例：宝塚記念"
                style="width:160px; padding:6px 10px; border:1px solid #cde4f7; border-radius:4px; font-size:13px;"
            >
        </div>

        {{-- 年 --}}
        <div>
            <div style="font-size:11px; color:#888; margin-bottom:4px;">年</div>
            <select name="year" style="padding:6px 10px; border:1px solid #cde4f7; border-radius:4px; font-size:13px; background:#fff;">
                <option value="">すべて</option>
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}年</option>
                @endforeach
            </select>
        </div>

        {{-- グレード --}}
        <div>
            <div style="font-size:11px; color:#888; margin-bottom:4px;">グレード</div>
            <select name="grade" style="padding:6px 10px; border:1px solid #cde4f7; border-radius:4px; font-size:13px; background:#fff;">
                <option value="">すべて</option>
                <option value="G1" @selected(request('grade') === 'G1')>G1</option>
                <option value="G2" @selected(request('grade') === 'G2')>G2</option>
                <option value="G3" @selected(request('grade') === 'G3')>G3</option>
            </select>
        </div>

        {{-- 開催場 --}}
        <div>
            <div style="font-size:11px; color:#888; margin-bottom:4px;">開催場</div>
            <select name="venue" style="padding:6px 10px; border:1px solid #cde4f7; border-radius:4px; font-size:13px; background:#fff;">
                <option value="">すべて</option>
                @foreach ($venues as $v)
                    <option value="{{ $v }}" @selected(request('venue') === $v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" style="
                background:#2a7bbf; color:#fff; border:none;
                padding:7px 20px; border-radius:4px; font-size:13px;
                font-weight:600; cursor:pointer;
            ">検索</button>
            @if (request()->hasAny(['keyword','year','grade','venue']))
                <a href="{{ route('races.index') }}" style="
                    padding:7px 14px; border:1px solid #ccc; border-radius:4px;
                    font-size:13px; color:#666; text-decoration:none; background:#fff;
                ">クリア</a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- 件数表示 --}}
<div style="font-size:12px; color:#888; margin-bottom:8px;">
    {{ $races->total() }} 件
    @if (request()->hasAny(['keyword','year','grade','venue']))
        <span style="color:#2a7bbf;">（絞り込み中）</span>
    @endif
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>レース名</th>
                <th>開催</th>
                <th>距離</th>
                <th>馬場</th>
                <th>頭数</th>
                <th>前半3F</th>
                <th>後半3F</th>
                <th>ペース</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($races as $race)
            <tr>
                <td>{{ $race->race_date?->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('races.show', $race) }}" class="link">
                        {{ $race->race_name }}
                    </a>
                    @if ($race->grade)
                        <span class="badge badge-{{ strtolower($race->grade) }}">{{ $race->grade }}</span>
                    @endif
                </td>
                <td>{{ $race->venue }}</td>
                <td>{{ $race->distance }}m</td>
                <td>{{ $race->track_condition ?? '-' }}</td>
                <td>{{ $race->field_size ?? '-' }}頭</td>
                <td>{{ $race->pace_3f_front ?? '-' }}</td>
                <td>{{ $race->pace_3f_back ?? '-' }}</td>
                <td>
                    @if ($race->pace_category)
                        <span class="badge pace-{{ $race->pace_category === 'ハイ' ? 'high' : ($race->pace_category === 'スロー' ? 'slow' : 'middle') }}">
                            {{ $race->pace_category }}
                        </span>
                    @elseif ($race->pace_balance !== null)
                        @php
                            $b = $race->pace_balance;
                            $label = $b >= 1.0 ? 'スロー' : ($b <= -1.0 ? 'ハイ' : 'ミドル');
                            $cls   = $b >= 1.0 ? 'slow' : ($b <= -1.0 ? 'high' : 'middle');
                        @endphp
                        <span class="badge pace-{{ $cls }}">{{ $label }}</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; color:#999; padding: 40px;">
                    レースデータがありません
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($races->hasPages())
    <div class="pagination">
        {{ $races->links() }}
    </div>
@endif
@endsection
