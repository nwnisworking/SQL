<?php
namespace SQL;
use ErrorException;
use SQL\Enums\Join as JoinEnum;
use SQL\Enums\Condition as ConditionEnum;

class Select extends Query{
  private ?string $table = null;
  
  private ?array $columns = null;

  private array $join = [];

  private array $where =[];

  private array $group = [];

  private array $having = [];

  private array $order = [];

  private ?string $limit;

  public string $name = 'SELECT';

  public function __construct(string $table, array $columns){
    $this->table = $table;
    $this->columns = $columns;
  }

  public function join(JoinEnum $type, string $table, string $a, ConditionEnum $op, $b, string $condition = 'AND'){
    if(isset($this->join[$table]))
      $this->join[$table]['args'][] = [$a, $op->value, $b, $condition];
    else
      $this->join[$table] = [
        'type'=>$type,
        'table'=>$table,
        'args'=>[[$a, $op->value, $b, $condition]]
      ];

    return $this;
  }

  public function innerJoin(string $table, string $a, ConditionEnum $op, $b, string $condition = 'AND'){
    return $this->join(JoinEnum::INNER, $table, $a, $op, $b, $condition);
  }

  public function outerJoin(string $table, string $a, ConditionEnum $op, string $b, string $condition = 'AND'){
    return $this->join(JoinEnum::OUTER, $table, $a, $op, $b, $condition);
  }

  public function crossJoin(string $table, string $a, ConditionEnum $op, string $b, string $condition = 'AND'){
    return $this->join(JoinEnum::CROSS, $table, $a, $op, $b, $condition);
  }

  public function leftJoin(string $table, string $a, ConditionEnum $op, string $b, string $condition = 'AND'){
    return $this->join(JoinEnum::LEFT, $table, $a, $op, $b, $condition);
  }

  public function rightJoin(string $table, string $a, ConditionEnum $op, string $b, string $condition = 'AND'){
    return $this->join(JoinEnum::RIGHT, $table, $a, $op, $b, $condition);
  }

  public function where(string $a, ConditionEnum $op, string|array|int $b, string $condition = 'AND'){
    if($op === ConditionEnum::IN && !is_array($b))
      throw new ErrorException('IN requires value to be an array');

    else if($op === ConditionEnum::BETWEEN && (!is_array($b) || count($b) !== 2))
      throw new ErrorException('BETWEEN requires value to be an array of size 2');

    $this->where[] = [$a, $op, $b, $condition];
    return $this;
  }

  public function in(string $a, array $data, string $condition = 'AND'){
    return $this->where($a, ConditionEnum::IN, $data, $condition);
  }

  public function between(string $a, mixed $data, string $condition = 'AND'){
    return $this->where($a, ConditionEnum::BETWEEN, $data, $condition);
  }

  public function having(string $a, ConditionEnum $op, string|array|int $b, string $condition = 'AND'){
    if($op === ConditionEnum::IN && !is_array($b))
      throw new ErrorException('IN requires value to be an array');
    
    else if($op === ConditionEnum::BETWEEN && (!is_array($b) || count($b) !== 2))
      throw new ErrorException('BETWEEN requires value to be an array of size 2');

    $this->having[] = [$a, $op, $b, $condition];
    return $this;
  }

  public function group(string ...$columns){
    $this->group = $columns;
    return $this;
  }

  public function order(string $column, string $order = 'DESC'){
    if(!in_array($order, ['DESC', 'ASC']))
      throw new ErrorException('Invalid order for $order param');
    $this->order[] = "$column $order";
    return $this;
  }

  public function limit(int $size){
    $this->limit = $size;
    return $this;
  }

  private function whereHavingSql(array $data){
    foreach($data as $i=>$v){
      $d = $v[2];
      $v = $v[1]->build($v);

      if(!is_array($d))
        $d = [$d];

      if($i === count($data) - 1)
        array_pop($v);

      $this->add(join(' ', $v).'', ...$d);
    }
  }

  public function build(){
    $this->add(sprintf('SELECT %s FROM %s', join(',', $this->columns), $this->table));
    
    if(count($this->join))
      foreach($this->join as $join)
        $this->add(' '.$join['type']->build($join));

    if(count($this->where)){
      $this->add(' WHERE ');
      $this->whereHavingSql($this->where);
    }

    if(count($this->group))
      $this->add(' GROUP BY '.join(', ', $this->group));

    if(count($this->having)){
      $this->add(' HAVING ');
      $this->whereHavingSql($this->having);
    }

    if(count($this->order))
      $this->add(' ORDER BY '.join(', ', $this->order));

    if(isset($this->limit))
      $this->add(' LIMIT '.$this->limit);

    $this->clear();

    if(isset($this->db))
      return $this->db;
    else
      return $this;
  }

  public function clear(){
    $this->table = null;
    $this->columns = null;
    $this->join = [];
    $this->where =[];
    $this->group = [];
    $this->having = [];
    $this->order = [];
    $this->limit = null;
    return $this;
  }
}