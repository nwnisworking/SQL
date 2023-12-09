<?php
namespace SQL\Query;

use SQL\Condition;
use SQL\Query;
use SQL\WhereTrait;
use UnexpectedValueException;

final class Select extends Query{
  use WhereTrait;

  private array $join = [];

  private array $having = [];

  private array $group = [];

  private array $order = [];

  private ?int $limit = null;

  private ?int $offset = null;

  private bool $enclose = false;

  private bool $distinct = false;

  public function __construct(string $table = null, array $columns = ['*']){
    parent::__construct($table, $columns);
  }

  /**
   * Select distinct values 
   */
  public function distinct(bool $distinct = false): self{
    $this->distinct = $distinct;
    return $this;
  }

  /**
   * Enclose SELECT statement with a bracket
   */
  public function enclose(bool $enclose = false): self{
    $this->enclose = $enclose;
    return $this;
  }

  /**
   * Joins current table with another table
   */
  public function join(string $type, string $table, string $column, string $op, string $value, string $glue = 'AND'){
    if(!in_array($type, ['INNER', 'FULL OUTER', 'FULL', 'LEFT', 'RIGHT']))
      throw new UnexpectedValueException("Join type $type is invalid.");

    if(!isset($this->join[$table]))
      $this->join[$table] = [
        'type'=>$type,
        'condition'=>[(new Condition($column, $op, $value, $glue))->setType(Condition::RAW)]
      ];
    else
      $this->join[$table]['condition'][] = (new Condition($column, $op, $value, $glue))->setType(Condition::RAW);

    return $this;
  }

  /**
   * Joins table that have matching values in both tables
   */
  public function innerJoin(string $table, string $column, string $op, string $value, string $glue = 'AND'): self{
    return $this->join('INNER', $table, $column, $op, $value, $glue);
  }

  /**
   * Join with the second table that have matching values. If no match, the second table records will be empty  
   */
  public function leftJoin(string $table, string $column, string $op, string $value, string $glue = 'AND'): self{
    return $this->join('LEFT', $table, $column, $op, $value, $glue);
  }

  /**
   * Join the second table with the first table that have matching values. If no match, the first table record will be empty
   */
  public function rightJoin(string $table, string $column, string $op, string $value, string $glue = 'AND'): self{
    return $this->join('RIGHT', $table, $column, $op, $value, $glue);
  }

  /**
   * Joins both table regardless whether it matches or not
   */
  public function fullJoin(string $table, string $column, string $op, string $value, string $glue = 'AND'): self{
    return $this->join('FULL OUTER', $table, $column, $op, $value, $glue);
  }

  /**
   * Group columns that have same value into summary rows
   */
  public function group(string ...$columns): self{
    array_push($this->group, ...$columns);
    return $this;
  }

  /**
   * Add HAVING conditions 
   */
  public function having(string $column, string $op, mixed $value, string $glue = 'AND'): self{
    if(!count($this->having))
      $this->having[] = 'HAVING';

    $this->having[] = new Condition($column, $op, $value, $glue);
    return $this;
  }

  /**
   * Sort the order of column
   */
  public function order(string $column, string $order = 'DESC'): self{
    $order = strtoupper($order);

    if($order !== 'ASC' && $order !== 'DESC')
      throw new UnexpectedValueException('Only ASC or DESC allowed');
    
    array_push($this->order, "$column $order");

    return $this;
  }

  /**
   * Limits the amount of rows and starting from the offset
   */
  public function limit(int $limit, ?int $offset = null): self{
    $this->limit = $limit;
    $this->offset = $offset;

    return $this;
  }

  /**
   * Get all the values of WHERE and HAVING conditions
   */
  public function data(): array{
    $data = [];

    foreach(array_merge($this->where, $this->having) as $v)
      if(is_object($v))
        array_push($data, ...(is_array($val = $v->values()) ? $val : [$val]));

    return $data;
  }

  public function __toString() : string{
    $stmt = 'SELECT ';
    
    if($this->distinct)
      $stmt .= 'DISTINCT ';

    $stmt.= join(', ', $this->columns);

    if(!isset($this->table) || empty($this->table))
      return $stmt;
    else
      $stmt.= " FROM $this->table";

    if(count($this->join)){
      foreach($this->join as $table=>$join){
        $stmt.= " $join[type] JOIN $table ON ";
        $stmt.= Condition::multiple(...$join['condition']);
      }
    }

    if(count($this->where))
      $stmt.= ' '.Condition::multiple(...$this->where);

    if(count($this->group))
      $stmt.= ' GROUP BY '.join(',', array_unique($this->group));

    if(count($this->having))
      $stmt.= ' '.Condition::multiple(...$this->having);

    if(count($this->order))
      $stmt.= ' ORDER BY '.join(',', $this->order);

    if(isset($this->limit))
      $stmt.= " LIMIT $this->limit";

    if(isset($this->offset))
      $stmt.= " OFFSET $this->offset";

    if($this->enclose)
      return "($stmt)";

    return $stmt;
  }
}