<?php

namespace Tests\Feature\I18n;

use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Dates, money, numbers and percentages read naturally in each language.
 */
class FormattingHelpersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dateStyles(): array
    {
        return [
            'medium' => ['medium', '5 nov. 2026', 'Nov 5, 2026'],
            'short' => ['short', '05/11/2026', '11/05/2026'],
            'long' => ['long', '5 novembre 2026', 'November 5, 2026'],
            'day' => ['day', '5 nov.', 'Nov 5'],
            'month' => ['month', 'novembre 2026', 'November 2026'],
            'weekday' => ['weekday', 'jeudi 5 novembre 2026', 'Thursday, November 5, 2026'],
            'time' => ['time', '14:30', '14:30'],
            'datetime' => ['datetime', '5 nov. 2026, 14:30', 'Nov 5, 2026, 14:30'],
            'day_time' => ['day_time', '5 nov., 14:30', 'Nov 5, 14:30'],
            'weekday_time' => ['weekday_time', 'jeu. 5 nov., 14:30', 'Thu Nov 5, 14:30'],
        ];
    }

    #[DataProvider('dateStyles')]
    public function test_a_date_is_written_in_each_language(string $style, string $french, string $english): void
    {
        $this->assertSame($french, $this->in('fr', fn () => local_date('2026-11-05 14:30:00', $style)));
        $this->assertSame($english, $this->in('en', fn () => local_date('2026-11-05 14:30:00', $style)));
    }

    public function test_no_date_stays_no_date(): void
    {
        $this->assertNull(local_date(null));
        $this->assertSame('Not set', local_date(null) ?? 'Not set');
    }

    public function test_money_in_francs_has_no_decimals(): void
    {
        config(['app.currency' => 'XAF']);

        $this->assertSame('125 000 FCFA', $this->in('fr', fn () => money(125000)));
        $this->assertSame('FCFA 125,000', $this->in('en', fn () => money(125000)));
        $this->assertSame('125 001 FCFA', $this->in('fr', fn () => money(125000.6)));
        $this->assertSame('FCFA 125,000', $this->in('en', fn () => money_text(125000)));
    }

    public function test_money_takes_another_currency_and_brick_amounts(): void
    {
        config(['app.currency' => 'XAF']);

        $this->assertSame('12,50 €', $this->in('fr', fn () => money(12.5, 'EUR')));
        $this->assertSame('€12.50', $this->in('en', fn () => money(12.5, 'EUR')));
        $this->assertSame('FCFA 2,500', $this->in('en', fn () => money(Money::of(2500, 'XAF'))));
    }

    public function test_numbers_and_percentages_use_the_language_separators(): void
    {
        $this->assertSame('125 000', $this->in('fr', fn () => number_local(125000)));
        $this->assertSame('125,000', $this->in('en', fn () => number_local(125000)));
        $this->assertSame('12,5', $this->in('fr', fn () => number_local(12.5, 1)));
        $this->assertSame('12,5 %', $this->in('fr', fn () => percent_local(12.5)));
        $this->assertSame('12.5%', $this->in('en', fn () => percent_local(12.5)));
        $this->assertSame('100 %', $this->in('fr', fn () => percent_local(100)));
    }

    /**
     * Run in one language, with the spaces ICU uses in French made ordinary.
     *
     * @param  callable(): ?string  $callback
     */
    private function in(string $locale, callable $callback): ?string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            $result = $callback();
        } finally {
            app()->setLocale($previous);
        }

        return $result === null ? null : str_replace(["\u{202F}", "\u{00A0}"], ' ', $result);
    }
}
