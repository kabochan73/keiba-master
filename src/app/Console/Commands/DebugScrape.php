<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DebugScrape extends Command
{
    protected $signature = 'debug:scrape {url}';
    protected $description = 'エンコーディングとHTML構造を確認する';

    public function handle(): int
    {
        $url = $this->argument('url');
        $client = new Client(['headers' => ['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120.0.0.0']]);

        $response = $client->get($url);
        $html = (string) $response->getBody();

        // エンコーディング情報
        $this->info('=== エンコーディング ===');
        $this->line('Content-Type: ' . $response->getHeaderLine('Content-Type'));

        preg_match('/<meta[^>]+charset=["\']?([^\s"\'>;]+)/i', $html, $m);
        $this->line('Meta charset: ' . ($m[1] ?? 'not found'));
        $this->line('mb_detect: ' . (mb_detect_encoding($html, ['UTF-8', 'EUC-JP', 'SJIS-win'], true) ?: 'unknown'));

        // UTF-8変換してから確認
        $charset = $m[1] ?? null;
        if ($charset && strtoupper($charset) !== 'UTF-8') {
            $html = mb_convert_encoding($html, 'UTF-8', $charset);
            $this->line("→ {$charset} から UTF-8 に変換");
        }

        // レース名
        $this->info('=== レース情報 ===');
        preg_match('/<h1[^>]*class=["\']RaceName["\'][^>]*>(.*?)<\/h1>/s', $html, $mn);
        $this->line('RaceName (h1): ' . strip_tags($mn[1] ?? 'not found'));

        // RaceData01
        preg_match('/class=["\']RaceData01["\'][^>]*>(.*?)<\/p>/s', $html, $md1);
        $this->line('RaceData01: ' . strip_tags($md1[1] ?? 'not found'));

        // RaceData02のspan要素
        preg_match('/class=["\']RaceData02["\'][^>]*>(.*?)<\/p>/s', $html, $md2);
        if (!empty($md2[1])) {
            preg_match_all('/<span[^>]*>(.*?)<\/span>/s', $md2[1], $spans);
            $this->line('RaceData02 spans: ' . implode(' | ', array_map('strip_tags', $spans[1] ?? [])));
        }

        return Command::SUCCESS;
    }
}
