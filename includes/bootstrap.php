<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/server.class.php';

// timezone: GMT+0
$date = gmdate('Y-m-d H:i:s', time());

// mysqli
$db = new mysqli;

try {
  $db->connect(DB_HOST, DB_USER, DB_PASS);
  // $db->autocommit(FALSE);
  $db->store_result();
} catch (mysqli_sql_exception $e) {
  printf('Terminating Database Connection: %s \n', $e);
  exit();
}

