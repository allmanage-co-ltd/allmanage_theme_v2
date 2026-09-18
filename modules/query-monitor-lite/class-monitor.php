<?php

/**
 * QueryMonitorLite\Monitor — クエリモニター描画クラス
 *
 * - 環境判定（ローカル / ステージングのみ動作）
 * - shutdown フックで全クエリ集計後にバーを出力
 * - SAVEQUERIES が true なら個別クエリの詳細・時間も表示
 * - 遅いクエリはオレンジ太字 + コピーボタンで強調
 */

namespace QueryMonitorLite;

if (!defined('ABSPATH')) {
    exit;
}

class Monitor
{
    public function boot(): void
    {
        $enabled = apply_filters('qml/enabled', $this->isDevEnv());
        if (!$enabled) {
            return;
        }

        // 管理者のみ表示
        add_action('init', function () {
            if (!current_user_can('manage_options')) {
                remove_action('wp_footer', [$this, 'render'], PHP_INT_MAX);
                remove_action('admin_footer', [$this, 'render'], PHP_INT_MAX);
            }
        });

        add_action('wp_footer', [$this, 'render'], PHP_INT_MAX);
        add_action('admin_footer', [$this, 'render'], PHP_INT_MAX);
        add_action('wp_head', [$this, 'inlineStyle']);
        add_action('admin_head', [$this, 'inlineStyle']);
        add_action('wp_footer', [$this, 'inlineScript'], PHP_INT_MAX);
        add_action('admin_footer', [$this, 'inlineScript'], PHP_INT_MAX);
    }

