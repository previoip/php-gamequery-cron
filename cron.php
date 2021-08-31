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

// get servers information
$servers_JSON = file_get_contents( __DIR__ . '/servers.json');
$servers_JSON = json_decode($servers_JSON, TRUE);

// SourceQuery init

$gquery = new xPaw\SourceQuery\SourceQuery();
define('SQ_TIMEOUT',  1 );
define('SQ_ENGINE',   xPaw\SourceQuery\SourceQuery::SOURCE );

// timezone: GMT+0
$date = gmdate('Y-m-d H:i:s', time());

// mysqli begin
$db = new mysqli;
$db->connect(DB_HOST, DB_USER, DB_PASS);

if($db->connect_error){
  $db->die(sprintf("Could not connect to MySQL databse: %s\n", $db->connect_error));
  exit();
}

$db->autocommit(FALSE);

$database = $db->select_db(DB_NAME);

if(!$database){
  // try create the database, if already created then continue, if cant then rollback and die
  $queryStatement_databaseInit = "CREATE DATABASE IF NOT EXISTS `%s`";
  $queryStatement_databaseInit = sprintf($queryStatement_databaseInit, DB_NAME);
  
  $queryStatement_tableInit = <<<SQLSTMT
  CREATE TABLE IF NOT EXISTS `%s` (
    `GAMESERVER_ID` int(11) unsigned NOT NULL auto_increment,
    `GAMESERVER_ENABLE` tinyint NOT NULL default 1,
    `GAMESERVER_NAME` varchar(255) NOT NULL default '',
    `GAMESERVER_HOST` varchar(255) NOT NULL default '',
    `GAMESERVER_PORT` varchar(255) NOT NULL default '',
    `GAMESERVER_QUERYPORT` varchar(255) NOT NULL default '',
    `GAMESERVER_QUERYPROTOCOL` varchar(255) NOT NULL default '',
    PRIMARY KEY (`GAMESERVER_ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `%s` (
    `SERVER_ID` int(11) unsigned NOT NULL auto_increment,
    `SERVER_GAMESERVER` int(11) unsigned NOT NULL,
    `SERVER_TIMESTAMP` datetime NOT NULL,
    `SERVER_STATUS` tinyint NOT NULL default 0,
    `SERVER_INFO` varchar(255) NOT NULL default '',
    `SERVER_PLAYER_NUM` tinyint NOT NULL default 0,
    `SERVER_PLAYER_INFO` varchar(255) NOT NULL default '',
    PRIMARY KEY (`SERVER_ID`),
    INDEX (`SERVER_ID`, `SERVER_TIMESTAMP`),
    FOREIGN KEY `SERVER_GAMESERVER`(`SERVER_ID`)
      REFERENCES `%s`(`GAMESERVER_ID`)
      ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  SQLSTMT;
  $queryStatement_tableInit = sprintf($queryStatement_tableInit, DB_TABLE_GAMEINFO, DB_TABLE_GAMESERVERS, DB_TABLE_GAMEINFO);

  $db->query($queryStatement_databaseInit);
  $db->select_db(DB_NAME);
  $db->multi_query($queryStatement_tableInit);

  if(!$db->commit()){
    echo sprintf("Could not initialize database: %s\n", $db->connect_errno);
    $db->rollback();
  }
}

// todo: insert query result if values differ
$columns = ['SERVER_ID', 'SERVER_GAMESERVER', 'SERVER_TIMESTAMP', 'SERVER_STATUS', 'SERVER_INFO', 'SERVER_PLAYER_NUM', 'SERVER_PLAYER_INFO'];
$values = ['', '', $date, 'null', 'null', 'null', 'null', 'null', 'null'];

$queryColumns = encloseStatementValue($columns);
$queryValues = encloseStatementValue($values);
$queryStatement_insert = 'INSERT INTO `' . DB_TABLE_GAMEINFO . '` %s VALUES %s;';

$queryStatement = sprintf($queryStatement_insert, $queryColumns, $queryValues);

$db->close();
