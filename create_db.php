z<?php
require __DIR__ . '/includes/bootstrap.php';

// Create Database
$queryStatement_insert = 'INSERT INTO %s %s VALUES %s' ;
$queryStatement_update = 'UPDATE %s SET %s WHERE %s';
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
  `SERVER_INFO` json NOT NULL default '{}',
  `SERVER_PLAYER_NUM` tinyint NOT NULL default 0,
  `SERVER_PLAYER_INFO` json NOT NULL default '{}',
  PRIMARY KEY (`SERVER_ID`),
  INDEX (`SERVER_ID`, `SERVER_TIMESTAMP`),
  FOREIGN KEY `REL_GAMESERVER`(`SERVER_GAMESERVER`)
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
  $db->commit();
  echo $date;
}

catch ( mysqli_sql_exception $e )
{
  printf("Database initialization has failed.\n %s", $e);
  $db->close();
  exit();
} finally {
  $db->next_result();
}

// Insert Initial GameServers Table
$queryStatement_select = 'SELECT * FROM %s';

try
{
  $database = $db->select_db(DB_NAME);
  $data = $db->query(sprintf($queryStatement_select, DB_TABLE_GAMESERVERS)) or die($db->error);
  $servers_db = $data->fetch_all(MYSQLI_ASSOC);  
} catch (mysqli_sql_exception $e) {
  echo $e;
} finally {
  $db->next_result();
} 


$index = 0;

$columns = array('GAMESERVER_NAME', 'GAMESERVER_HOST', 'GAMESERVER_PORT', 'GAMESERVER_QUERYPORT', 'GAMESERVER_ENABLE', 'GAMESERVER_QUERYPROTOCOL');
foreach($servers_JSON as $serverName=>$value)
{
  $values = array($serverName,$value['queryHost'],$value['serverPort'],$value['queryPort'],$value['enableQuery'],$value['queryProtocol']);
  $concat = array_combine($columns, $values);
  array_walk($concat, function(&$v, $k){$v = $k . ' = \''. $v .'\'';});
  $concat = implode(', ', $concat);

  if(!isset($servers_db[$index]['GAMESERVER_ID'])){
    $db->query(sprintf($queryStatement_insert, DB_TABLE_GAMESERVERS, '(`GAMESERVER_NAME`)', '(\''. $serverName .'\')'));
  } 
  $queryStatement = sprintf($queryStatement_update, DB_TABLE_GAMESERVERS, $concat, 'GAMESERVER_ID = ' . ($index + 1) . '' ); 
  $db->query($queryStatement);
  $db->next_result();
  $index++;
}
