<?php

class CssPrefixer {

    static $_fixes = [
        "border-radius" => [
            "_*" => ["-webkit-border-radius", "-moz-border-radius"],
        ],
        "user-select" => [
            "_*" => ["-webkit-user-select", "-moz-user-select", "-ms-user-select"],
        ],
        "transform" => [
            "_*" => ["-webkit-transform", "-moz-transform", "-o-transform"],
        ],
        "box-sizing" => [
            "_*" => ["-webkit-box-sizing", "-moz-box-sizing"],
        ],
        "box-shadow" => [
            "_*" => ["-webkit-box-shadow", "-moz-box-shadow"],
        ],
        "display" => [
            "flex" => ["display" => "-ms-flexbox"],
            "inline-flex" => ["display" => "-ms-inline-flexbox"],
            "block" => ["-webkit-appearance" => "none"],
            "grid" => ["display" => "-ms-grid"],
        ],
        "background-origin" => [
            "_*" => ["-moz-background-size", "-o-background-size"],
        ],
        "background-size" => [
            "_*" => ["-moz-background-size", "-o-background-size"],
        ],
        "transition" => [
            "_*" => ["-webkit-transition", "-o-transition"],
        ],
        "animation" => [
            "_*" => ["-webkit-animation", "-moz-animation", "-o-animation"],
        ],
        "flex-direction" => [
            "row" => ["-webkit-box-orient" => "horizontal", "-webkit-box-direction" => "normal", "-webkit-flex-direction" => "row", "-moz-box-orient" => "horizontal", "-moz-box-direction" => "normal", "-ms-flex-direction" => "row"],
            "column" => ["-webkit-box-orient" => "vertical", "-webkit-box-direction" => "normal", "-webkit-flex-direction" => "column", "-moz-box-orient" => "vertical", "-moz-box-direction" => "normal", "-ms-flex-direction" => "column"],
        ],
        "flex-wrap" => [
            "_*" => ["-webkit-flex-wrap", "-ms-flex-wrap"],
        ],
        "justify-content" => [
            "_*" => ["-webkit-box-pack", "-webkit-justify-content", "-moz-box-pack", "-ms-flex-pack"],
        ],
        "align-items" => [
            "_*" => ["-webkit-box-align", "-webkit-align-items", "-moz-box-align", "-ms-flex-align"],
        ],
        "border-top-left-radius" => [
            "_*" => ["-webkit-border-top-left-radius", "-moz-border-radius-topleft"],
        ],
        "border-bottom-left-radius" => [
            "_*" => ["-webkit-border-bottom-left-radius", "-moz-border-radius-bottomleft"],
        ],
        "text-overflow" => [
            "_*" => ["-o-text-overflow"],
        ],
        "border-top-right-radius" => [
            "_*" => ["-webkit-border-top-right-radius", "-moz-border-radius-topright"],
        ],
        "border-bottom-right-radius" => [
            "_*" => ["-webkit-border-bottom-right-radius", "-moz-border-radius-bottomright"],
        ],
        "transform-origin" => [
            "_*" => ["-webkit-transform-origin", "-moz-transform-origin", "-ms-transform-origin", "-o-transform-origin"],
        ],
        "animation-direction" => [
            "_*" => ["-webkit-animation-direction", "-moz-animation-direction", "-o-animation-direction"],
        ],
        "object-fit" => [
            "_*" => ["-o-object-fit"],
        ],
        "animation-fill-mode" => [
            "_*" => ["-webkit-animation-fill-mode", "-moz-animation-fill-mode", "-o-animation-fill-mode"],
        ],
        "flex-basis" => [
            "_*" => ["-webkit-flex-basis", "-ms-flex-preferred-size"],
        ],
        "align-self" => [
            "_*" => ["-webkit-align-self", "-ms-flex-item-align"],
        ],
        "white-space" => [
            "nowrap" => ["-webkit-overflow-scrolling" => "touch"],
        ],
        "cursor" => [
            "zoom-in" => ["cursor" => "-moz-zoom-in"],
            "grab" => ["cursor" => "-moz-grab"],
            "grabbing" => ["cursor" => "-moz-grabbing"],
        ],
        "position" => [
            "relative" => ["-webkit-overflow-scrolling" => "touch"],
        ],
        "align-content" => [
            "_*" => ["-webkit-align-content", "-ms-flex-line-pack"],
        ],
        "filter" => [
            "_*" => ["-webkit-filter"],
        ],
        "touch-action" => [
            "_*" => ["-ms-touch-action"],
        ],
        "outline" => [
            "none" => ["-webkit-text-size-adjust" => "100%", "-webkit-backface-visibility" => "hidden"],
        ],
        "will-change" => [
            "opacity" => ["-webkit-backface-visibility" => "hidden"],
            "transform" => ["-webkit-backface-visibility" => "hidden"],
        ],
        "pointer-events" => [
            "_*" => ["-webkit-pointer-events", "-moz-pointer-events"],
        ],
        "visibility" => [
            "visible" => ["-webkit-font-smoothing" => "auto"],
        ],
    ];

    public function prefixCss($css, $inScope = false) {

        $result = '';
        $i = 0;
        $selector = '';
        $maxI = strlen($css);
        $spacing = 0;

        while ($i < $maxI) {

            $c = $css[$i];

            if ($c === ' ') {
                $spacing++;
                $result .= $c;
                $i++;
                continue;
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

                $buff = '';

                // Get selector
                $selector = '';
                while ($i < $maxI && $css[$i] !== "{") {
                    $selector .= $css[$i];
                    $i++;
                }
                $i++;
                $buff .= $selector . '{';
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
                if (strpos($selector, '@keyframes') === 0) {
                    $scope = false;
                }

                $body = $this->prefixCss($body, $scope);
                $buff .= $body . '}';

                // Keyframes
                if (strpos($selector, '@keyframes') === 0) {
                    foreach (['webkit', 'moz', 'o'] as $prefix) {
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
                while ($i < $maxI && $css[$i] !== ":") {
                    $prop .= $css[$i];
                    $i++;
                }
                $i++;
                $line .= $prop . ':';
                $value = '';
                while ($i < $maxI && $css[$i] !== ";") {
                    $value .= $css[$i];
                    $i++;
                }
                $i++;
                $line .= $value . ';';

                $propTrim = trim($prop);
                $valueTrim = trim($value);

                if (isset(static::$_fixes[$propTrim][$valueTrim])) {

                    $space = '';
                    for ($x = 0; $x < $spacing; $x++) {
                        $space .= ' ';
                    }

                    $x = '';
                    foreach (static::$_fixes[$propTrim][$valueTrim] as $newProp => $newValue) {
                        $x .= $newProp . ': ' . $newValue . ";\n" . $space;
                    }
                    $line = $x . $line;
                } elseif (isset(static::$_fixes[$propTrim]['_*'])) {

                    $space = '';
                    for ($x = 0; $x < $spacing; $x++) {
                        $space .= ' ';
                    }

                    $x = '';
                    foreach (static::$_fixes[$propTrim]['_*'] as $newProp) {
                        $x .= $newProp . ': ' . $valueTrim . ";\n" . $space;
                    }
                    $line = $x . $line;
                }

                $result .= $line;

                continue;
            }

            $i++;
        }

        return $result;
    }

}