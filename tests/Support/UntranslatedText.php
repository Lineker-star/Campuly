<?php

namespace Tests\Support;

/**
 * Find (and optionally wrap) visible text in a Blade view that does not go
 * through __(). Used by tests/Feature/I18n/UntranslatedTextTest.php and by
 * developers translating a module (docs/I18N.md).
 *
 * It looks at text between tags and at the attributes people read or hear:
 * aria-label, placeholder, title, alt, and label / description props of
 * <april:…> and <x-…> components. Blade echoes, directives, comments,
 * scripts and styles are ignored.
 */
final class UntranslatedText
{
    private const ATTRIBUTES = ['aria-label', 'placeholder', 'title', 'alt'];

    private const COMPONENT_PROPS = ['label', 'description', 'heading', 'placeholder', 'title'];

    /**
     * Report every untranslated text in the view.
     *
     * @return list<array{line: int, kind: string, text: string, fixable: bool}>
     */
    public static function find(string $blade): array
    {
        $masked = self::mask($blade);
        $found = [];

        // Text between tags.
        preg_match_all('/>([^<>]*)</u', $masked, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as [$text, $offset]) {
            $visible = trim(str_replace("\u{E000}", '', $text));

            if (self::isWords($visible)) {
                $found[] = [
                    'line' => substr_count($blade, "\n", 0, $offset) + 1,
                    'kind' => 'text',
                    'text' => trim(preg_replace('/\s+/u', ' ', self::unmask($blade, $text, $offset)) ?? ''),
                    'fixable' => !self::hasHiddenCode(self::unmask($blade, $text, $offset))
                        && (!str_contains($text, "\u{E000}") || self::placeholders(self::unmask($blade, $text, $offset)) !== null),
                ];
            }
        }

        // Attributes on any tag, and readable props on components.
        preg_match_all('/<((?:x-|april:)?[A-Za-z][\w:.-]*)\b([^<>]*)>/u', $masked, $tags, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($tags as $tag) {
            $isComponent = str_starts_with($tag[1][0], 'x-') || str_starts_with($tag[1][0], 'april:');
            $names = $isComponent ? [...self::ATTRIBUTES, ...self::COMPONENT_PROPS] : self::ATTRIBUTES;

            preg_match_all('/(?<![:\w-])('.implode('|', array_map('preg_quote', $names)).')="([^"]*)"/u', $tag[2][0], $attributes, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            foreach ($attributes as $attribute) {
                $value = $attribute[2][0];

                if (str_contains($value, "\u{E000}") || !self::isWords($value)) {
                    continue;
                }

                $found[] = [
                    'line' => substr_count($blade, "\n", 0, $tag[2][1] + $attribute[0][1]) + 1,
                    'kind' => $attribute[1][0],
                    'text' => $value,
                    'fixable' => true,
                ];
            }
        }

        // @section('title', 'Literal text') — page titles, headings, error messages.
        foreach (self::sections($blade) as [$offset, , $text]) {
            $found[] = [
                'line' => substr_count($blade, "\n", 0, $offset) + 1,
                'kind' => 'section',
                'text' => $text,
                'fixable' => true,
            ];
        }

        usort($found, fn (array $a, array $b): int => $a['line'] <=> $b['line']);

        return $found;
    }

    /**
     * Sections whose value is a literal sentence, as [offset, length, text, name].
     *
     * @return list<array{0: int, 1: int, 2: string, 3: string}>
     */
    private static function sections(string $blade): array
    {
        $sections = [];
        preg_match_all("/@section\(\s*'([\w.-]+)'\s*,\s*'((?:[^'\\\\]|\\\\.)*)'\s*\)/u", $blade, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $text = str_replace("\\'", "'", $match[2][0]);

            if (in_array($match[1][0], ['code', 'back_url'], true) || !self::isWords($text)) {
                continue;
            }

            $sections[] = [$match[0][1], strlen($match[0][0]), $text, $match[1][0]];
        }

        return $sections;
    }

    /**
     * Turn "Words {{ $expr }} words" into one translatable sentence with
     * :placeholders, or null when the text holds directives, raw echoes or
     * comments and needs a person.
     */
    private static function placeholders(string $original): ?string
    {
        if (preg_match('/@[a-zA-Z]|\{!!|\{\{--/', $original) === 1) {
            return null;
        }

        preg_match('/^(\s*)(.*?)(\s*)$/su', $original, $parts);
        $sentence = '';
        $replace = [];
        $rest = $parts[2];

        while (preg_match('/\{\{\s*(.+?)\s*\}\}/su', $rest, $echo, PREG_OFFSET_CAPTURE) === 1) {
            $sentence .= substr($rest, 0, $echo[0][1]);
            $expression = $echo[1][0];
            $name = self::placeholderName($expression);
            $base = $name;
            $n = 2;

            while (isset($replace[$name]) && $replace[$name] !== $expression) {
                $name = $base.$n++;
            }

            $replace[$name] = $expression;
            $sentence .= ':'.$name;
            $rest = substr($rest, $echo[0][1] + strlen($echo[0][0]));
        }

        $sentence .= $rest;
        $sentence = html_entity_decode(preg_replace('/\s+/u', ' ', $sentence) ?? $sentence, ENT_QUOTES | ENT_HTML5);

        if (!self::isWords(preg_replace('/:\w+/u', '', $sentence) ?? '')) {
            return null;
        }

        $pairs = [];

        foreach ($replace as $name => $expression) {
            $pairs[] = "'$name' => $expression";
        }

        return $parts[1].'{{ __('.self::quote($sentence).', ['.implode(', ', $pairs).']) }}'.$parts[3];
    }

    /**
     * Keep the directives of a text where they are and wrap each run of
     * words between them; null when raw echoes or comments are involved.
     */
    private static function aroundDirectives(string $original): ?string
    {
        if (preg_match('/\{!!|\{\{--/', $original) === 1) {
            return null;
        }

        // Text, directive, text, directive, … — split by the directives' positions.
        preg_match_all('/(?<![\w.])@\w+(?:\s*(?<args>\((?:[^()]++|(?&args))*\)))?/u', $original, $directives, PREG_OFFSET_CAPTURE);
        $parts = [];
        $cursor = 0;

        foreach ($directives[0] as [$directive, $at]) {
            $parts[] = substr($original, $cursor, $at - $cursor);
            $parts[] = $directive;
            $cursor = $at + strlen($directive);
        }

        $parts[] = substr($original, $cursor);

        $result = '';
        $changed = false;

        foreach ($parts as $index => $part) {
            // Odd indexes are the directives themselves.
            if ($index % 2 === 1 || !self::isWords(preg_replace('/\{\{.*?\}\}/su', '', $part) ?? '')) {
                $result .= $part;

                continue;
            }

            $wrapped = str_contains($part, '{{')
                ? self::placeholders($part)
                : (function (string $text): string {
                    preg_match('/^(\s*)(.*?)(\s*)$/su', $text, $m);

                    return $m[1].'{{ __('.self::quote(html_entity_decode(preg_replace('/\s+/u', ' ', $m[2]) ?? $m[2], ENT_QUOTES | ENT_HTML5)).') }}'.$m[3];
                })($part);

            if ($wrapped === null) {
                return null;
            }

            $result .= $wrapped;
            $changed = true;
        }

        return $changed ? $result : null;
    }

    /**
     * Whether the original text holds a directive, a comment or a raw echo.
     */
    private static function hasHiddenCode(string $original): bool
    {
        return preg_match('/(?<![\w.])@[a-zA-Z]|\{!!|\{\{--/', $original) === 1;
    }

    /**
     * A readable placeholder name for an echoed expression.
     */
    private static function placeholderName(string $expression): string
    {
        $map = [
            '/^(?:money_text|money)\(/' => 'amount',
            '/^local_date\(/' => 'date',
            '/^(?:mb_)?strtolower\(\s*school_terms?\(|^school_terms?\(|^ucfirst\(\s*school_terms?\(/' => 'term',
            "/^config\\('app\\.name'\\)/" => 'app',
            '/^(?:count|\$\w+->count\(\)|.*->count\(\))/' => 'count',
            '/^percent_local\(/' => 'percent',
        ];

        foreach ($map as $pattern => $name) {
            if (preg_match($pattern, $expression) === 1) {
                return $name;
            }
        }

        $inner = preg_replace('/^number_local\((.*?)(?:,.*)?\)$/s', '$1', $expression) ?? $expression;
        preg_match_all('/[A-Za-z_][A-Za-z0-9_]*/', $inner, $words);
        $skip = ['this', 'fn', 'null', 'true', 'false', 'id', 'value', 'get', 'first', 'name_for', 'e'];
        $candidates = array_values(array_filter($words[0], fn (string $w): bool => !in_array(strtolower($w), $skip, true)));
        $last = end($candidates) ?: 'value';
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $last) ?? $last);

        return preg_replace('/[^a-z0-9_]/', '', $snake) ?: 'value';
    }

