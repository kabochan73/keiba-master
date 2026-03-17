<?php

namespace App\Services\Scraping;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class NetkeibaClient
{
    private Client $client;

    // リクエスト間隔（秒）
    private int $sleepSeconds = 2;

    public function __construct()
    {
        $this->client = new Client([
            'timeout'         => 30,
            'connect_timeout' => 10,
            'headers'         => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ja,en-US;q=0.7,en;q=0.3',
            ],
        ]);
    }

    /**
     * URLを取得してDomCrawlerを返す
     */
    public function fetch(string $url): ?Crawler
    {
        try {
            sleep($this->sleepSeconds);

            $response = $this->client->get($url);
            $html = (string) $response->getBody();

            // エンコーディングを検出して UTF-8 に変換
            $encoding = $this->detectEncoding($response, $html);
            if ($encoding && strtoupper($encoding) !== 'UTF-8') {
                $html = mb_convert_encoding($html, 'UTF-8', $encoding);
            }

            // 変換後もmeta charsetがEUC-JPのままだとDomCrawlerが再変換してしまうため
            // meta charsetをUTF-8に書き換える
            $html = preg_replace(
                '/<meta[^>]+charset=["\']?[^"\'\s;>]+["\']?/i',
                '<meta charset="UTF-8"',
                $html
            );

            return new Crawler($html);
        } catch (RequestException $e) {
            Log::error("スクレイピング失敗: {$url}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * レスポンスヘッダーまたはHTMLのmeta charsetからエンコーディングを検出
     */
    private function detectEncoding($response, string $html): ?string
    {
        // 1. Content-Typeヘッダーから取得
        $contentType = $response->getHeaderLine('Content-Type');
        if (preg_match('/charset=([^\s;]+)/i', $contentType, $m)) {
            return $m[1];
        }

        // 2. HTMLのmeta charsetから取得
        if (preg_match('/<meta[^>]+charset=["\']?([^\s"\'>;]+)/i', $html, $m)) {
            return $m[1];
        }

        // 3. mb_detect_encoding にフォールバック
        $detected = mb_detect_encoding($html, ['UTF-8', 'EUC-JP', 'SJIS-win'], true);
        return $detected ?: null;
    }

    public function setSleepSeconds(int $seconds): void
    {
        $this->sleepSeconds = $seconds;
    }

    /**
     * 文字列を安全なUTF-8に変換する（不正バイトを除去）
     */
    public static function cleanText(string $text): string
    {
        // 不正なUTF-8シーケンスを除去
        $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // それでも残る不正バイトをiconvで除去
        $result = iconv('UTF-8', 'UTF-8//IGNORE', $clean);
        return $result !== false ? trim($result) : trim($clean);
    }
}
