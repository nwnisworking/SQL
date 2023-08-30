<?php
namespace SQL;
use ErrorException;

abstract class Query{
  /**
   * The table for the column
   */
  protected ?string $table = null;

  /**
   * The column-value or column only for select/insert/delete/update query. 
   */
  protected ?array $columns = null;

  /**
   * The name of the query that is being represented.
   */
  public string $name;

  /**
   * Type of data that will passed when building query.
   */
  protected string $placeholder_type = '?';

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
   * Change the placeholder type when adding data
   */
  public function changePlaceholder(string $data_type){
    if(!in_array($data_type, ['?', ':']))
      throw new ErrorException('Data type is invalid');

    $this->placeholder_type = $data_type;
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

    if($this->placeholder_type === ':' && !is_null($data)){
      preg_match_all('/:[a-zA-Z0-9]+/', $stmt, $a);
      $a = $a[0];
      $b = array_keys($data);

      if(array_is_list($data))
        throw new ErrorException('data is not an associative array');

      if(count($v = array_diff($a, $b)))
        throw new ErrorException(join(', ', array_unique($v)).' key missing from data');
    }
    elseif($this->placeholder_type === '?' && !is_null($data)){
      if(!array_is_list($data))
        throw new ErrorException('data is not a sequential array');

      if(substr_count($stmt, '?') !== count($data))
        throw new ErrorException('Number of ? does not match the size of data');
    }

    $data = is_null($data) ? [] : $data;

    foreach($data as $k=>$v){
      if($this->placeholder_type === ':')
        $this->data[$k] = $v;
      else
        $this->data[] = $v;

    }

    return $this;
  }

  /**
   * Get the statement of the query and its data 
   */
  public function getStatement(mixed &$data){
    $data = $this->data;
    return $this->stmt;
  }

  /**
   * Check whether if parameter value match the defined operators 
   */
  public static function isCondition(string $op){
    return in_array($op, ['=', '<', '>', '<=', '>=', '<>', '!=', 'IN', 'BETWEEN']);
  }

  public function column(){
    if($this->name === 'SELECT')
      return array_values($this->columns);

    return array_keys($this->columns);
  }

  public abstract function build();
}