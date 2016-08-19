<?php

if (1 > count($argv)) {
    echo "Usage: php -f {$argv[0]} \n";
    return;
}

$modman = $argv[1];

$modman = @file_get_contents($modman);

$modman = explode("\n", $modman);
$files = array();
foreach ($modman as $line) {
    list($file, ) = explode(' ', $line);
    $files[] = $file;
}
$files = array_filter($files);
print_r($files);
echo "<contents>\n";
echo "\t<target>\n";
$targets = array(
    'magelocal' => 'app/code/local',
    'magecommunity' => 'app/code/community',
    'magecore' => 'app/code/core',
    'magedesign' => 'app/design',
    'mageetc' => 'app/etc',
    'magelib' => 'lib',
    'magelocale' => 'app/locale',
    'magemedia' => 'media',
    'mageskin' => 'skin',
    // 'mageweb' => '',
    'magetest' => 'tests',
    // 'mage' => '',
);
    foreach ($files as &$file) {

        $target = 'mage';
        foreach ($targets as $key => $_target) {
            if (0 === strpos($file, $_target)) {
                $target = $key;
                $file = str_replace($_target . '/', '', $file);
            }
        }

        echo "\t\t<target>{$target}</target>\n";
    }
echo "\t</target>\n";
echo "\t<path>\n";
    foreach ($files as $file) {
        echo "\t\t<path>{$file}</path>\n";
    }
echo "\t</path>\n";
echo "\t<type>\n";
    foreach ($files as $file) {
        $type = strstr($file, '.') ? 'file' : 'dir';
        echo "\t\t<type>{$type}</type>\n";
    }
echo "\t</type>\n";
echo "\t<include>\n";
    foreach ($files as $file) {
        echo "\t\t<include/>\n";
    }
echo "\t</include>\n";
echo "\t<ignore>\n";
    foreach ($files as $file) {
        echo "\t\t<ignore/>\n";
    }
echo "\t</ignore>\n";
echo "</contents>\n";
// $host      = $argv[1];
// $username  = $argv[2];
// $password  = $argv[3];
// $database  = $argv[4];
// $tableName = $argv[5];
// $magentoVersion = isset($argv[6]) ? $argv[6] : 1;

// $link = mysql_connect($host, $username, $password);
// mysql_select_db($database);
// $query = "SHOW CREATE TABLE {$tableName}";

// $result = mysql_query($query);

// $_sql = array();
// $sql = '';
// while ($line = mysql_fetch_array($result)) {
//     foreach ($line as $value) {
//         $_sql[] = $value;
//     }
// }
// mysql_close($link);

// $sql = $_sql[2];
// $line = "***************************";
// $line .= $line . $line;
// echo "\n{$line}\n" . $sql . "\n{$line}\n";

// $convertor = new SQLCreateStatemant2Mage2DdlTableConvertor($sql, $magentoVersion);

// echo $convertor->generateItnterface();
// echo "\n{$line}\n";
// echo $convertor;


// echo "\n{$line}\n";
