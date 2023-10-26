<?php

namespace Architekt\Installer;

class Color
{
    public static function darken(string $rgb, float $darker = 2): string
    {

        $hash = (strpos($rgb, '#') !== false) ? '#' : '';
        $rgb = (strlen($rgb) == 7) ? str_replace('#', '', $rgb) : ((strlen($rgb) == 6) ? $rgb : false);
        if (strlen($rgb) != 6) return $hash . '000000';
        $darker = ($darker > 1) ? $darker : 1;

        list($R16, $G16, $B16) = str_split($rgb, 2);

        $R = sprintf("%02X", floor(hexdec($R16) / $darker));
        $G = sprintf("%02X", floor(hexdec($G16) / $darker));
        $B = sprintf("%02X", floor(hexdec($B16) / $darker));

        return $hash . $R . $G . $B;
    }

    public static function template(Architekt $architekt,): Template
    {
        return (new Template())
            ->setCompileDir($architekt->directoryTemporary())
            ->setTemplateDir($architekt->directoryFiles() . DIRECTORY_SEPARATOR . 'templates');
    }
}