<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/server.class.php';
include __DIR__ . '/includes/functions.php';

// define db defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'test_gamequery_db');
// define db.table defaults, do not change
define('DB_TABLE_GAMESERVERS', 'game_servers');
define('DB_TABLE_STATS', 'serverstats');

// get servers information
$servers_JSON = file_get_contents( __DIR__ . '/servers-info-temp.json');
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

  echo "Creating Database...";

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
    `SERVER_INFO` json NOT NULL default '',
    `SERVER_PLAYER_NUM` tinyint NOT NULL default 0,
    `SERVER_PLAYER_INFO` json NOT NULL default '',
    PRIMARY KEY (`SERVER_ID`),
    INDEX (`SERVER_ID`, `SERVER_TIMESTAMP`),
    FOREIGN KEY `SERVER_GAMESERVER`(`SERVER_ID`)
      REFERENCES `%s`(`GAMESERVER_ID`)
      ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  SQLSTMT;

  $queryStatement_tableInit = sprintf($queryStatement_tableInit, DB_TABLE_GAMESERVERS, DB_TABLE_STATS, DB_TABLE_GAMESERVERS);
  
  try
  {
    $db->query($queryStatement_databaseInit);
    $db->select_db(DB_NAME);
    $db->multi_query($queryStatement_tableInit);
  }
  
  catch ( Exception $e )
  {
    $db->rollback();
    printf("Database initialization has failed.\n %s", $e);
  }

  if(!$db->commit() && $db->connect_error){
    printf("Could not initialize database: %s\n", $db->connect_error);
    exit();
  } else {
  echo "Database Created.\n";
  echo $date;
  }

} else {
  
  $queryStatement_exist = 'SELECT EXISTS( SELECT 1 FROM `%s` WHERE %s = %s) LIMIT 1';
  $queryStatement_insert = 'INSERT INTO %s %s VALUES %s' ;
  $queryStatement_update = 'UPDATE %s SET %s WHERE %s';
  $queryStatement_select = 'SELECT * FROM %s';
  $queryStatement_insert_dupe = $queryStatement_insert . ' ON DUPLICATE KEY UPDATE %s';

  $columns = ['GAMESERVER_NAME', 'GAMESERVER_HOST', 'GAMESERVER_PORT', 'GAMESERVER_QUERYPORT', 'GAMESERVER_ENABLE', 'GAMESERVER_QUERYPROTOCOL'];
  $queryColumns = encloseStatementValue($columns);

  $data = $db->query(sprintf($queryStatement_select, DB_TABLE_GAMESERVERS));
  $db->commit();
  $servers_db = $data->fetch_all(MYSQLI_ASSOC);
  
  
  $columns = ['GAMESERVER_NAME', 'GAMESERVER_HOST', 'GAMESERVER_PORT', 'GAMESERVER_QUERYPORT', 'GAMESERVER_ENABLE', 'GAMESERVER_QUERYPROTOCOL'];
  
  $index = 0;
  foreach($servers_JSON as $serverName=>$server){
    // $values = array($serverName, $server['queryHost'], $server['serverPort'], $server['queryPort'], 1, $server['queryProtocol']);
    $values = array($serverName, $server['queryHost'], $server['serverPort'], $server['queryPort'], $server['enableQuery'], $server['queryProtocol']);
    $concat = array_combine($columns, $values);
    array_walk($concat, function(&$v, $k){$v = $k . ' = \''. $v .'\'';});
    $concat = implode(', ', $concat);
    
    if(!isset($servers_db[$index]['GAMESERVER_ID']))
    {
      $db->query(sprintf($queryStatement_insert, DB_TABLE_GAMESERVERS, '(`GAMESERVER_NAME`)', '(\''. $serverName .'\')'));
    }
    $queryStatement = sprintf($queryStatement_update, DB_TABLE_GAMESERVERS, $concat, 'GAMESERVER_ID = ' . ($index + 1) . '' ); 
    $db->query($queryStatement);

    $index++;
  }
    $db->commit();


  // todo: insert query result if values differ
  $columns = ['SERVER_ID', 'SERVER_GAMESERVER', 'SERVER_TIMESTAMP', 'SERVER_STATUS', 'SERVER_INFO', 'SERVER_PLAYER_NUM', 'SERVER_PLAYER_INFO'];
  $values = ['', '', $date, 'null', 'null', 'null', 'null', 'null', 'null'];

  $queryColumns = encloseStatementValue($columns);
  $queryValues = encloseStatementValue($values);
  $queryStatement = sprintf($queryStatement_insert, DB_TABLE_STATS, $queryColumns, $queryValues);
}

$db->close();