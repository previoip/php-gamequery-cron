<?php
require __DIR__ . '/includes/bootstrap.php';

$gameq = new GameServer;

$queryStatement_select = 'SELECT * FROM %s';
$queryStatement_insert = 'INSERT INTO %s %s VALUES %s' ;

try
{
  $database = $db->select_db(DB_NAME);
  $data = $db->query(sprintf($queryStatement_select, DB_TABLE_GAMESERVERS));
  $servers_db = $data->fetch_all(MYSQLI_ASSOC);  
} catch (mysqli_sql_exception $e) {
  echo $e;
  exit();
} finally {
  $db->next_result();
}

if($database && empty($servers_db))
{
  echo "No table found.";
  exit();
}

// $values = ['', $date, 'null', 'null', 'null', 'null', 'null', 'null'];


foreach($servers_db as $index=>$server)
{
  $columns = ['SERVER_GAMESERVER', 'SERVER_TIMESTAMP', 'SERVER_STATUS', 'SERVER_INFO', 'SERVER_PLAYER_NUM', 'SERVER_PLAYER_INFO'];
  $gameq->addServer($server['GAMESERVER_NAME'], $server['GAMESERVER_HOST'], $server['GAMESERVER_QUERYPORT']);
  $gameq->begin();
  $players_json = $gameq->getPlayers(true);
  // $info_json = $gameq->getInfo(true);
  $info_json = '{}';
  $status = empty($players_json) ? empty($info_json) ? 0 : 1 : 2;
  $values = array($index+1, tosstr($date), $status, tosstr($info_json), 1, tosstr($players_json));
  $columns = encloseStatementValue($columns);
  // $values = encloseStatementValue($values, true);
  $values = '(' . implode(',' , $values) . ')';

  $queryStatement = sprintf($queryStatement_insert, DB_TABLE_STATS, $columns, $values);
  echo($queryStatement);
  $db->query($queryStatement) or die($db->error);
  $db->next_result();

}