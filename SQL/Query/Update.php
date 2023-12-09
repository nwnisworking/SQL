<?php
namespace SQL\Query;

use SQL\Condition;
use SQL\Query;
use SQL\WhereTrait;

final class Update extends Query{
  use WhereTrait;

  public function data(): array{
    $data = array_values($this->columns);
    $where = $this->where;
    array_shift($where);

    foreach($where as $v)
      if(is_a($v, Condition::class))
        array_push($data, ...(is_array($val = $v->values()) ? $val : [$val]));

    return $data;
  }

  public function __toString() : string{
    $stmt = "UPDATE $this->table SET";
    $columns = array_map(fn($a, $b)=>Condition::eq($a, $b, ','), array_keys($this->columns), $this->columns);

    $stmt.= ' '.Condition::multiple(...$columns).' '.Condition::multiple(...$this->where);
    return $stmt;
  }
}