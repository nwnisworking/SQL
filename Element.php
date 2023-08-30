<?php
namespace SQL;
use ErrorException;

class Element{
  public const RAW_VALUE = 0;

  public const COLON = 1;

  public const QUESTION = 2;
  private array $elements = [];

  private array $data = [];

  public readonly int $data_type;

  public function __construct(int $data_type){
    $this->data_type = $data_type;
  }

  public function append(string $column, string $op, mixed $value, string $glue = 'AND'){
    if(
      (in_array($op, ['IN', 'BETWEEN']) || $this->data_type === self::COLON) && 
      !is_array($value)  
    )
      throw new ErrorException('Value is not an array');

    if($this->data_type === self::RAW_VALUE)
      $c = is_array($value) ? array_values($value) : $value;
    
    elseif($this->data_type === self::COLON){
      $c = array_keys($value);
      
      if(array_is_list($value))
        throw new ErrorException('value must be an associative array');

      $this->data = array_merge($this->data, $value);      
    }
    else{
      $c = is_array($value) ? array_fill(0, count($value), '?') : '?';

      if(!is_array($value))
        $value = [$value];
      elseif(is_array($value) && !array_is_list($value))
        $value = array_values($value);

      array_push($this->data, ...$value);
    }

    switch($op){
      case 'IN' : 
        $this->elements[] = fn(bool $add_glue = false)=>sprintf("$column IN (%s)", join(', ', $c)).($add_glue ? ' '.$glue.' ' : '');
      break;
      case 'BETWEEN' :
        $this->elements[] = fn(bool $add_glue = false)=>sprintf("$column BETWEEN %s AND %s", ...$c).($add_glue ? ' '.$glue.' ' : '');
      break;
      case '' : 
        $this->elements[] = fn($add_glue = false)=>$column.($add_glue ? "$glue " : '');
      break;
      default : 
        $this->elements[] = fn(bool $add_glue = false)=>sprintf("$column $op %s", is_array($c) ? $c[0] : $c).($add_glue ? ' '.$glue.' ' : '');
      break;
    } 

    return $this;
  }

  /**
   * Get the size of the element
   */
  public function size(){
    return count($this->elements);
  }

  public function getAll(){
    return $this->elements;
  }

  public function data(){
    return $this->data;
  }

  public function clear(){
    $this->data = [];
    $this->elements = [];
    return $this;
  }

  public function __toString(){
    $str = '';

    foreach($this->elements as $i=>$v){
      $str.= $v($this->size() - 1 !== $i);
    }

    return $str;
  }
}