<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/server.class.php';
include __DIR__ . '/includes/functions.php';

// define db defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'my_gamequery_db');
// define db.table defaults, do not change
define('DB_TABLE_GAMESERVERS', 'game_server');
define('DB_TABLE_GAMEINFO', 'game_info');

// get servers information
$servers_JSON = json_decode($servers_JSON, TRUE);

// gamequery class instance
$server = new GameServer();

// timezone: GMT+0
$date = gmdate('Y-m-d H:i:s', time());

// mysqli begin
$db = new mysqli;
$db->connect(DB_HOST, DB_USER, DB_PASS);

if($db->connect_error){
  die(sprintf("Could not connect to MySQL databse: %s\n", $db->connect_error));
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
  
  try
  {
    $db->query($queryStatement_databaseInit);
    $db->select_db(DB_NAME);
    $db->multi_query($queryStatement_tableInit);
  }
  
  catch ( Exception $e )
  {
    $db->rollback();
    echo $e;
  }

  if(!$db->commit() && $db->connect_error){
    printf("Could not initialize database: %s\n", $db->connect_error);
  }

} else {
  
  $queryStatement_exist = 'SELECT EXIST( SELECT 1 FROM `' . DB_TABLE_GAMESERVERS . '` WHERE %s = %s) LIMIT 1';
  $queryStatement_insert = 'INSERT INTO `' . DB_TABLE_GAMEINFO . '` %s VALUES %s';
  
  $columns = ['GAMESERVER_NAME', 'GAMESERVER_HOST', 'GAMESERVER_PORT', 'GAMESERVER_QUERYPORT', 'GAMESERVER_ENABLE', 'GAMESERVER_QUERYPROTOCOL'];
  
  $queryColumns = encloseStatementValue($columns[0]);
  $queryValues = encloseStatementValue('myserver');
  $queryStatement = sprintf($queryStatement_exist, $queryColumns, $queryValues);

  $result = $db->query($queryStatement);
  var_dump($result->fetch_all());

  foreach($servers_JSON as $serverName=>$server){
    $values = array($serverName, $server['queryHost'], $server['serverPort'], $server['queryPort'], 1, $server['queryProtocol']);
    $queryColumns = encloseStatementValue($columns);
    $queryValues = encloseStatementValue($values);
    $queryStatement = sprintf($queryStatement_insert, $queryColumns, $queryValues);
  
    // $db->prepare($queryStatement);
  }

  // todo: insert query result if values differ
  $columns = ['SERVER_ID', 'SERVER_GAMESERVER', 'SERVER_TIMESTAMP', 'SERVER_STATUS', 'SERVER_INFO', 'SERVER_PLAYER_NUM', 'SERVER_PLAYER_INFO'];
  $values = ['', '', $date, 'null', 'null', 'null', 'null', 'null', 'null'];

  $queryColumns = encloseStatementValue($columns);
  $queryValues = encloseStatementValue($values);
  $queryStatement = sprintf($queryStatement_insert, $queryColumns, $queryValues);
}

$db->close();