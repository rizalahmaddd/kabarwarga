<?php

namespace Tests\Unit;

use App\Support\Qris;
use PHPUnit\Framework\TestCase;

class QrisTest extends TestCase
{
    public static function staticPayload(): string
    {
        $body = '000201010211'
            .'26570011ID.DANA.WWW011893600915300000000102090000000010303UMI'
            .'51440014ID.CO.QRIS.WWW0215ID10200000000010303UMI'
            .'5204549953033605502015802ID5910KAS RT 04 6006MALANG61056514162070703A01'
            .'6304';

        return $body.Qris::crc($body);
    }

    public function test_crc_matches_ccitt_false_check_value(): void
    {
        $this->assertSame('29B1', Qris::crc('123456789'));
    }

    public function test_static_payload_is_recognised(): void
    {
        $payload = self::staticPayload();

        $this->assertTrue(Qris::isValid($payload));
        $this->assertTrue(Qris::isStatic($payload));
        $this->assertFalse(Qris::isValid(substr($payload, 0, -1).'0'));
        $this->assertFalse(Qris::isValid('hello world'));
    }

    public function test_amount_turns_static_into_dynamic(): void
    {
        $dynamic = Qris::withAmount(self::staticPayload(), 50007);

        $this->assertTrue(Qris::isValid($dynamic));
        $this->assertFalse(Qris::isStatic($dynamic));
        $this->assertSame('12', Qris::value($dynamic, '01'));
        $this->assertSame('50007', Qris::value($dynamic, '54'));
        $this->assertNull(Qris::value($dynamic, '55'));
        $this->assertSame('KAS RT 04 ', Qris::value($dynamic, '59'));
        $this->assertStringContainsString('530336054055000758', $dynamic);
    }
}
