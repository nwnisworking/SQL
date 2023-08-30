<?php
namespace SQL\Query;
use SQL\Element;
use SQL\Query;

final class Delete extends Query{
  private Element $where;

  public function __construct(string $table){
    $this->name = 'DELETE';
    $this->table = $table;
    $this->changePlaceholder($this->placeholder_type);
  }

  /**
   * Change the type of placeholder used for prepare statement. 
   */
  public function changePlaceholder(string $data_type){
    parent::changePlaceholder($data_type);
    $this->where = new Element($this->placeholder_type === '?' ? Element::QUESTION : Element::COLON);

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