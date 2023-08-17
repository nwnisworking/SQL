<?php
namespace SQL;

class Delete extends Query{
  protected string $table;
  
  public string $name = 'DELETE';

  public function __construct(string $table){
    $this->table = $table;    
  }

  public function build(){}
}