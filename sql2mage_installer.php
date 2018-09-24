<?php

define('ROOT_DIR', __DIR__);
include_once  ROOT_DIR . '/src/StatementConvertor.php';

if (6 > count($argv)) {
    echo "Usage: php -f {$argv[0]} [host] [user] [password] [database] [table]\n";
    return;
}

$host      = $argv[1];
$username  = $argv[2];
$password  = $argv[3];
$database  = $argv[4];
$tableName = $argv[5];
// $magentoVersion = 2;//isset($argv[6]) ? $argv[6] : 2;

$connection = mysqli_connect($host, $username, $password);
mysqli_select_db($connection, $database);
$query = "SHOW CREATE TABLE {$tableName}";

$result = mysqli_query($connection, $query);

$_sql = array();
$sql = '';
while ($line = mysqli_fetch_array($result)) {
    foreach ($line as $value) {
        $_sql[] = $value;
    }
}
mysqli_close($connection);

$sql = $_sql[2];
$line = "***************************";
$line .= $line . $line;
echo "\n{$line}\n" . $sql . "\n{$line}\n";

$convertor = new \Swissup\StatementConvertor($sql);

echo $convertor;
echo "\n{$line}\n";
