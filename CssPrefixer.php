<?php

class CssPrefixer {

    static $_moz_webkit = [
        "background-origin",
        "background-size",
        "border-image",
        "border-image-outset",
        "border-image-repeat",
        "border-image-source",
        "border-image-width",
        "border-radius",
        "box-shadow",
        "column-count",
        "column-gap",
        "column-rule",
        "column-rule-color",
        "column-rule-style",
        "column-rule-width",
        "column-width",
    ];
    static $_moz_webkit_ms = [
        "box-flex",
        "box-orient",
        "box-align",
        "box-ordinal-group",
        "box-flex-group",
        "box-pack",
        "box-direction",
        "box-lines",
        "box-sizing",
        "animation-duration",
        "animation-name",
        "animation-delay",
        "animation-direction",
        "animation-iteration-count",
        "animation-play-state",
        "animation-timing-function",
        "animation-fill-mode",
    ];
    static $_moz_webkit_ms_o = [
        "perspective",
        "transform",
        "transform-origin",
        "transition",
        "transition-property",
        "transition-duration",
        "transition-timing-function",
        "transition-delay",
        "user-select",
    ];
    static $_misc = [
        "background-clip",
        "border-bottom-left-radius",
        "border-bottom-right-radius",
        "border-top-left-radius",
        "border-top-right-radius",
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

            if (!$inScope) {
                // Get selector or skip comments

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
                        while ($i < $maxI - 1 && $css[$i] !== "*" && $css[$i + 1] !== "/") {
                            $i++;
                        }
                        $i += 2;
                        continue;
                    }
                }

                // Selector
                $selector = '';
                while ($i < $maxI && $css[$i] !== "{") {
                    $selector .= $css[$i];
                    $i++;
                }
                $i++;
                $result .= $selector . '{';
                // Get body
                $body = '';
                while ($i < $maxI && $css[$i] !== "}") {
                    $body .= $css[$i];
                    $i++;
                }
                $i++;
                $body = $this->prefixCss($body, true);
                $result .= $body . '}';

                continue;
            }

            if ($inScope) {

                $prop = '';
                while ($i < $maxI && $css[$i] !== ":") {
                    $prop .= $css[$i];
                    $i++;
                }
                $i++;
                $result .= $prop . ':';
                $value = '';
                while ($i < $maxI && $css[$i] !== ";") {
                    $value .= $css[$i];
                    $i++;
                }
                $i++;
                $result .= $value . ';';

                $propTrim = trim($prop);
                $prefixes = [];

                if (in_array($propTrim, static::$_moz_webkit)) {
                    $prefixes[] = '-moz-';
                    $prefixes[] = '-webkit-';
                }
                if (in_array($propTrim, static::$_moz_webkit_ms)) {
                    $prefixes[] = '-moz-';
                    $prefixes[] = '-webkit-';
                    $prefixes[] = '-ms-';
                }
                if (in_array($propTrim, static::$_moz_webkit_ms_o)) {
                    $prefixes[] = '-moz-';
                    $prefixes[] = '-webkit-';
                    $prefixes[] = '-ms-';
                    $prefixes[] = '-o-';
                }
                if (in_array($propTrim, static::$_misc)) {
                }

                $space = '';
                if (count($prefixes) > 0) {
                    for ($x = 0; $x <= $spacing; $x++) {
                        $space .= ' ';
                    }
                }
                foreach ($prefixes as $prefix) {
                    $result .= "\n" . $space . $prefix . $propTrim . ':' . $value . ';';
                }

                continue;
            }

            $i++;
        }

        return $result;
    }

}