    /**
     * バーと詳細パネルを出力する
     */
    public function render(): void
    {
        global $wpdb;

        $queries  = $wpdb->queries ?? [];
        $count    = count($queries);
        $total    = 0.0;
        $slow     = [];

        foreach ($queries as $q) {
            $time   = isset($q[1]) ? (float) $q[1] : 0.0;
            $total += $time;
            if ($time >= QML_SLOW_THRESHOLD) {
                $slow[] = ['sql' => $q[0], 'time' => $time, 'caller' => $q[2] ?? ''];
            }
        }

        usort($slow, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        $slow_count = count($slow);
        $total_fmt  = number_format($total * 1000, 1) . 'ms';
        $has_detail = defined('SAVEQUERIES') && SAVEQUERIES && !empty($queries);
        $bar_class  = $slow_count > 0 ? 'qml-bar qml-bar--slow' : 'qml-bar';
        ?>
        <div id="qml" class="<?php echo esc_attr($bar_class); ?>" role="complementary" aria-label="Query Monitor">
            <button class="qml-toggle" type="button" aria-expanded="false" aria-controls="qml-detail">
                <span class="qml-icon">🔍</span>
                <span class="qml-stat"><?php echo esc_html($count); ?> queries</span>
                <span class="qml-sep">|</span>
                <span class="qml-stat"><?php echo esc_html($total_fmt); ?></span>
                <?php if ($slow_count > 0) : ?>
                    <span class="qml-sep">|</span>
                    <span class="qml-stat qml-stat--warn">⚠ <?php echo esc_html($slow_count); ?> slow</span>
                <?php endif; ?>
                <?php if (!$has_detail) : ?>
                    <span class="qml-hint">（詳細は SAVEQUERIES=true で有効）</span>
                <?php endif; ?>
                <span class="qml-caret" aria-hidden="true">▲</span>
            </button>

            <?php if ($has_detail) : ?>
            <div id="qml-detail" class="qml-detail" hidden>
                <?php if (!empty($slow)) : ?>
                <div class="qml-section">
                    <div class="qml-section__title">⚠ Slow queries（><?php echo esc_html(QML_SLOW_THRESHOLD * 1000); ?>ms）</div>
                    <?php foreach ($slow as $q) : ?>
                    <div class="qml-row qml-row--slow">
                        <span class="qml-time"><?php echo esc_html(number_format($q['time'] * 1000, 2)); ?>ms</span>
                        <code class="qml-sql"><?php echo esc_html($this->truncate($q['sql'], 300)); ?></code>
                        <button class="qml-copy" type="button" title="SQLをコピー" data-sql="<?php echo esc_attr($q['sql']); ?>">📋</button>
                        <?php if ($q['caller'] !== '') : ?>
                        <span class="qml-caller"><?php echo esc_html($this->truncate($q['caller'], 100)); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="qml-section">
                    <div class="qml-section__title">All queries（<?php echo esc_html($count); ?>件 / <?php echo esc_html($total_fmt); ?>）</div>
                    <?php foreach ($queries as $q) :
                        $time    = isset($q[1]) ? (float) $q[1] : 0.0;
                        $is_slow = $time >= QML_SLOW_THRESHOLD;
                    ?>
                    <div class="qml-row <?php echo $is_slow ? 'qml-row--slow' : ''; ?>">
                        <span class="qml-time"><?php echo esc_html(number_format($time * 1000, 2)); ?>ms</span>
                        <code class="qml-sql"><?php echo esc_html($this->truncate($q[0], 200)); ?></code>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * インラインスタイルを出力する
     */
    public function inlineStyle(): void
    {
        ?>
        <style>
        #qml {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999999;
            font-family: monospace;
            font-size: 12px;
            line-height: 1;
            background: #1e1e1e;
            color: #d4d4d4;
            border-top: 2px solid #444;
        }
        #qml.qml-bar--slow { border-top-color: #e67e22; }
        .qml-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 6px 12px;
            background: none;
            border: none;
            color: inherit;
            font: inherit;
            cursor: pointer;
            text-align: left;
        }
        .qml-toggle:hover { background: #2a2a2a; }
        .qml-sep { color: #555; }
        .qml-stat--warn { color: #e67e22; font-weight: bold; }
        .qml-hint { color: #666; font-size: 11px; }
        .qml-caret { margin-left: auto; font-size: 10px; transition: transform .2s; }
        .qml-toggle[aria-expanded="true"] .qml-caret { transform: rotate(180deg); }
        .qml-detail {
            max-height: 40vh;
            overflow-y: auto;
            border-top: 1px solid #333;
        }
        .qml-section { padding: 8px 12px; border-bottom: 1px solid #2a2a2a; }
        .qml-section__title { color: #888; font-size: 11px; margin-bottom: 6px; }
        .qml-row {
            display: flex;
            gap: 8px;
            align-items: baseline;
            padding: 3px 0;
            border-bottom: 1px solid #2a2a2a;
            flex-wrap: wrap;
        }
        .qml-row:last-child { border-bottom: none; }
        .qml-row--slow .qml-time { color: #e67e22; font-weight: bold; }
        .qml-row--slow .qml-sql  { color: #e67e22; font-weight: bold; }
        .qml-time { color: #9cdcfe; min-width: 56px; flex-shrink: 0; }
        .qml-sql { color: #ce9178; word-break: break-all; flex: 1; font-size: 11px; background: none; }
        .qml-caller { color: #666; font-size: 10px; width: 100%; padding-left: 64px; }
        .qml-copy {
            flex-shrink: 0;
            background: none;
            border: 1px solid #444;
            color: #888;
            font-size: 11px;
            padding: 1px 4px;
            cursor: pointer;
            border-radius: 3px;
            line-height: 1;
        }
        .qml-copy:hover { background: #2a2a2a; color: #d4d4d4; }
        .qml-copy.qml-copied { border-color: #4ec9b0; color: #4ec9b0; }
        </style>
        <?php
    }

    /**
     * トグル・コピーボタンのスクリプトを出力する
     */
    public function inlineScript(): void
    {
        ?>
        <script>
        (function () {
            var btn    = document.querySelector('.qml-toggle');
            var detail = document.getElementById('qml-detail');
            if (!btn || !detail) return;

            btn.addEventListener('click', function () {
                var open = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', open ? 'false' : 'true');
                detail.hidden = open;
            });

            // コピーボタン
            document.addEventListener('click', function (e) {
                var copyBtn = e.target.closest('.qml-copy');
                if (!copyBtn) return;
                var sql = copyBtn.getAttribute('data-sql') || '';
                if (!sql) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(sql).then(function () { flashCopied(copyBtn); });
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = sql;
                    ta.style.position = 'fixed';
                    ta.style.top = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); flashCopied(copyBtn); } catch (err) {}
                    document.body.removeChild(ta);
                }
            });

            function flashCopied(el) {
                el.textContent = '✓';
                el.classList.add('qml-copied');
                setTimeout(function () {
                    el.textContent = '📋';
                    el.classList.remove('qml-copied');
                }, 1500);
            }
        })();
        </script>
        <?php
    }

    /**
     * ホスト名からローカル・ステージング環境かを判定する
     */
    private function isDevEnv(): bool
    {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';

        foreach (QML_LOCAL_HOSTS as $pattern) {
            if (strpos($host, $pattern) !== false) {
                return true;
            }
        }
        foreach (QML_STAGING_HOSTS as $pattern) {
            if (strpos($host, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 長い文字列を省略する
     */
    private function truncate(string $str, int $max): string
    {
        if (mb_strlen($str) <= $max) {
            return $str;
        }
        return mb_substr($str, 0, $max) . '…';
    }
}
