<?php
namespace SQL;
class Condition{
  public const QN = 0;

  public const RAW = 1;

  public const COLON = 2;

  public readonly string $column;

  public readonly string $op;

  public readonly int|string|array|Condition $raw_values;

  public readonly string $glue;

  public int $type;

  public function __construct(string $column, string $op, mixed $values, string $glue = 'AND'){
    $this->column = $column;
    $this->op = $op;
    $this->raw_values = $values;
    $this->glue = $glue;
    $this->type = self::QN;
  }

  public function setType(int $type = self::QN): self{
    $this->type = $type;

    foreach(is_array($this->raw_values) ? $this->raw_values : [] as $value){
      if(is_a($value, self::class))
        $value->setType($type);
    }

    return $this;
  }

  /**
   * A (<) sign condition 
   */
  public static function lt(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, '<', $value, " $glue ");
  }

  /**
   * A (>) sign condition
   */
  public static function gt(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, '>', $value, " $glue ");
  }

  /**
   * A (=) sign condition
   */
  public static function eq(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, '=', $value, " $glue ");
  }

  /**
   * A (<=) sign condition
   */
  public static function lte(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, '<=', $value, " $glue ");
  }

  /**
   * A (>=) sign condition
   */
  public static function gte(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, '>=', $value, " $glue ");
  }

  /**
   * A (<>|!=) sign condition
   */
  public static function not(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, '<>', $value, " $glue ");
  }

  /**
   * A SQL LIKE operator 
   */
  public static function like(string $column, mixed $value, string $glue = 'AND'): self{
    return new self($column, 'LIKE', $value, " $glue ");
  }

  /**
   * A SQL IN operator
   */
  public static function in(string $column, array $value, string $glue = 'AND'): self{
    return new self($column, 'IN', $value, " $glue ");
  }

  /**
   * A SQL BETWEEN operator
   */
  public static function between(string $column, mixed $a, mixed $b, string $glue = 'AND'): self{
    return new self($column, 'BETWEEN', array_merge(
      is_array($a) ? $a : [$a], 
      is_array($b) ? $b : [$b]
    ), $glue);
  }

  /**
   * Creates an enclose condition object
   */
  public static function enclose(Condition ...$condition): self{
    return new self('', '()', $condition, '');
  }

  /**
   * Adds quote around string types
   */
  public static function quote(mixed $value): string{
    return is_string($value) ? '\''. addslashes($value) .'\'': $value;
  }

  public static function join(array $value, string $glue = ','): string{
    return join($glue, $value);
  }

  /**
   * Get nested values from an array 
   */
  public function values(): array{
    $values = [];

    if(!is_array($this->raw_values))
      $values[] = $this->raw_values;

    else
      foreach($this->raw_values as $value)
        array_push($values, 
          ...(is_a($value, self::class) ? $value->values() : [$value])
        );
      
    return $values;
  }

  /**
   * Add multiple conditions into a string
   */
  public static function multiple(mixed ...$conditions): string{
    $str = '';
    $len = count($conditions);

    foreach($conditions as $i=>$v)
      if(is_a($v, self::class))
        $str.= $v.($i < $len - 1 ? $v->glue : '');
      else
        $str.= $v.' ';

    return $str;
  }

  public function __toString(): string{
    $cond = trim(sprintf('%s %s', $this->column, $this->op));

    switch($this->op){
      case 'IN' : 
      case 'BETWEEN' : 
        $value = array_map(fn($v, $k)=>match($this->type){
          self::RAW=>self::quote($v),
          self::QN=>'?',
          self::COLON=>$k
        }, $this->raw_values, array_keys($this->raw_values));

        if($this->op === 'IN')
          $cond.= ' ('.self::join($value).')';      
        
        else
          $cond.= ' '.self::join($value, ' AND ');
      break;
      case '()' : 
        $cond = '('.self::multiple(...$this->raw_values).')';
      break;
      default : 
        $cond.= ' '.match($this->type){
          self::RAW=>self::quote($this->raw_values),
          self::QN=>'?',
          self::COLON=>current(array_keys($this->raw_values))
        };
      break;
    }
    
    return trim($cond);
  }
}