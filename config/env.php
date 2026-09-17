<?php
$envFile = __DIR__ . '/../.env';

if (!is_readable($envFile)) {
    return;
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
        continue;
    }

    [$name, $value] = explode('=', $line, 2);
    $name = trim($name);
    $value = trim($value);

    if ($name === '') {
        continue;
    }

    if (strlen($value) >= 2) {
        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        }
    }

    if (getenv($name) === false) {
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}