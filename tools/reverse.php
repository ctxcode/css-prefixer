<?php

$css = file_get_contents(__DIR__ . '/template.css');
$propPrefixes = [];
$valuePrefixes = [];

function parseCss($css, $inScope = false) {

    global $propPrefixes;
    global $valuePrefixes;

    $i = 0;
    $selector = '';
    $maxI = strlen($css);
    $spacing = 0;

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

            if ($selector[0] === '@') {
                continue;
            }

            $body = parseCss($body, true);

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

            if ($propTrim[0] === '-') {
                $prefix = null;
                if ($propTrim[2] === '-') {
                    $prefix = '-o-';
                } elseif ($propTrim[3] === '-') {
                    $prefix = '-ms-';
                } elseif ($propTrim[4] === '-') {
                    $prefix = '-moz-';
                } elseif ($propTrim[7] === '-') {
                    $prefix = '-webkit-';
                }
                if ($prefix && substr($propTrim, 0, strlen($prefix)) === $prefix) {
                    $default = substr($propTrim, strlen($prefix));
                    if (!isset($propPrefixes[$default])) {
                        $propPrefixes[$default] = [];
                    }
                    $propPrefixes[$default][] = $prefix;
                }
            }

            if ($valueTrim[0] === '-') {
                $prefix = null;
                if (isset($valueTrim[2]) && $valueTrim[2] === '-') {
                    $prefix = '-o-';
                } elseif (isset($valueTrim[3]) && $valueTrim[3] === '-') {
                    $prefix = '-ms-';
                } elseif (isset($valueTrim[4]) && $valueTrim[4] === '-') {
                    $prefix = '-moz-';
                } elseif (isset($valueTrim[7]) && $valueTrim[7] === '-') {
                    $prefix = '-webkit-';
                }
                if ($prefix && substr($valueTrim, 0, strlen($prefix)) === $prefix) {
                    $default = substr($valueTrim, strlen($prefix));
                    if (preg_match('/^[a-z]+$/', $default)) {
                        if (!isset($valuePrefixes[$default])) {
                            $valuePrefixes[$default] = [];
                        }
                        $valuePrefixes[$default][] = $prefix;
                    }
                }
            }

            continue;
        }

        $i++;
    }
}

parseCss($css);

echo "-- PROPS\n";
foreach ($propPrefixes as $key => $prefixes) {
    $prefixes = array_unique($prefixes);
    sort($prefixes);
    echo '"' . $key . '" => ["' . (implode('","', $prefixes)) . '"]' . "\n";
}

echo "-- VALUES\n";
foreach ($valuePrefixes as $key => $prefixes) {
    $prefixes = array_unique($prefixes);
    sort($prefixes);
    echo '"' . $key . '" => ["' . (implode('","', $prefixes)) . '"]' . "\n";
}
