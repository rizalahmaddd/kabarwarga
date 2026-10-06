<?php

use App\Models\Setting;

if (! function_exists('rupiah')) {
    function rupiah(int|float|null $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    function setting(string $key): string
    {
        return Setting::read($key);
    }
}

if (! function_exists('clean_phone')) {
    function clean_phone(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }
}

if (! function_exists('terbilang')) {
    function terbilang(int|float $number): string
    {
        $number = (int) abs($number);
        if ($number === 0) {
            return 'nol';
        }

        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $words[$number];
        }
        if ($number < 20) {
            return terbilang($number - 10).' belas';
        }
        if ($number < 100) {
            $rem = $number % 10;

            return trim(terbilang(intdiv($number, 10)).' puluh '.($rem ? $words[$rem] : ''));
        }
        if ($number < 200) {
            return trim('seratus '.($number > 100 ? terbilang($number - 100) : ''));
        }
        if ($number < 1000) {
            return trim(terbilang(intdiv($number, 100)).' ratus '.($number % 100 ? terbilang($number % 100) : ''));
        }
        if ($number < 2000) {
            return trim('seribu '.($number > 1000 ? terbilang($number - 1000) : ''));
        }
        if ($number < 1000000) {
            return trim(terbilang(intdiv($number, 1000)).' ribu '.($number % 1000 ? terbilang($number % 1000) : ''));
        }
        if ($number < 1000000000) {
            return trim(terbilang(intdiv($number, 1000000)).' juta '.($number % 1000000 ? terbilang($number % 1000000) : ''));
        }
        if ($number < 1000000000000) {
            return trim(terbilang(intdiv($number, 1000000000)).' milyar '.($number % 1000000000 ? terbilang($number % 1000000000) : ''));
        }

        return (string) $number;
    }
}
