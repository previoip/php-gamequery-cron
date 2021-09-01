<?php

use xPaw\SourceQuery as SQ;

define('SQ_TIMEOUT',  1 );
define('SQ_ENGINE',   SQ\SourceQuery::SOURCE );

class GameServer
{
  public $serverName;
  public $serverHost;
  public $serverPort;
  
  private $serverEngine;
  private $timeout;
  private $serverQuery;

  private $queryResult;

  public function __construct()
  {
    $this->serverQuery = new SQ\SourceQuery;
  }

  private function addServer( $serverName, $serverHost, $serverPort, $serverEngine = SQ_ENGINE, $timeout = SQ_TIMEOUT )
  {
    $this->serverName = $serverName;
    $this->serverHost = $serverHost;
    $this->serverPort = $serverPort;
    $this->serverEngine = $serverEngine;
    $this->timeout = $timeout;
  }

  private function query( $returnArray = False )
  {
    try
    {
      $this->serverQuery->connect( $this->serverHost, $this->serverPort, $this->serverEngine, $this->timeout );
      $this->queryResult = array( null, $this->serverQuery->GetInfo(), $this->serverQuery->GetRules(), $this->serverQuery->GetPlayers() );
    }

    catch (Exeption $e)
    {
      $this->queryResult = array( $e , null, null, null);
    }

    finally
    {
      $this->serverQuery->Disconnect();

      if($returnArray){
        return $this->queryResult;
      }
    }
  }

  private function getPlayers()
  {
    return $this->queryResult[3];
  }

  private function getInfo()
  {
    return $this->queryResult[1];
  }
}