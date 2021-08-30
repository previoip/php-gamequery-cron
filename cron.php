<?php
require_once __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/includes/functions.php';


// define defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'my_gamequery_db');
define('DB_TABLE_GAMESERVERS', 'game_servers');
define('DB_TABLE_GAMEINFO', 'game_info');

// timezone: GMT+0
$date = gmdate('Y-m-d H:i:s', time());

// todo: parse query
$gameServers_file = file_get_contents(__DIR__ . '/servers.json');
$gameServers = json_decode($gameServers_file);
exit();

//ignore
$db = new mysqli;
$db->connect(DB_HOST, DB_USER, DB_PASS);

if($db->connect_errno()){
  printf("Could not connect to MySQL databse: %s\n", $db->connect_errno);
  exit();
}

$database = $db->select_db(DB_NAME);
if(!$database){
  $db->die('No database is present');

  // todo: try create database, if not then continue, if cant then rollback and die
  $queryStatement_databaseInit = <<<SQLSTMT
  CREATE DATABASE `%s`
  SQLSTMT;
  
  $queryStatement_tableInit = <<<SQLSTMT
  CREATE TABLE IF NOT EXISTS `%s` (
    `ID` int(11) unsigned NOT NULL auto_increment,
    `SERVER_NAME` varchar(255) NOT NULL default '',
    `SERVER_HOST` varchar(255) NOT NULL default '',
    `SERVER_QUERYPORT` varchar(255) NOT NULL default '',
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `%s` (
    `ID` int(11) unsigned NOT NULL auto_increment,
    `SERVER` int(11) unsigned NOT NULL,
    `SERVER_TIMESTAMP` datetime NOT NULL,
    `SERVER_STATUS` boolean NOT NULL default '0',
    `SERVER_INFO` varchar(255) NOT NULL default '',
    `SERVER_PLAYER_NUM` tinyint NOT NULL default '0',
    `SERVER_PLAYER_INFO` varchar(255) NOT NULL default '',
    PRIMARY KEY (`ID`)
    FOREIGN KEY `SERVER` (`ID`);
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  SQLSTMT;

  $queryStatement_tableInit = sprintf($queryStatement_tableInit, DB_TABLE_GAMEINFO, DB_TABLE_GAMESERVERS);

}
else {
  // todo: insert query result if values differ
  $columns = ['ID', 'SERVER', 'SERVER_TIMESTAMP', 'SERVER_STATUS', 'SERVER_INFO', 'SERVER_PLAYER_NUM', 'SERVER_PLAYER_INFO'];
  $values = ['', '', $date, 'null', 'null', 'null', 'null', 'null', 'null'];
  
  $queryColumns = encloseStatementValue($columns);
  $queryValues = encloseStatementValue($values);
  $queryStatement_insert = 'INSERT INTO `' . DB_TABLE_GAMEINFO . '` %s VALUES %s;';
  
  $queryStatement = sprintf($queryStatement_insert, $queryColumns, $queryValues);
}