<?php
namespace SQL;

class Insert extends Query{
  protected string $table;
  
  protected array $columns;

  public string $name = 'INSERT';

  public function __construct(string $table, array $columns){
    $this->table = $table;
    $this->columns = $columns;
  }

  public function build(){
    $this->add(sprintf(
      'INSERT INTO %s(%s) VALUES (%s)', 
      $this->table, 
      join(' ',array_keys($this->columns)), 
      join(' ', array_map(fn($e)=>'?', $this->columns))
    ), ...array_values($this->columns));
    return $this->db;
  }
}