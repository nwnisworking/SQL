<?php
namespace DB\Query;
use DB\Element;
use DB\Query;

final class Delete extends Query{
  private ?string $table = null;


  private Element $where;

  public function __construct(string $table){
    $this->table = $table;
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
    $this->add("DELETE FROM $this->table");

    if($this->where->size()){
      $this->add(' WHERE '.$this->where, $this->where->data());
    }

    if(isset($this->database))
      return $this->database;
    else
      return $this;
  }
}