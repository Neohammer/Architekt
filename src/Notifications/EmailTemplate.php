<?php

namespace Architekt\Notifications;

use Architekt\DB\DBEntity;
use Architekt\DB\DBEntityCache;
use Users\Administrator;

class EmailTemplate extends DBEntity
{
    use DBEntityCache;

    const DEFAULT = 1;
    protected static ?string $_table_prefix = 'at_';
    protected static ?string $_table = 'email_template';
    protected static string $_labelField = 'name';

    protected static array $sendGridReplacers = [
        '|(<style.*>)|Ui' => '{literal}$1',
        '|(<\/style>)|Ui' => '$1{/literal}',
        '|\{\{ ([a-z\.]+) \}\}|i' => '{\$json.$1}',
        '|\{\{\#if ([a-z\.]+) ?\}\}|i' => '{if \$json.$1}',
        '|\{\{/if\}\}|i' => '{/if}',
        '|\{\{formatDate ([a-z\.]+) ([a-z\.]+) ?\}\}|i' => '{date(\$json.$2,strtotime(\$json.$1))}',
    ];

    /** @return static[] */
    public static function byAdministrator(Administrator $administrator): array
    {
        ($that = new self())->_search()
            ->and($that, $administrator);

        return $that->_results();
    }

    public static function byKey(string $key, Administrator $administrator): ?static
    {
        ($that = new self())->_search()
            ->and($that, 'key', $key)
            ->and($that, $administrator)
            ->limit();

        if ($that->_next()) {
            return $that;
        }

        return null;
    }

    public static function default(): ?static
    {
        return new self(self::DEFAULT);
    }

    public function administrator(): Administrator
    {
        return Administrator::fromCache($this->_get('administrator_id'));
    }

    public function templateCode(): string
    {
        return $this->_get('template_code');
    }

    public function htmlBodySmarty(): string
    {
        return preg_replace(
            array_keys(self::$sendGridReplacers),
            self::$sendGridReplacers,
            $this->_get('body_html')
        );
    }

    public function htmlTextSmarty(): string
    {
        return preg_replace(
            array_keys(self::$sendGridReplacers),
            self::$sendGridReplacers,
            $this->_get('body_text')
        );
    }

    public function key(): string
    {
        return $this->_get('key');
    }

    public function varsInHTML(): array
    {
        preg_match_all('/\{\$([A-Z\_]+)\}/Ui', $this->_get('body_html'), $founds);

        return $founds[1] ?? [];
    }

    public function varsDefault(): array
    {
        return json_decode($this->_get('vars_default'), true);
    }

    public function vars(): array
    {
        $varsHTML = $this->varsInHTML();
        $varsCommon = $this->varsCommon();
        $vars = $this->_get('vars') ? json_decode($this->_get('vars'), true) : [];
        $return = [];

        foreach ($varsHTML as $varHtml) {
            if(!array_key_exists($varHtml, $varsCommon)) {
                $return[$varHtml] = $vars[$varHtml] ?? 'html';
            }
        }

        return $return;
    }


    public function varsCommon(): array
    {
        return [
            'AUTHOR_NAME' => $this->administrator()->label(),
            'AUTHOR_LOGO' => $this->administrator()->label(),
            'AUTHOR_FACEBOOK' => $this->administrator()->_get('facebook'),
            'AUTHOR_INSTAGRAM' => $this->administrator()->_get('instagram'),
            'AUTHOR_TWITTER' => $this->administrator()->_get('twitter'),
            'AUTHOR_PINTEREST' => $this->administrator()->_get('pinterest'),
        ];
    }
}