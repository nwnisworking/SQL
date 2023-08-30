<?php
namespace SQL\Query;
use SQL\Element;
use SQL\Query;

final class Insert extends Query{
  public function __construct(string $table, array $columns){
    $this->name = 'INSERT';
    $this->table = $table;
    $this->columns = $columns;
  }

  public function build(){
    $key = array_keys($this->columns);
    $value = array_values($this->columns);

    $this
    ->add(sprintf("INSERT INTO $this->table(%s)", join(',', $key)))
    ->add(sprintf(" VALUES(%s)", join(',', array_map(fn($e)=>$this->placeholder_type === ':' ? ":$e" : '?', $key))),
      $this->placeholder_type === ':' ? array_combine(array_map(fn($e)=>":$e", $key), $value) : $value
    );

    if(isset($this->database))
      return $this->database;
    else
      return $this;
  }
}