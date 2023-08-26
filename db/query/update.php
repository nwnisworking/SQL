<?php
namespace DB\Query;
use DB\Element;
use DB\Query;

final class Update extends Query{
  private ?string $table = null;

  private ?array $columns = null;

  private Element $where;

  public function __construct(string $table, array $columns){
    $this->table = $table;
    $this->columns = $columns;
    $this->changeDataType($this->data_type);
  }

  /**
   * Change the type of placeholder used for prepare statement. 
   */
  public function changeDataType(string $data_type){
    parent::changeDataType($data_type);
    $this->where = new Element($this->data_type === '?' ? Element::QN : Element::COLON);

    return $this;
  }

  /**
   * Filter records that matches the condition
   */
  public function where(string $column, string $op, mixed $value, string $glue = 'AND'){
    $this->where->append($column, $op, $value, $glue);

    return $this;
  }

  public function build(){
    $key = array_keys($this->columns);
    $value = array_values($this->columns);

    $this->add("UPDATE $this->table SET ");
    $this->add(
      sprintf("%s", join(',', array_map(fn($e)=>$this->data_type === ':' ? "$e = :$e" : '$e = ?', $key))), 
      $this->data_type === ':' ? array_combine(array_map(fn($e)=>":$e", $key), $value) : $value
    );

    if($this->where->size()){
      $this->add(' WHERE '.$this->where, $this->where->data());
    }

    if(isset($this->database))
      return $this->database;
    else
      return $this;
  }
}