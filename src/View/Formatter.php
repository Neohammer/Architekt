<?php

namespace Architekt\View;

use Smarty_Internal_Template;

class Formatter
{
    const DAYS = array(
        1 => array('nom' => 'Lundi', 'abbr' => 'Lun.', 'code' => 'lundi', 'row_css' => 'info', 'isWorked' => true),
        2 => array('nom' => 'Mardi', 'abbr' => 'Mar.', 'code' => 'mardi', 'row_css' => 'info', 'isWorked' => true),
        3 => array('nom' => 'Mercredi', 'abbr' => 'Mer.', 'code' => 'mercredi', 'row_css' => 'info', 'isWorked' => true),
        4 => array('nom' => 'Jeudi', 'abbr' => 'Jeu.', 'code' => 'jeudi', 'row_css' => 'info', 'isWorked' => true),
        5 => array('nom' => 'Vendredi', 'abbr' => 'Ven.', 'code' => 'vendredi', 'row_css' => 'info', 'isWorked' => true),
        6 => array('nom' => 'Samedi', 'abbr' => 'Sam.', 'code' => 'samedi', 'row_css' => 'warning', 'isWorked' => false),
        7 => array('nom' => 'Dimanche', 'abbr' => 'Dim.', 'code' => 'dimanche', 'row_css' => 'danger', 'isWorked' => false),

    );

    const MONTHS = array(
        1 => array('nom' => 'Janvier', 'abbr' => 'Jan.'),
        2 => array('nom' => 'Février', 'abbr' => 'Fév.'),
        3 => array('nom' => 'Mars', 'abbr' => 'Mar.'),
        4 => array('nom' => 'Avril', 'abbr' => 'Avr.'),
        5 => array('nom' => 'Mai', 'abbr' => 'Mai'),
        6 => array('nom' => 'Juin', 'abbr' => 'Juin'),
        7 => array('nom' => 'Juillet', 'abbr' => 'Juil.'),
        8 => array('nom' => 'Août', 'abbr' => 'Aout'),
        9 => array('nom' => 'Septembre', 'abbr' => 'Sep.'),
        10 => array('nom' => 'Octobre', 'abbr' => 'Oct.'),
        11 => array('nom' => 'Novembre', 'abbr' => 'Nov.'),
        12 => array('nom' => 'Décembre', 'abbr' => 'Déc.')
    );


    public function date(array $params, Smarty_Internal_Template $smarty_obj = null): string
    {
        return date($params['format'] ?? 'd/m/Y', strtotime($params['date']));
    }

    public function userDate(array $params, Smarty_Internal_Template $smarty_obj): string
    {
        $date = $params['date'];
        if (null === $date) {
            return '';
        }

        if (strpos(' ', $date) !== false) {
            $tmp = explode(' ', $date);
            $date = $tmp[0];
        }

        list($y, $m, $d) = explode('-', $date);

        $y = (int)$y;
        $m = (int)$m;
        $d = (int)$d;
        $day = (int)date('N', strtotime($date));

        return sprintf(
            '%s %s %s',
            self::DAYS[$day]['nom'],
            $d === 1 ? "1er" : $d,
            self::MONTHS[$m]['nom']
        );

    }

    public function month(array $params, Smarty_Internal_Template $smarty_obj): string
    {
        $month = $params['month'];
        if (str_contains($month, '-')) {
            list($year, $month) = explode('-', $month);
        }
        return sprintf(
            '%s %s',
            self::MONTHS[(int)$month]['nom'],
            $year
        );
    }

    public function hour(array $params, Smarty_Internal_Template $smarty_obj): string
    {
        $hour = $params['hour'];
        if (str_contains($hour, ' ')) {
            list(, $hour) = explode(' ', $hour);
        }
        list($h, $i) = explode(':', $hour);

        return sprintf(
            '%sh%s',
            $h,
            $i
        );
    }

    public function price(array $params, Smarty_Internal_Template $smarty_obj): string
    {
        return sprintf('%s%s',
            number_format(floatval($params['price']) ?? 0, '2', ',', ' '),
            '&euro;'
        );
    }

    public function accountingPrice(array $params, Smarty_Internal_Template $smarty_obj): string
    {
        return number_format(floatval($params['price']) ?? 0, '2', ',', ' ');
    }

    public function percent(array $params, Smarty_Internal_Template $smarty_obj): string
    {
        return sprintf('%s%s',
            number_format(floatval($params['percent']) ?? 0, '0', ',', ''),
            '%'
        );
    }
}