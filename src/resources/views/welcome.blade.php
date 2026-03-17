<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeibaAnalyzer - 競馬分析ツール</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 40px;
        }
        .logo {
            font-size: 3rem;
            font-weight: bold;
            color: #e94560;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(233, 69, 96, 0.5);
        }
        .subtitle {
            font-size: 1.2rem;
            color: #a8b2d8;
            margin-bottom: 40px;
        }
        .status {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
        }
        .status h2 {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #64ffda;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #a8b2d8;
        }
        .info-value {
            color: #64ffda;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            background: #e94560;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">KeibaAnalyzer</div>
        <div class="subtitle">競馬分析ツール - Docker + Laravel 11</div>

        <div class="status">
            <h2>システム情報</h2>
            <div class="info-item">
                <span class="info-label">フレームワーク</span>
                <span class="info-value">Laravel {{ app()->version() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">PHP バージョン</span>
                <span class="info-value">{{ phpversion() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">環境</span>
                <span class="info-value">{{ app()->environment() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">データベース</span>
                <span class="info-value">MySQL 8.0</span>
            </div>
            <div class="info-item">
                <span class="info-label">ステータス</span>
                <span class="info-value" style="color: #64ffda;">稼働中</span>
            </div>
        </div>

        <div class="badge">開発環境</div>
    </div>
</body>
</html>
