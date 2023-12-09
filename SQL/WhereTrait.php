<?php
namespace SQL;

trait WhereTrait{
  protected array $where = [];

  /**
   * Add WHERE conditions 
   */
  public function where(Condition ...$conditions): self{
    if(!count($this->where))
      $this->where[] = 'WHERE';

    $this->where = array_merge($this->where, $conditions);
    return $this;
  }

  /**
   * Encapsulates conditions 
   */
  public function encloseWhere(Condition ...$conditions): self{
    return $this->where(new Condition('', '()', $conditions));
  }

  public function eq(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::eq($column, $value, $glue));
  }

  public function lt(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::lt($column, $value, $glue));
  }

  public function gt(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::gt($column, $value, $glue));
  }

  public function lte(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::lte($column, $value, $glue));
  }

  public function gte(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::gte($column, $value, $glue));
  }

  public function not(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::not($column, $value, $glue));
  }

  public function like(string $column, mixed $value, string $glue = 'AND'): self{
    return $this->where(Condition::like($column, $value, $glue));
  }

  public function in(string $column, array $value, string $glue = 'AND'): self{
    return $this->where(Condition::in($column, $value, $glue));
  }

  public function between(string $column, mixed $a, mixed $b, string $glue = 'AND'): self{
    return $this->where(Condition::between($column, $a, $b, $glue));
  }
}