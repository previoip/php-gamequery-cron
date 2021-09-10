<?php
// define database defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'my_gamequery_db');

// define database.table name defaults, once initiated do not modify these
define('DB_TABLE_GAMESERVERS', 'game_servers');
define('DB_TABLE_STATS', 'serverstats');

// get servers information information from json file
$servers_JSON = file_get_contents( __DIR__ . '/servers.json');
$servers_JSON = json_decode($servers_JSON, TRUE);

mysqli_report(MYSQLI_REPORT_STRICT);