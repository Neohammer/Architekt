<?php

namespace Users;

class Link
{
    const TYPE_REAL = 'real';
    const TYPE_LINK = 'link';
    const TYPE_MODAL = 'modal';
    const TYPE_APPEND = 'append';
    const TYPE_ACTION = 'action';

    protected static function build(
        string  $url,
        string  $icon,
        string  $text,
        string  $class,
        string  $type = self::TYPE_LINK,
        ?string $confirm = null,
        ?string $target = null,
        bool    $hideLabelOnMobile = true,
        bool    $disabled = false,
    ): string
    {

        if ($disabled) {
            $parts = [];
            $class .= ' disabled';

        } else {
            $parts = [
                sprintf('href="%s"', $url)
            ];

            if ($type === self::TYPE_REAL) {
                $parts[] = 'real="1"';

            } elseif ($type !== self::TYPE_LINK) {
                $parts[] = sprintf('eventType="%s"', $type);
            }

            if ($target) {
                if ($type === self::TYPE_REAL) {
                    $parts[] = sprintf('target="%s"', $target);
                } else {

                    $parts[] = sprintf('data-target="%s"', $target);
                }
            }

            if ($confirm) {
                $parts[] = sprintf('confirm="%s"', htmlentities($confirm));
            }
        }

        return sprintf(
            '<a %s class="%s">%s<span class="%s"> %s</span></a>',
            join(' ', $parts),
            $class,
            $icon,
            $hideLabelOnMobile ? 'd-none d-lg-inline' : '',
            $text
        );
    }
}