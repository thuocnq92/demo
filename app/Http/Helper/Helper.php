<?php
namespace App\Http\Helper;

class Helper {
    const CURRENCY_JPY = 'JPY';
    const CURRENCY_USD = 'USD';

    public static $symbols = [
        'JPY' => '¥',
        'USD' => '$'
    ];

    /**
     * @param int $price
     * @param null $currency
     * @return string
     */
    public static function currencyFormat($price = 0, $currency = null) {
        $symbol = $currency === null ? self::$symbols[self::CURRENCY_JPY] : self::$symbols[$currency];
        $price = doubleval($price);
        $price = number_format($price);

        return $symbol . $price;
    }
}