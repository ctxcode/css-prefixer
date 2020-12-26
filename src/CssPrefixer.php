<?php

namespace CssPrefixer;

class Prefixer {

    public function prefixCss($css, $inScope = false) {

        $result = '';
        $i = 0;
        $selector = '';
        $maxI = strlen($css) - 1;
        $spacing = 0;
        $findIndent = true;
        $indent = '';

        $props = [];

        while ($i <= $maxI) {

            $c = $css[$i];

            if ($c === ' ' || $c === "\t") {
                $spacing++;
                $result .= $c;
                $i++;
                if ($findIndent) {
                    $indent .= $c;
                }
                continue;
            }
            if ($findIndent && strlen($indent) > 0) {
                $findIndent = false;
            }

            if ($c === "\n" || $c === "\r") {
                $spacing = 0;
                $result .= $c;
                $i++;
                continue;
            }

            // Comments
            if ($c === '/') {
                $nextC = $css[$i + 1] ?? null;
                // Single line comment
                if ($nextC === '/') {
                    // Comment
                    while ($i <= $maxI && $css[$i] !== "\n") {
                        $i++;
                    }
                    $i += 1;
                    continue;
                }
                // Multi line comment
                if ($nextC === '*') {
                    // Comment
                    while ($i <= $maxI - 1 && ($css[$i] !== "*" || $css[$i + 1] !== "/")) {
                        $i++;
                    }
                    $i += 2;
                    continue;
                }
            }

            if (!$inScope) {

                $buff = '';

                // Get selector
                $selector = '';
                while ($i <= $maxI && $css[$i] !== "{") {
                    $selector .= $css[$i];
                    $i++;
                }
                $i++;
                $buff .= $selector . '{';
                // Get body
                $body = '';
                $depth = 0;
                while ($i <= $maxI && ($css[$i] !== "}" || $depth > 0)) {
                    if ($css[$i] === "{") {$depth++;}
                    if ($css[$i] === "}") {$depth--;}
                    $body .= $css[$i];
                    $i++;
                }
                $i++;

                $scope = true;
                if (strpos($selector, '@keyframes') === 0) {
                    $scope = false;
                }

                $body = $this->prefixCss($body, $scope);
                $buff .= $body . '}';

                // Keyframes
                if (strpos($selector, '@keyframes') === 0) {
                    // foreach (['webkit', 'moz', 'o'] as $prefix) {
                    foreach (['webkit'] as $prefix) {
                        $newSelector = str_replace('@keyframes', '@-' . $prefix . '-keyframes', $selector);
                        $buff .= "\n";
                        $buff .= $newSelector . '{';
                        $buff .= $body . '}' . "\n";
                    }
                }

                $result .= $buff;

                continue;
            }

            if ($inScope) {

                $line = '';
                $prop = '';
                while ($i <= $maxI && $css[$i] !== ":") {
                    $prop .= $css[$i];
                    $i++;
                }
                $i++;
                $line .= $prop . ':';
                $value = '';
                while ($i <= $maxI && $css[$i] !== ";") {
                    $value .= $css[$i];
                    $i++;
                }
                $i++;
                $line .= $value . ';';

                $propTrim = trim($prop);
                $valueTrim = trim($value);

                if (!isset($props[$propTrim])) {
                    $props[$propTrim] = [];
                }
                $props[$propTrim][] = $valueTrim;

                // if (isset(static::$_fixes[$propTrim][$valueTrim])) {

                //     $space = '';
                //     for ($x = 0; $x < $spacing; $x++) {
                //         $space .= ' ';
                //     }

                //     $x = '';
                //     foreach (static::$_fixes[$propTrim][$valueTrim] as $newProp => $newValue) {
                //         $x .= $newProp . ': ' . $newValue . ";\n" . $space;
                //     }
                //     $line = $x . $line;
                // } elseif (isset(static::$_fixes[$propTrim]['_*'])) {

                //     $space = '';
                //     for ($x = 0; $x < $spacing; $x++) {
                //         $space .= ' ';
                //     }

                //     $x = '';
                //     foreach (static::$_fixes[$propTrim]['_*'] as $newProp) {
                //         $x .= $newProp . ': ' . $valueTrim . ";\n" . $space;
                //     }
                //     $line = $x . $line;
                // }

                $result .= $line;

                continue;
            }

            $i++;
        }

        if ($inScope) {
            // Add prefixes
            $table = &Table::$table;

            $indentAfter = '';
            $i = $maxI;
            while ($i >= 0) {
                $c = $css[$i];
                if ($c != ' ' && $c != "\t") {
                    break;
                }
                $indentAfter .= $c;
                $i--;
            }

            $result = substr($result, 0, strlen($result) - strlen($indentAfter));

            foreach ($props as $propName => $values) {
                foreach ($values as $value) {
                    if (isset($table[$propName])) {
                        $b = &$table[$propName]['b'];
                        foreach ($b as $browser => $bData) {
                            $checkPropName = $propName;
                            if (isset($bData['pre'])) {
                                $checkPropName = $bData['pre'] . $checkPropName;
                            }

                            $checkValue = $value;
                            if (isset($bData['vals'][$value]['pre'])) {
                                $checkValue = $bData['vals'][$value]['pre'] . $value;
                            }

                            if (!isset($props[$checkPropName])) {
                                $props[$checkPropName] = [];
                            }
                            if (!in_array($checkValue, $props[$checkPropName])) {
                                // Add to css
                                $props[$checkPropName][] = $checkValue;
                                $result .= "$indent$checkPropName: $checkValue;\n";
                            }

                        }
                    }
                }
            }

            $result .= $indentAfter;
        }

        return $result;
    }

}