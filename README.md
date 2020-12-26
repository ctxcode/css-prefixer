
# Prefix CSS

Note: This is just a simple library and wont fix most of the things. At the moment it just contains all the prefixes from the MSDN docs and applies it to your CSS. That and adding @keyframes for -webkit-.

## Install

```
composer require ctxkiwi/css-prefixer
```

## Usage

```
$prefixer = new \CssPrefixer\Prefixer();
$css = $prefixer->prefixCss($css);
```
