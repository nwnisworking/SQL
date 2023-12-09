<?php
namespace SQL\Query;

use SQL\Condition;
use SQL\Query;
use SQL\WhereTrait;

final class Delete extends Query{
  use WhereTrait;

  public function data(): array{
    $data = [];
    $where = $this->where;
    array_shift($where);

    foreach($where as $v)
      if(is_a($v, Condition::class))
        array_push($data, ...(is_array($val = $v->values()) ? $val : [$val]));

    return $data;
  }

  public function __toString() : string{
    $stmt = "DELETE FROM $this->table";

    if(count($this->where))
      $stmt.= ' '.Condition::multiple(...$this->where);

    return $stmt;
  }
}