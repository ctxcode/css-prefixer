<?php

$css = file_get_contents(__DIR__ . '/template.css');
$propAlternatives = [];

function parseCss($css, $inScope = false) {

    global $propAlternatives;

    $i = 0;
    $selector = '';
    $maxI = strlen($css);
    $spacing = 0;
    $valueByProp = [];
    $alternatives = [];

    while ($i < $maxI) {

        $c = $css[$i];

        if ($c === ' ') {
            $i++;
            continue;
        }

        if ($c === "\n" || $c === "\r") {
            $i++;
            continue;
        }

        // Comments
        if ($c === '/') {
            $nextC = $css[$i + 1] ?? null;
            // Single line comment
            if ($nextC === '/') {
                // Comment
                while ($i < $maxI && $css[$i] !== "\n") {
                    $i++;
                }
                $i += 1;
                continue;
            }
            // Multi line comment
            if ($nextC === '*') {
                // Comment
                while ($i < $maxI - 1 && ($css[$i] !== "*" || $css[$i + 1] !== "/")) {
                    $i++;
                }
                $i += 2;
                continue;
            }
        }

        if (!$inScope) {
            // Get selector
            $selector = '';
            while ($i < $maxI && $css[$i] !== "{") {
                $selector .= $css[$i];
                $i++;
            }
            $i++;
            $selector = trim($selector);
            // Get body
            $body = '';
            $depth = 0;
            while ($i < $maxI && ($css[$i] !== "}" || $depth > 0)) {
                if ($css[$i] === "{") {$depth++;}
                if ($css[$i] === "}") {$depth--;}
                $body .= $css[$i];
                $i++;
            }
            $i++;

            $scope = true;
            if ($selector[0] === '@') {
                $scope = false;
            }

            $body = parseCss($body, $scope);

            continue;
        }

        if ($inScope) {

            $prop = '';
            while ($i < $maxI && $css[$i] !== ":") {
                $prop .= $css[$i];
                $i++;
            }
            $i++;
            $value = '';
            while ($i < $maxI && $css[$i] !== ";") {
                $value .= $css[$i];
                $i++;
            }
            $i++;

            $propTrim = trim($prop);
            $valueTrim = trim($value);

            if ($propTrim[0] === '-' || $valueTrim[0] === '-') {
                if (!preg_match('/^-[0-9]/', $valueTrim)) {
                    $alternatives[$propTrim] = $valueTrim;
                }
            } else {
                if (count($alternatives) > 0) {
                    if (!isset($propAlternatives[$propTrim])) {
                        $propAlternatives[$propTrim] = [];
                    }
                    $propAlternatives[$propTrim][$valueTrim] = $alternatives;
                    $alternatives = [];
                }
            }

            continue;
        }

        $i++;
    }
}

parseCss($css);

// Filter stuff out
foreach ($propAlternatives as $prop => $values) {
    $keys = [];
    foreach ($values as $defValue => $altValues) {

        $allSame = true;
        foreach ($altValues as $val) {
            if ($defValue !== $val) {$allSame = false;}
        }
        if (!preg_match('/^[a-z-]+$/', $defValue)) {
            unset($propAlternatives[$prop][$defValue]);
        }
        if ($allSame) {
            $keys = array_keys($altValues);
            unset($propAlternatives[$prop][$defValue]);
        }
    }
    if ($keys) {
        $propAlternatives[$prop] = ['_*' => $keys];
    } elseif (count($propAlternatives[$prop]) == 0) {
        // echo "($prop)\n";
    }
}

foreach ($propAlternatives as $prop => $values) {
    if (count($values) == 0) {
        continue;
    }
    echo '"' . $prop . '" => [' . "\n";
    foreach ($values as $value => $prefixes) {
        echo '   "' . $value . '" => [';
        if ($value == '_*') {
            foreach ($prefixes as $prefix) {
                echo '"' . $prefix . '",';
            }
        } else {
            foreach ($prefixes as $key => $value) {
                echo '"' . $key . '" => "' . $value . '",';
            }
        }
        echo '],' . "\n";
    }
    echo '],';
    echo "\n";
}
