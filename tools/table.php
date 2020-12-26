<?php

$files = glob(__DIR__ . '/properties/*.json');
$result = [];

$browserAlias = [
    'chrome' => 'c',
    'chrome_android' => 'ca',
    'edge' => 'e',
    'firefox' => 'f',
    'firefox_android' => 'fa',
    'ie' => 'ie',
    'opera' => 'o',
    'opera_android' => 'oa',
];

function getBrowserAlias($name) {
    global $browserAlias;
    if (isset($browserAlias[$name])) {
        return $browserAlias[$name];
    }
    $browserAlias[$name] = count($browserAlias);
    return $browserAlias[$name];
}

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file));
    if (!isset($data->css->properties)) {
        continue;
    }
    foreach ($data->css->properties as $pname => $pdata) {

        $pResult = [
            'b' => [],
        ];
        foreach ($pdata as $vname => $vdata) {

            if ($vname == '__compat') {
                if (!isset($vdata->support)) {
                    continue;
                }

                foreach ($vdata->support as $browser => $bdata) {
                    foreach ($bdata as $versionData) {
                        if (isset($versionData->prefix) && !isset($versionData->version_removed)) {

                            $prefix = $versionData->prefix;
                            $b = getBrowserAlias($browser);

                            if (!isset($pResult['b'][$b])) {
                                $pResult['b'][$b] = [];
                            }
                            $pResult['b'][$b]['pre'] = $prefix;

                            break;
                        }
                    }
                }

                continue;
            }

            // e.g. pname = display , vname: flex
            if (!isset($vdata->__compat->support)) {
                continue;
            }

            foreach ($vdata->__compat->support as $browser => $bdata) {
                foreach ($bdata as $versionData) {
                    if (isset($versionData->prefix) && !isset($versionData->version_removed)) {

                        $prefix = $versionData->prefix;
                        $b = getBrowserAlias($browser);

                        if (!isset($pResult['b'][$b])) {
                            $pResult['b'][$b] = [];
                        }
                        if (!isset($pResult['b'][$b]['vals'])) {
                            $pResult['b'][$b]['vals'] = [];
                        }

                        $pResult['b'][$b]['vals'][$vname] = ['pre' => $prefix];

                        break;
                    }
                }
            }

        }
        if (count($pResult['b']) > 0) {
            $result[$pname] = $pResult;
        }

    }
}

file_put_contents(__DIR__ . '/table.json', json_encode($result));

$code = "<?php\n\nnamespace CssPrefixer;\n\nclass Table {\n\n public static \$table = ";
$code .= var_export($result, true);
$code .= ";\n\n}\n";

file_put_contents(__DIR__ . '/../src/Table.php', $code);