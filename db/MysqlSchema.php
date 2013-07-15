<?php
/**
 * Author:  Mark O'Keeffe
 * Company: Veneficus Ltd.
 * Date:    15/07/13
 *
 * MysqlSchema.php
 */

class MysqlSchema extends CMysqlSchema
{
  /**
   * Collects the foreign key column details for the given table.
   * @param CMysqlTableSchema $table the table metadata
   */
  protected function findConstraints($table)
  {
    $row=$this->getDbConnection()->createCommand('SHOW CREATE TABLE '.$table->rawName)->queryRow();
    $matches=array();
    $regexp='/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)\sON DELETE ([A-Z ]*)\sON UPDATE ([A-Z ]*)/mi';
    foreach($row as $sql)
    {
      if(preg_match_all($regexp,$sql,$matches,PREG_SET_ORDER))
        break;
    }
    foreach($matches as $match)
    {

      $keys=array_map('trim',explode(',',str_replace(array('`','"'),'',$match[1])));
      $fks=array_map('trim',explode(',',str_replace(array('`','"'),'',$match[3])));
      $onDelete = $match[4];
      $onUpdate = $match[5];
      foreach($keys as $k=>$name)
      {
        $table->foreignKeys[$name]=array(
          str_replace(array('`','"'),'',$match[2]),
          $fks[$k],
          $onDelete,
          $onUpdate,
        );
        if(isset($table->columns[$name]))
          $table->columns[$name]->isForeignKey=true;
      }
    }
  }

}