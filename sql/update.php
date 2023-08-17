<?php
namespace SQL;

class Update extends Query{
  protected string $table;
  
  protected array $columns;

  public string $name = 'UPDATE';


  public function __construct(string $table, array $columns){
    $this->table = $table;
    $this->columns = $columns;
  }

  public function build(){}
}