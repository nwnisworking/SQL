<?php
namespace DB;
use ErrorException;

abstract class Query{
  /**
   * Type of data passed.
   */
  protected string $data_type = '?';

  /**
   * Data can either be an associative array or sequential array.
   * 
   */
  protected array $data = [];

  /**
   * The statement constructed when add method is called
   */
  protected string $stmt = '';

  /**
   * The database to connect to the query
   */
  protected ?Database $database;

  /**
   * Change the data type when adding data
   */
  public function changeDataType(string $data_type){
    if(!in_array($data_type, ['?', ':']))
      throw new ErrorException('Data type is invalid');

    $this->data_type = $data_type;
    return $this;
  }

  public function setDatabase(Database $db){
    $this->database = $db;
    return $this;
  }

  /**
   * Add data and statement to the query
   */
  public function add(string $stmt, ?array $data = null){
    $this->stmt.= $stmt;

    if($this->data_type === ':' && !is_null($data)){
      preg_match_all('/:[a-zA-Z0-9]+/', $stmt, $a);
      $a = $a[0];
      $b = array_keys($data);

      if(array_is_list($data))
        throw new ErrorException('data is not an associative array');

      if(count($v = array_diff($a, $b)))
        throw new ErrorException(join(', ', array_unique($v)).' key missing from data');
    }
    elseif($this->data_type === '?' && !is_null($data)){
      if(!array_is_list($data))
        throw new ErrorException('data is not a sequential array');

      if(substr_count($stmt, '?') !== count($data))
        throw new ErrorException('Number of ? does not match the size of data');
    }

    $data = is_null($data) ? [] : $data;

    foreach($data as $k=>$v){
      if($this->data_type === ':')
        $this->data[$k] = $v;
      else
        $this->data[] = $v;

    }

    return $this;
  }

  public function getStatement(mixed &$data){
    $data = $this->data;
    return $this->stmt;
  }

  public static function isCondition(string $op){
    return in_array($op, ['=', '<', '>', '<=', '>=', '<>', '!=', 'IN', 'BETWEEN']);
  }

  public abstract function build();
}