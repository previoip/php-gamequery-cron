<?php
/**
 * Concatenate array into single string for multiple sql table manipulation.
 *
 * @param Array|String Array or string.
 *
 * @return String Returns a concatenate string for sql statement.
 */
function encloseStatementValue($target)
{
  if(gettype($target) == 'string')
  {
    return  '`' . $target . '`'; 
  }
  
  else if (gettype($target) == 'array')
  {
    $temp = array_map(function($item){return '`' . $item . '`';}, $target);
    return  '(' . implode(', ', $temp) . ')';   
  }
  
  else
  {
    return;
  }
}