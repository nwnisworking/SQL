<?php
namespace SQL\Query;
use SQL\Element;
use SQL\Query;

final class Update extends Query{
  private Element $where;

  public function __construct(string $table, array $columns){
    $this->name = 'UPDATE';
    $this->table = $table;
    $this->columns = $columns;
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
    $key = array_keys($this->columns);
    $value = array_values($this->columns);

    $this->add("UPDATE $this->table SET ");
    $this->add(
      sprintf("%s", join(',', array_map(fn($e)=>$this->placeholder_type === ':' ? "$e = :$e" : '$e = ?', $key))), 
      $this->placeholder_type === ':' ? array_combine(array_map(fn($e)=>":$e", $key), $value) : $value
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