    /**
     * Wrap the plain texts and attributes in __() and return the new view.
     */
    public static function fix(string $blade): string
    {
        $masked = self::mask($blade);
        $edits = [];

        preg_match_all('/>([^<>]*)</u', $masked, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as [$text, $offset]) {
            if (!self::isWords(trim(str_replace("\u{E000}", '', $text)))) {
                continue;
            }

            // Directives inside the text stay; the words between them are wrapped piece by piece.
            if (self::hasHiddenCode(substr($blade, $offset, strlen($text)))) {
                $split = self::aroundDirectives(substr($blade, $offset, strlen($text)));

                if ($split !== null) {
                    $edits[] = [$offset, strlen($text), $split];
                }

                continue;
            }

            if (str_contains($text, "\u{E000}")) {
                $mixed = self::placeholders(substr($blade, $offset, strlen($text)));

                if ($mixed !== null) {
                    $edits[] = [$offset, strlen($text), $mixed];
                }

                continue;
            }

            preg_match('/^(\s*)(.*?)(\s*)$/su', $text, $parts);
            $words = preg_replace('/\s+/u', ' ', $parts[2]) ?? $parts[2];
            $edits[] = [$offset, strlen($text), $parts[1].'{{ __('.self::quote(html_entity_decode($words, ENT_QUOTES | ENT_HTML5)).') }}'.$parts[3]];
        }

        preg_match_all('/<((?:x-|april:)?[A-Za-z][\w:.-]*)\b([^<>]*)>/u', $masked, $tags, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($tags as $tag) {
            $isComponent = str_starts_with($tag[1][0], 'x-') || str_starts_with($tag[1][0], 'april:');
            $names = $isComponent ? [...self::ATTRIBUTES, ...self::COMPONENT_PROPS] : self::ATTRIBUTES;

            preg_match_all('/(?<![:\w-])('.implode('|', array_map('preg_quote', $names)).')="([^"]*)"/u', $tag[2][0], $attributes, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            foreach ($attributes as $attribute) {
                $value = $attribute[2][0];

                if (str_contains($value, "\u{E000}") || !self::isWords($value)) {
                    continue;
                }

                $name = $attribute[1][0];
                $decoded = self::quote(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));
                // A component prop takes PHP; an HTML attribute takes an echo.
                $replacement = $isComponent && in_array($name, self::COMPONENT_PROPS, true) && !in_array($name, self::ATTRIBUTES, true)
                    ? ':'.$name.'="__('.$decoded.')"'
                    : $name.'="{{ __('.$decoded.') }}"';
                $edits[] = [$tag[2][1] + $attribute[0][1], strlen($attribute[0][0]), $replacement];
            }
        }

        foreach (self::sections($blade) as [$offset, $length, $text, $name]) {
            $edits[] = [$offset, $length, "@section('$name', __(".self::quote($text).'))'];
        }

        usort($edits, fn (array $a, array $b): int => $b[0] <=> $a[0]);

        foreach ($edits as [$offset, $length, $replacement]) {
            $blade = substr_replace($blade, $replacement, $offset, $length);
        }

        return $blade;
    }

    /**
     * Replace everything that is not literal markup with same-length filler,
     * so offsets still point into the original view. Echoes become U+E000
     * (a "something dynamic is here" marker); comments, directives, scripts
     * and styles become spaces.
     */
    private static function mask(string $blade): string
    {
        // Byte for byte (no /u): offsets in the mask must match the original view.
        $blank = fn (array $m): string => preg_replace('/[^\n]/', ' ', $m[0]) ?? $m[0];
        $marker = fn (array $m): string => "\u{E000}".str_repeat(' ', max(0, strlen($m[0]) - strlen("\u{E000}")));

        $blade = preg_replace_callback('/\{\{--.*?--\}\}/su', $blank, $blade) ?? $blade;
        $blade = preg_replace_callback('/@php\b.*?@endphp/su', $blank, $blade) ?? $blade;
        $blade = preg_replace_callback('/<(script|style)\b.*?<\/\1>/su', $blank, $blade) ?? $blade;
        $blade = preg_replace_callback('/\{\{.*?\}\}|\{!!.*?!!\}/su', $marker, $blade) ?? $blade;
        // @directive(...) with balanced parentheses, and bare @directives.
        // Not an "@" inside a word (person@example.com).
        $blade = preg_replace_callback('/(?<![\w.])@\w+\s*(\((?:[^()]++|(?1))*\))?/u', $blank, $blade) ?? $blade;
        // Alpine and Livewire attribute values are code, not text.
        $blade = preg_replace_callback('/\s(?:x-[\w:.-]+|wire:[\w.-]+|@[\w.:-]+|:[\w.-]+)="[^"]*"/u', $blank, $blade) ?? $blade;

        return $blade;
    }

    private static function unmask(string $blade, string $masked, int $offset): string
    {
        return substr($blade, $offset, strlen($masked));
    }

    /**
     * Whether the text holds a word a person reads (not only numbers, symbols or entities).
     */
    private static function isWords(string $text): bool
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);

        return preg_match('/\p{L}{2,}/u', $text) === 1;
    }

    private static function quote(string $text): string
    {
        return "'".str_replace(['\\', "'"], ['\\\\', "\\'"], $text)."'";
    }
}
