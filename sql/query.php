<?php
namespace SQL;
use ErrorException;

abstract class Query{
  public string $stmt = '';

  public array $data = [];

  protected ?DB $db;

  public function add(string $stmt, mixed ...$data){
    if(substr_count($stmt, '?') !== count($data))
      throw new ErrorException('Data size does not match');

    $this->stmt.= $stmt;

    if(count($data))
      array_push($this->data, ...$data);

    return $this;
  }

  public function setConnection(DB $db){
    $this->db = $db;
    return $this;
  }

  public function prependQuery(?Query $query){
    if(!isset($query)) return $this;
    
    $this->stmt = $query->stmt.";$this->stmt";
    array_unshift($this->data, ...$query->data);
    return $this;
  }

  public static function select(string $table, array $column = ['*']){
    if(!array_is_list($column))
      throw new ErrorException('Array must be sequential');

    return new Select($table, $column);
  }

  public static function insert(string $table, array $column){
    return new Insert($table, $column);
  }

  public static function delete(string $table){
    return new Delete($table);
  }

  public static function update(string $table, array $column){
    if(array_is_list($column))
      throw new ErrorException('Array must be a list');

    return new Update($table, $column);
  }
}