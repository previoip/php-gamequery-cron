<?php
require __DIR__ . '/includes/bootstrap.php';

$queryStatement_select = 'SELECT * FROM %s';

try
{
  $database = $db->select_db(DB_NAME);
  $data = $db->query(sprintf($queryStatement_select, DB_TABLE_GAMESERVERS));
  $servers_db = $data->fetch_all(MYSQLI_ASSOC);  
} catch (mysqli_sql_exception $e) {
  echo $e;
}

if($database && !isset($servers_db))
{
  $servers_db = array();
}

var_dump($servers_db);

$index = 0;

$columns = array('GAMESERVER_NAME', 'GAMESERVER_HOST', 'GAMESERVER_PORT', 'GAMESERVER_QUERYPORT', 'GAMESERVER_ENABLE', 'GAMESERVER_QUERYPROTOCOL');
foreach($servers_JSON as $serverName=>$value)
{
  $values = array($serverName,$value['queryHost'],$value['serverPort'],$value['queryPort'],$value['enableQuery'],$value['queryProtocol']);
  $concat = array_combine($columns, $values);
  array_walk($concat, function(&$v, $k){$v = $k . ' = \''. $v .'\'';});
  $concat = implode(', ', $concat);

  $index++;
}
