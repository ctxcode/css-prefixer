<?php

include __DIR__ . '/../src/Table.php';
include __DIR__ . '/../src/CssPrefixer.php';

$input = $argv[1];
$out = $argv[2];

$css = file_get_contents($input);

$cssFixer = new \CssPrefixer\Prefixer();
$css = $cssFixer->prefixCss($css);

file_put_contents($out, $css);

//echo $css;
