<?php
namespace SQL\Query;

use InvalidArgumentException;
use SQL\Condition;
use SQL\Query;

final class Insert extends Query{
  private array $values = [];

  private array $duplicate = [];

  /**
   * Set columns that matches a unique index 
   */
  public function duplicate(string $column, mixed $value): self{
    $this->duplicate[] = new Condition($column, '=', $value, ',');
    return $this;
  }

  /**
   * Insert values into database
   */
  public function values(mixed ...$values): self{
    if(count($this->columns) === 0 || count($values) === count($this->columns))
      $this->values[] = array_map(fn($e)=>new Condition('', '', $e, ','), $values);
    else
      throw new InvalidArgumentException('Argument length does not match the size of columns');

    return $this;
  }

  /**
   * Get the value of VALUES and DUPLICATE
   */
  public function data(): array{
    $data = [];

    foreach(array_merge($this->values, $this->duplicate) as $v){
      if(is_array($v)){
        array_push($data, ...array_merge(...array_map(fn($e)=>$e->values(), $v)));
      }
      else
        array_push($data, ...$v->values());
    }

    return $data;
  }

  public function __toString() : string{
    $stmt = "INSERT INTO $this->table";


    if(isset($this->columns) && !empty($this->columns))
      $stmt.= '('.join(', ', $this->columns).')';

    if(count($this->values)){
      $stmt.= " VALUES";

      foreach($this->values as $i=>$value){
        $stmt.= '('.Condition::multiple(...$value).')';

        if(count($this->values) - 1 !== $i)
          $stmt.= ', ';
      }
    }

    if(count($this->duplicate))
      $stmt.= ' ON DUPLICATE KEY UPDATE '.Condition::multiple($this->duplicate);

    return $stmt;
  }
}