<?php

define('ROOT_DIR', __DIR__);
include_once  ROOT_DIR . '/src/StatementConvertor.php';

if (6 > count($argv)) {
    echo "Usage: php -f {$argv[0]} [host] [user] [password] [database] [tables]\n";
    return;
}

$host      = $argv[1];
$username  = $argv[2];
$password  = $argv[3];
$database  = $argv[4];
$tableNames = $argv[5];
// $magentoVersion = 2;//isset($argv[6]) ? $argv[6] : 2;

$connection = mysqli_connect($host, $username, $password);
mysqli_select_db($connection, $database);

$tableNames = explode(',', $tableNames);

$convertor = new \Swissup\StatementConvertor();

$convertor->setReplacements([
    'vendorName' => [
        'Tm' => 'Swissup'
    ],
    'moduleName' => [
        'Helpmate' => 'Helpdesk'
    ],
    'tableName' => [
        'tm_helpmate_department'      => 'swissup_helpdesk_department',
        'tm_helpmate_department_user' => 'swissup_helpdesk_department_user',
        'tm_helpmate_status'          => 'swissup_helpdesk_status',
        'tm_helpmate_theard'          => 'swissup_helpdesk_message',
        'tm_helpmate_ticket'          => 'swissup_helpdesk_ticket'
    ],
    'modelName' => [
        'Theard' => 'Message'
    ]
]);

$delimiter = "***************************";
$delimiter .= $delimiter . $delimiter;
$delimiter = "\n{$delimiter}\n";
foreach ($tableNames as $tableName) {
    $query = "SHOW CREATE TABLE {$tableName}";
    $result = mysqli_query($connection, $query);

    $_sql = array();
    $sql = '';
    while ($line = mysqli_fetch_array($result)) {
        foreach ($line as $value) {
            $_sql[] = $value;
        }
    }

    $sql = $_sql[2];
    echo "{$delimiter}" . $sql . "{$delimiter}";

    $convertor->parse($sql);

    echo $convertor;
    echo "{$delimiter}";
}

mysqli_close($connection);
