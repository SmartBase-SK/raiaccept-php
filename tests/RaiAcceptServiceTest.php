<?php

namespace Raiaccept\RaiacceptApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Raiaccept\RaiacceptApiClient\OrderFieldFormat;
use Raiaccept\RaiacceptApiClient\RaiAcceptService;

class RaiAcceptServiceTest extends TestCase
{
    public function testSanitizeOrderField(): void
    {
        $test_cases = [
            // format, limit, input, expected
            [OrderFieldFormat::PERSON_NAME, 32, 'John3 (Jr.) #1', 'John Jr.'],
            [OrderFieldFormat::PERSON_NAME, 32, "O'Brien-Smith", "O'Brien-Smith"],
            [OrderFieldFormat::PERSON_NAME, 32, 'Јован', 'Jovan'],
            [OrderFieldFormat::ADDRESS_LINE, 50, 'Bulevar kral' . "\u{0302}" . 'a Petra I 10', 'Bulevar krala Petra I 10'],
            [OrderFieldFormat::ADDRESS_LINE, 50, 'Bulevar kralja Petra I 10', 'Bulevar kralja Petra I 10'],
            [OrderFieldFormat::ADDRESS_LINE, 50, 'Булевар краља Петра I 10', 'Bulevar krala Petra I 10'],
            [OrderFieldFormat::ADDRESS_LINE, 50, 'Ulica Vožda Karađorđa 5', 'Ulica Vozda Karađorđa 5'],
            [OrderFieldFormat::ADDRESS_LINE, 50, "Test #5 (unit) 1/2 - 'quote'", "Test #5 (unit) 1/2 - 'quote'"],
            [OrderFieldFormat::ADDRESS_LINE, 50, 'Булевар краља 10', 'Bulevar krala 10'],
            [OrderFieldFormat::POSTAL_CODE, 16, '11000-SK #1', '11000-SK 1'],
            [OrderFieldFormat::POSTAL_CODE, 16, '110 00', '110 00'],
            [OrderFieldFormat::EMAIL, 255, 'user+tag@example.com', 'user+tag@example.com'],
            [OrderFieldFormat::EMAIL, 255, 'test@example.org', 'test@example.org'],
            [OrderFieldFormat::MERCHANT_REFERENCE, 150, 'raiaccept__42__abc-123', 'raiaccept__42__abc-123'],
            [OrderFieldFormat::MERCHANT_REFERENCE, 150, 'ORD-1001', 'ORD-1001'],
            [OrderFieldFormat::FREE_TEXT, 100, "Album\u{0007} name", 'Album name'],
        ];

        foreach ($test_cases as [$format, $limit, $input, $expected]) {
            $result = RaiAcceptService::sanitize_order_field($input, $format, $limit);

            $this->assertSame($expected, $result);

            if ($result !== null) {
                $this->assertTrue(
                    RaiAcceptService::matches_order_field_pattern($result, $format),
                    sprintf('Format %s produced invalid value: [%s]', $format, $result)
                );
            }
        }
    }

    public function testLegacyTransliterateAndLimitLength(): void
    {
        $test_cases = [
            // limit, input, expected
            [50, 'Bulevar kralja Petra I 10', 'Bulevar kralja Petra I 10'],
            [50, 'Bulevar kral' . "\u{0302}" . 'a Petra I 10', 'Bulevar krala Petra I 10'],
            [50, 'Булевар краља Петра I 10', 'Bulevar krala Petra I 10'],
            [50, "Test #5 (unit) 1/2 - 'quote'", "Test #5 (unit) 1/2 - 'quote'"],
            [50, "Street & Name; <script>|`\\", 'Street Name script'],
            [255, 'foo@bar.com', 'foo@bar.com'],
        ];

        foreach ($test_cases as [$limit, $input, $expected]) {
            $result = RaiAcceptService::transliterate_and_limit_length($input, $limit);

            $this->assertSame($expected, $result);

            if ($result !== null) {
                $this->assertTrue(
                    RaiAcceptService::matches_order_field_pattern($result, OrderFieldFormat::ADDRESS_LINE)
                        || RaiAcceptService::matches_order_field_pattern($result, OrderFieldFormat::EMAIL),
                    sprintf('Legacy sanitization produced unexpected value: [%s]', $result)
                );
            }
        }
    }

    public function testTransliterate(): void
    {
        $test_cases = [
            // input, expected
            ['  Bulevar   kralja   Petra   I   10  ', 'Bulevar kralja Petra I 10'],
        ];

        foreach ($test_cases as [$input, $expected]) {
            $this->assertSame($expected, RaiAcceptService::transliterate($input));
        }
    }

    public function testCleanPhoneNumber(): void
    {
        $test_cases = [
            // input, expected
            ['0908 396 747', '0908396747'],
            ['00421908123456', '+421908123456'],
            ['123', ''],
        ];

        foreach ($test_cases as [$input, $expected]) {
            $this->assertSame($expected, RaiAcceptService::clean_phone_number($input));
        }
    }

    public function testLegacyTransliterateAndLimitLengthReturnsNullForWhitespace(): void
    {
        $this->assertNull(RaiAcceptService::transliterate_and_limit_length('   ', 50));
    }

    public function testLegacyTransliterateAndLimitLengthAppliesMaxLength(): void
    {
        $result = RaiAcceptService::transliterate_and_limit_length(
            'Bulevar kralja Petra I 10 extra long suffix',
            20
        );

        $this->assertLessThanOrEqual(20, mb_strlen($result));
    }
}
