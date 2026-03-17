<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '競馬分析ツール')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Hiragino Sans', sans-serif;
            background: #f0f7ff;
            color: #333;
            font-size: 14px;
        }

        /* ヘッダー */
        header {
            background: #fff;
            border-bottom: 2px solid #b8daf5;
            padding: 0 24px;
            height: 52px;
            display: flex;
            align-items: center;
            gap: 32px;
        }
        header .logo {
            font-size: 16px;
            font-weight: 700;
            color: #2a7bbf;
            text-decoration: none;
        }
        header nav a {
            color: #555;
            text-decoration: none;
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 4px;
        }
        header nav a:hover { background: #e8f3fc; color: #2a7bbf; }

        /* メインコンテンツ */
        main {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        /* ページタイトル */
        .page-title {
            font-size: 18px;
            font-weight: 700;
            color: #2a7bbf;
            margin-bottom: 16px;
        }

        /* カード */
        .card {
            background: #fff;
            border: 1px solid #cde4f7;
            border-radius: 8px;
            overflow: hidden;
        }

        /* テーブル */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #e8f3fc;
            color: #2a7bbf;
            font-weight: 600;
            padding: 10px 12px;
            text-align: left;
            font-size: 13px;
            border-bottom: 1px solid #cde4f7;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f7ff;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f5faff; }

        /* バッジ */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-g1 { background: #fff0e0; color: #c85000; border: 1px solid #f5c88a; }
        .badge-g2 { background: #e8f0ff; color: #1a4fbf; border: 1px solid #a8c0f0; }
        .badge-g3 { background: #f0fff0; color: #1a7a30; border: 1px solid #90d0a0; }

        /* ペースバッジ */
        .pace-high   { background: #ffe8e8; color: #c00; border: 1px solid #f5a0a0; }
        .pace-slow   { background: #e8ffe8; color: #060; border: 1px solid #90d090; }
        .pace-middle { background: #f0f0f0; color: #555; border: 1px solid #ccc; }

        /* リンク */
        a.link { color: #2a7bbf; text-decoration: none; }
        a.link:hover { text-decoration: underline; }

        /* ページネーション */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 4px;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #cde4f7;
            border-radius: 4px;
            color: #2a7bbf;
            text-decoration: none;
            background: #fff;
            font-size: 13px;
        }
        .pagination .active span {
            background: #2a7bbf;
            color: #fff;
            border-color: #2a7bbf;
        }
        .pagination a:hover { background: #e8f3fc; }
    </style>
    @stack('styles')
</head>
<body>
    <header>
        <a href="{{ route('races.index') }}" class="logo">競馬分析ツール</a>
        <nav>
            <a href="{{ route('races.index') }}">レース一覧</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
