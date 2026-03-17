@extends('layouts.app')

@section('title', 'レース一覧')

@section('content')
<div class="page-title">レース一覧</div>

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
