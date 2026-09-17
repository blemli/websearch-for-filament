<?php

it('ships the same keys in german and english', function () {
    $flatten = function (array $array, string $prefix = '') use (&$flatten): array {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys = is_array($value)
                ? [...$keys, ...$flatten($value, "{$prefix}{$key}.")]
                : [...$keys, "{$prefix}{$key}"];
        }

        return $keys;
    };

    $en = $flatten(require __DIR__ . '/../resources/lang/en/websearch.php');
    $de = $flatten(require __DIR__ . '/../resources/lang/de/websearch.php');

    expect($de)->toBe($en);
});
