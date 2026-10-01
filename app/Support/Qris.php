<?php

namespace App\Support;

class Qris
{
    /**
     * Split a QRIS (EMVCo) payload into its top-level tags, or null when it is malformed.
     *
     * @return array<int, array{0: string, 1: string}>|null
     */
    public static function parse(string $payload): ?array
    {
        $tags = [];
        $offset = 0;
        $length = mb_strlen($payload);

        while ($offset < $length) {
            $id = mb_substr($payload, $offset, 2);
            $size = mb_substr($payload, $offset + 2, 2);

            if (! ctype_digit($id) || ! ctype_digit($size) || $offset + 4 + (int) $size > $length) {
                return null;
            }

            $tags[] = [$id, mb_substr($payload, $offset + 4, (int) $size)];
            $offset += 4 + (int) $size;
        }

        return $tags;
    }

    public static function isValid(string $payload): bool
    {
        $tags = self::parse($payload);

        if (! $tags || $tags[0] !== ['00', '01'] || end($tags)[0] !== '63') {
            return false;
        }

        return strtoupper(end($tags)[1]) === self::crc(mb_substr($payload, 0, -4));
    }

    public static function isStatic(string $payload): bool
    {
        return self::isValid($payload) && self::value($payload, '01') !== '12';
    }

    public static function value(string $payload, string $id): ?string
    {
        foreach (self::parse($payload) ?? [] as [$tag, $value]) {
            if ($tag === $id) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Turn a static QRIS into a dynamic one that asks for exactly this amount.
     */
    public static function withAmount(string $payload, int $amount): string
    {
        $tags = array_filter(
            self::parse($payload) ?? [],
            fn ($tag) => ! in_array($tag[0], ['54', '55', '56', '57', '63'], true),
        );

        $result = '';
        $amountAdded = false;

        foreach ($tags as [$id, $value]) {
            if ($id === '01') {
                $value = '12';
            }
            if (! $amountAdded && (int) $id > 54) {
                $result .= self::tag('54', (string) $amount);
                $amountAdded = true;
            }
            $result .= self::tag($id, $value);
        }

        if (! $amountAdded) {
            $result .= self::tag('54', (string) $amount);
        }

        $result .= '6304';

        return $result.self::crc($result);
    }

    private static function tag(string $id, string $value): string
    {
        return $id.str_pad((string) mb_strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    /**
     * CRC-16/CCITT-FALSE, the checksum QRIS stores in tag 63.
     */
    public static function crc(string $data): string
    {
        $crc = 0xFFFF;

        foreach (str_split($data) as $char) {
            $crc ^= ord($char) << 8;
            for ($i = 0; $i < 8; $i++) {
                $crc = $crc & 0x8000 ? ($crc << 1) ^ 0x1021 : $crc << 1;
                $crc &= 0xFFFF;
            }
        }

        return sprintf('%04X', $crc);
    }
}
