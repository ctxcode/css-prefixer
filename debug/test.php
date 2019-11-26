<?php

include __DIR__ . '/../CssPrefixer.php';

$input = $argv[1];
$out = $argv[2];

$css = file_get_contents($input);

$cssFixer = new CssPrefixer();
$css = $cssFixer->prefixCss($css);

file_put_contents($out, $css);

echo $css;
