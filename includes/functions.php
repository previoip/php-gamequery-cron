<?php
/**
 * Concatenate array into single string for multiple sql table manipulation.
 *
 * @param Array|String Array or string.
 *
 * @return String Returns a concatenate string for sql statement.
 */
function encloseStatementValue($target, $asString = false)
{
  if(gettype($target) == 'string')
  {
    if($asString)
    {
      return  '\'' . $target . '\''; 
    }
    else
    {
      return  '`' . $target . '`';
    }
  }
  
  else if (gettype($target) == 'array')
  {
    if($asString)
    {
      $temp = array_map(function($item){return '\'' . $item . '\'';}, $target);
    }
    else
    {
      $temp = array_map(function($item){return '`' . $item . '`';}, $target);
    }
    return  '(' . implode(', ', $temp) . ')';   
  }
  
  else
  {
    return;
  }
}