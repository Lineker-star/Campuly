<?php

/*
 * Build a PHPUnit configuration that runs the suite on another database.
 *
 *     php tests/phpunit-for-driver.php pgsql     # writes phpunit.pgsql.xml
 *     php tests/phpunit-for-driver.php sqlite    # writes phpunit.sqlite.xml
 *     php tests/phpunit-for-driver.php mysql     # writes phpunit.mysql.xml
 *
 * phpunit.xml forces Sail's MySQL settings in both <env> and <server>, so the
 * suite can never reach a real database by accident. This copies it and puts
 * the values from .env.testing.<driver> (.env.testing for mysql) in their place.
 * A DB_* variable set in the shell wins over the file, for example
 * DB_PORT=5433 on a machine whose PostgreSQL listens there.
 */

$driver = $argv[1] ?? '';

if (!in_array($driver, ['mysql', 'pgsql', 'sqlite'], true)) {
    fwrite(STDERR, "Usage: php tests/phpunit-for-driver.php mysql|pgsql|sqlite\n");
    exit(1);
}

$root = dirname(__DIR__);
$envFile = $root.'/'.($driver === 'mysql' ? '.env.testing' : ".env.testing.{$driver}");

if (!is_file($envFile)) {
    fwrite(STDERR, "Missing {$envFile}\n");
    exit(1);
}

$names = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
$values = [];

foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    if (preg_match('/^(DB_[A-Z_]+)=(.*)$/', trim($line), $match) === 1) {
        $values[$match[1]] = trim($match[2], " \t\"'");
    }
}

$names = array_values(array_unique([...$names, ...array_keys($values)]));

foreach ($names as $name) {
    $fromShell = getenv($name);

    if ($fromShell !== false) {
        $values[$name] = $fromShell;
    }
}

if ($driver === 'sqlite') {
    $path = $values['DB_DATABASE'] ?? 'database/testing.sqlite';

    if ($path !== ':memory:' && !preg_match('#^([A-Za-z]:)?[/\\\\]#', $path)) {
        $path = $root.'/'.$path;
    }

    if ($path !== ':memory:' && !is_file($path)) {
        touch($path);
    }

    $values['DB_DATABASE'] = str_replace('\\', '/', $path);
}

$document = new DOMDocument;
$document->preserveWhiteSpace = true;
$document->load($root.'/phpunit.xml');
$xpath = new DOMXPath($document);

$php = $xpath->query('//php')?->item(0);

if (!$php instanceof DOMElement) {
    fwrite(STDERR, "phpunit.xml has no <php> section\n");
    exit(1);
}

foreach ($values as $name => $value) {
    foreach (['env', 'server'] as $element) {
        $nodes = $xpath->query("//php/{$element}[@name='{$name}']");

        if ($nodes === false || $nodes->length === 0) {
            $node = $document->createElement($element);
            $node->setAttribute('name', $name);

            if ($element === 'env') {
                $node->setAttribute('force', 'true');
            }

            $php->appendChild($document->createTextNode('    '));
            $php->appendChild($node);
            $php->appendChild($document->createTextNode("\n    "));
            $nodes = [$node];
        }

        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $node->setAttribute('value', $value);
            }
        }
    }
}

$target = "phpunit.{$driver}.xml";
$document->save($root.'/'.$target);

echo $target.PHP_EOL;
