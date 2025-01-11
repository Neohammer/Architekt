<?php

class Css
{

    const BUTTON_ACTION = 'fs-5 shadow me-1';
    const BUTTON_ACTION_TAB = 'flex-sm-fill btn-lg btn-icon-4 me-2';
    const BUTTON_ACTION_SMALL = 'btn-icon-4 fs-6 btn-sm me-1';
    const BUTTON_ACTION_BOTTOM = 'fs-6 fs-md-5 btn-icon-4';
    const BUTTON_ACTION_MODAL = 'fs-5 btn-icon-4 w-75 mb-3';
    const BUTTON_ACTION_CARD_HEADER = 'fs-5 btn-icon-4 py-1 shadow-sm ';
    const USER_NAV_LINK = 'text-body ms-0';
    const BUTTON_SUBMIT = 'fs-6 fs-md-5 btn-icon-4 btn-submit fs-5';

    public static function buttonAction(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION, $itemClass);
    }

    private static function button(
        string $class,
        string $itemClass,
        bool   $mobileOnly = false,
        bool   $desktopOnly = false,
        bool   $rounded = true,
        bool   $highlight = true,
        bool   $center = false,
    ): string
    {
        return sprintf(
            'btn %s text-%s btn-%s %s %s %s',
            $rounded ? 'btn-rounded' : '',
            $center ? 'center' : 'start',
            $itemClass,
            $class,
            $mobileOnly ? self::displayMobile() : ($desktopOnly ? self::displayDesktop() : ''),
            $highlight ? '' : 'opacity-50'
        );
    }

    private static function displayMobile(): string
    {
        return 'd-lg-none';
    }

    private static function displayDesktop(): string
    {
        return 'd-none d-lg-inline';
    }

    public static function buttonActionDesktop(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION, $itemClass, desktopOnly: true);
    }

    public static function buttonActionMobile(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION, $itemClass, mobileOnly: true);
    }

    public static function buttonActionSmall(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_SMALL, $itemClass);
    }

    public static function buttonActionSmallMobile(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_SMALL, $itemClass, mobileOnly: true);
    }

    public static function buttonActionSmallDesktop(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_SMALL, $itemClass, desktopOnly: true);
    }

    public static function buttonActionBottom(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_BOTTOM, $itemClass);
    }

    public static function buttonActionBottomDesktop(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_BOTTOM, $itemClass, desktopOnly: true);
    }

    public static function buttonActionBottomMobile(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_BOTTOM, $itemClass, mobileOnly: true);
    }

    public static function buttonActionCardHeader(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_CARD_HEADER, $itemClass, rounded: false);
    }

    public static function buttonActionCardHeaderMobile(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_CARD_HEADER, $itemClass, mobileOnly: true, rounded: false);
    }

    public static function buttonActionCardHeaderDesktop(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_CARD_HEADER, $itemClass, desktopOnly: true, rounded: false);
    }

    public static function buttonActionModal(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_MODAL, $itemClass);
    }

    public static function buttonActionModalDesktop(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_MODAL, $itemClass, desktopOnly: true);
    }

    public static function buttonActionModalMobile(string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_MODAL, $itemClass, mobileOnly: true);
    }

    public static function buttonTab(bool $active, string $itemClass): string
    {
        return self::button(self::BUTTON_ACTION_TAB, $itemClass, rounded: false, highlight: $active, center: true);
    }

    public static function buttonSubmit(string $itemClass): string
    {
        return self::button(self::BUTTON_SUBMIT, $itemClass);
    }

    public static function buttonSubmitBottom(string $itemClass): string
    {
        return self::button(self::BUTTON_SUBMIT, $itemClass);
    }

    public static function table(string $itemClass, bool $datatable = true): string
    {
        return sprintf('table table-striped table-row-custom table-bordered table-%s w-100 %s', $itemClass, $datatable ? 'list' : '');
    }

    public static function legendTableItem(string $itemClass): string
    {
        return sprintf('table table-striped table-row-custom table-bordered table-%s shadow', $itemClass);
    }

    public static function tableRowDesktop(): string
    {
        return 'd-none d-lg-table-cell';
    }

    public static function userNavLink(): string
    {
        return self::USER_NAV_LINK;
    }
}