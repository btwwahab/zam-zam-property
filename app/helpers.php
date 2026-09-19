<?php

use App\Models\PageContent;

if (! function_exists('pc')) {
    /**
     * Page-content string by key, with a fallback default.
     */
    function pc(string $key, string $default = ''): string
    {
        static $map = null;
        if ($map === null) {
            $map = PageContent::map();
        }
        $v = $map[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }
}

if (! function_exists('pc_list')) {
    /**
     * Page-content JSON list by key -> array of assoc rows.
     */
    function pc_list(string $key, array $default = []): array
    {
        $raw = pc($key, '');
        if ($raw === '') {
            return $default;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }
}

if (! function_exists('legal_blocks')) {
    /**
     * Split a plain-text legal body into headings + paragraphs.
     * A block (separated by a blank line) that is a single short line
     * with no ending punctuation is treated as a heading.
     */
    function legal_blocks(string $text): array
    {
        $out = [];
        foreach (preg_split('/\R\s*\R/', trim($text)) as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $isHeading = ! preg_match('/\R/', $block)
                && mb_strlen($block) <= 70
                && ! preg_match('/[.!?,:;]$/u', $block);
            $out[] = ['type' => $isHeading ? 'h' : 'p', 'text' => $block];
        }

        return $out;
    }
}

if (! function_exists('money_pk')) {
    /**
     * Format a PKR amount the way this site's admins already write prices
     * by hand ("PKR 45 Lakh", "PKR 1.6 Crore") — used for auto-computed
     * totals where nobody types the display string manually.
     */
    function money_pk(float $amount): string
    {
        $amount = round($amount);
        if ($amount >= 10000000) {
            $v = $amount / 10000000;

            return 'PKR ' . rtrim(rtrim(number_format($v, 2), '0'), '.') . ' Crore';
        }
        if ($amount >= 100000) {
            $v = $amount / 100000;

            return 'PKR ' . rtrim(rtrim(number_format($v, 2), '0'), '.') . ' Lakh';
        }

        return 'PKR ' . number_format($amount);
    }
}

if (! function_exists('asset_v')) {
    /**
     * Local asset URL with a ?v=<mtime> cache-buster so browsers always
     * pick up the latest file after a deploy.
     */
    function asset_v(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $file = public_path(ltrim($path, '/'));
        $v = is_file($file) ? filemtime($file) : substr(md5($path), 0, 8);

        return $path . '?v=' . $v;
    }
}
