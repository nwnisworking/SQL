<?php
namespace DB\Query;
use DB\Element;
use DB\Query;

final class Insert extends Query{
  private ?string $table = null;

  private ?array $columns = null;

  public string $name = 'INSERT';

  public function __construct(string $table, array $columns){
    $this->table = $table;
    $this->columns = $columns;
  }

  public function build(){
    $key = array_keys($this->columns);
    $value = array_values($this->columns);

    $this
    ->add(sprintf("INSERT INTO $this->table(%s)", join(',', $key)))
    ->add(sprintf(" VALUES(%s)", join(',', array_map(fn($e)=>$this->data_type === ':' ? ":$e" : '?', $key))),
      $this->data_type === ':' ? array_combine(array_map(fn($e)=>":$e", $key), $value) : $value
    );

    if(isset($this->database))
      return $this->database;
    else
      return $this;
  }
}