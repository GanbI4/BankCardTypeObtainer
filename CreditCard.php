<?php

class CreditCard {
    const AMEX = 1;
    const CHINA_UNION_PAY = 2;
    const DINERS_CLUB = 3;
    const DISCOVER_CARD = 6;
    const INTERPAYMENT = 7;
    const INSTAPAYMENT = 8;
    const JCB = 7;
    const MAESTRO = 8;
    const DANKORT = 9;
    const MASTERCARD = 10;
    const VISA = 11;
    const UATP = 12;
    const UNKNOWN = 14;

    public static $rules = array(
        /* American express.     Length 15                   IIN ranges: 34, 37 */
        self::AMEX            => ['length' => '/^\d{15}$/',    'pattern' => '/^(34|37)\d+/' ],

        /* China UnionPay        Length 16 - 19              IIN ranges: 62 */
        self::CHINA_UNION_PAY => ['length' => '/^\d{16,19}$/', 'pattern' => '/^62\d+/' ],

        /* Diners Club           Length 14 - 16              IIN ranges: 300-305,309,36,38-39,54,55 */
        self::DINERS_CLUB     => ['length' => '/^\d{14,16}$/', 'pattern' => '/^(3(0[0-59]|[689])|5[45])\d+/' ],

        /* InterPayment          Length 16 - 19              IIN ranges: 636 */
        self::INTERPAYMENT    => ['length' => '/^\d{16,19}$/', 'pattern' => '/^636\d+/' ],

        /* InstaPayment          Length 16                   IIN ranges: 637 - 639 */
        self::INSTAPAYMENT    => ['length' => '/^\d{16}$/',    'pattern' => '/^63[7-9]\d+/' ],

        /* JCB                   Length 16                   IIN ranges: 3528 - 3589 */
        self::JCB             => ['length' => '/^\d{16}$/',    'pattern' => '/^35(2[89]|[3-8]\d)\d+/' ],

        /* Dankort               Length 16                   IIN ranges: 5019  */
        self::DANKORT         => ['length' => '/^\d{16}$/',    'pattern' => '/^5019\d+/' ],

        /* Maestro               Length 12 - 19              IIN ranges: 50, 56 - 69 */
        self::MAESTRO         => ['length' => '/^\d{12,19}$/', 'pattern' => '/^(50|(5[6-9]|6\d))\d+/' ],

        /* MasterCard            Length 16                   IIN ranges: 51 - 55 */
        self::MASTERCARD      => ['length' => '/^\d{16}$/',    'pattern' => '/^5[1-5]\d+/' ],

        /* MasterCard            Length 13 - 16              IIN ranges: 4 */
        self::VISA            => ['length' => '/^\d{13,16}$/', 'pattern' => '/^4\d+/' ],

        /* UATP                  Length 15                   IIN ranges: 1 */
        self::UATP            => ['length' => '/^\d{15}$/',    'pattern' => '/^1\d+/' ]
    );

    public static function verifyLuhn($number)
    {
        $sum = 0;
        $number = strrev($number);
        for ($i = 0; $i < strlen($number); ++$i) {
            $current = substr($number, $i, 1);
            if ($i % 2 == 1) {
                $current *= 2;
                if ($current > 9) {
                    $firstDigit = $current % 10;
                    $secondDigit = ($current - $firstDigit) / 10;
                    $current = $firstDigit + $secondDigit;
                }
            }
            $sum += $current;
        }
        return ($sum % 10 == 0);
    }

    public static function obtainCardType($number)
    {
        if (!self::verifyLuhn($number)) {
            return 0;
        }
        /* Dirty Hack to not use ugly regexp for Discover card (6011, 622126-622925, 644-649, 65) */
        $first_six = (int) substr($number, 0, 6);

        $discover_pattern = '/^6((011|5)|(4[4-9])|5)\d+/';
        $discover_length  = '/^\d{16}$/';

        if (preg_match($discover_pattern, $number) || (622126 <= $first_six && $first_six <= 622925 )) {
            return preg_match($discover_length, $number) ? self::DISCOVER_CARD : 0;
        }

        foreach (self::$rules as $type => $rule)
        {
            if (preg_match($rule['pattern'], $number)) {
                return preg_match($rule['length'], $number) ? $type : 0;
            }
        }

        return self::UNKNOWN;
    }

}
