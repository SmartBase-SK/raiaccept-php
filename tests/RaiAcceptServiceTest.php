<?php

namespace Raiaccept\RaiacceptApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Raiaccept\RaiacceptApiClient\RaiAcceptService;

class RaiAcceptServiceTest extends TestCase
{
    private const GATEWAY_ADDRESS_PATTERN = '/^[\p{L}\d\'() .,#\/-]*$/u';

    public function testPrecomposedSerbianMerchantAddressIsUnchanged(): void
    {
        $input = 'Bulevar kralja Petra I 10';

        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame($input, $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    public function testSerbianLatinAddressWithDiacriticsIsGatewayValid(): void
    {
        $input = 'Ulica Vožda Karađorđa 5';

        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame('Ulica Vozda Karađorđa 5', $result);
        $this->assertStringContainsString('đ', $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    /**
     * @dataProvider addressGatewayComplianceProvider
     */
    public function testAddressOutputMatchesGatewayCharset(string $input, string $expected): void
    {
        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame($expected, $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    public function addressGatewayComplianceProvider(): array
    {
        return [
            'precomposed serbian merchant address' => [
                'Bulevar kralja Petra I 10',
                'Bulevar kralja Petra I 10',
            ],
            'combining circumflex in serbian address' => [
                'Bulevar kral' . "\u{0302}" . 'a Petra I 10',
                'Bulevar krala Petra I 10',
            ],
            'cyrillic serbian address' => [
                'Бulevar краља Петра I 10',
                'Bulevar krala Petra I 10',
            ],
            'gateway punctuation in address' => [
                "Test #5 (unit) 1/2 - 'quote'",
                "Test #5 (unit) 1/2 - 'quote'",
            ],
        ];
    }

    public function testCombiningCircumflexInSerbianAddressIsRemoved(): void
    {
        $input = 'Bulevar kral' . "\u{0302}" . 'a Petra I 10';

        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame('Bulevar krala Petra I 10', $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    public function testCyrillicSerbianAddressTransliteratesToLatin(): void
    {
        $input = 'Бulevar краља Петра I 10';

        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame('Bulevar krala Petra I 10', $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    public function testGatewayAllowedPunctuationIsPreserved(): void
    {
        $input = "Test #5 (unit) 1/2 - 'quote'";

        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame("Test #5 (unit) 1/2 - 'quote'", $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    public function testEmailAtSignIsPreserved(): void
    {
        $result = RaiAcceptService::transliterate_and_limit_length('foo@bar.com', 255);

        $this->assertSame('foo@bar.com', $result);
    }

    public function testUnsupportedCharactersAreStripped(): void
    {
        $input = "Street & Name; <script>|`\\";

        $result = RaiAcceptService::transliterate_and_limit_length($input, 50);

        $this->assertSame('Street Name script', $result);
        $this->assertMatchesRegularExpression(self::GATEWAY_ADDRESS_PATTERN, $result);
    }

    public function testWhitespaceIsNormalized(): void
    {
        $input = "  Bulevar   kralja   Petra   I   10  ";

        $result = RaiAcceptService::transliterate($input);

        $this->assertSame('Bulevar kralja Petra I 10', $result);
    }

    public function testEmptyStringReturnsNullFromTransliterateAndLimitLength(): void
    {
        $this->assertNull(RaiAcceptService::transliterate_and_limit_length('   ', 50));
    }

    public function testLengthLimitIsApplied(): void
    {
        $input = 'Bulevar kralja Petra I 10 extra long suffix';

        $result = RaiAcceptService::transliterate_and_limit_length($input, 20);

        $this->assertLessThanOrEqual(20, mb_strlen($result));
    }
}
