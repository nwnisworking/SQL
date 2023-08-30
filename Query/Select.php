<?php
namespace SQL\Query;
use SQL\Element;
use SQL\Query;
use ErrorException;

final class Select extends Query{
  /**
   * Combine rows from table based on column relation
   */
  private array $join = [];

  /**
   * Filter records that fulfills specified condition
   */
  private Element $where;

  /**
   * Groups row that have the same values into summary rows
   */
  private Element $group;

  /**
   * Filters record by allowing aggregating functions
   */
  private Element $having;

  /**
   * Sort result in ascending or descending order
   */
  private Element $order;

  /**
   * Get the starting index of the row
   */
  private ?int $offset;
  
  /**
   * Limits the number of rows retrieve
   */
  private ?int $limit;

  public function __construct(string $table, array $columns = ['*'], array $data = []){
    if(!array_is_list($columns))
      throw new ErrorException('Column requires a sequential array');

    $this->name = 'SELECT';
    $this->table = $table;
    $this->columns = $columns;
    $this->changePlaceholder($this->placeholder_type);
    array_push($this->data, ...$data);
  }

  /**
   * Change the type of placeholder used for prepare statement. 
   */
  public function changePlaceholder(string $data_type){
    parent::changePlaceholder($data_type);
    $type = $this->placeholder_type === '?' ? Element::QUESTION : Element::COLON;
    $this->where = new Element($type);
    $this->having = new Element($type);
    $this->group = new Element(Element::RAW_VALUE);
    $this->order = new Element(Element::RAW_VALUE);
    $this->join = [];
    $this->offset = null;
    $this->limit = null;
    $this->data = [];

    return $this;
  }

  /**
   * Combine table based on the related column  
   */
  public function join(string $type, string $table, string $column, string $op, mixed $value, string $glue = 'AND'){
    if(!in_array($type, ['OUTER', 'LEFT', 'RIGHT', 'INNER']))
      throw new ErrorException('Join type not recognised');

    if(!isset($this->join[$table]))
      $this->join[$table] = ['type'=>$type, 'conditions'=>(new Element(Element::RAW_VALUE))->append($column, $op, $value, $glue)];
    else
      $this->join[$table]['conditions']->append($column, $op, $value, $glue);
    
    return $this;
    }

  /**
   * Join table that have matching values in both tables
   */
  public function innerJoin(string $table, string $column, string $op, mixed $value, string $glue = 'AND'){
    return $this->join('INNER', $table, $column, $op, $value, $glue);
  }

  public function outerJoin(string $table, string $column, string $op, mixed $value, string $glue = 'AND'){
    return $this->join('OUTER', $table, $column, $op, $value, $glue);
  }

  /**
   * Join and return all records from the first table and any matching records from the right table
   */
  public function leftJoin(string $table, string $column, string $op, mixed $value, string $glue = 'AND'){
    return $this->join('LEFT', $table, $column, $op, $value, $glue);
  }

  /**
   * Join and return all records from the right table and any matching records from the left table
   */
  public function rightJoin(string $table, string $column, string $op, mixed $value, string $glue = 'AND'){
    return $this->join('RIGHT', $table, $column, $op, $value, $glue);
  }

  /**
   * Filter records that matches the condition
   */
  public function where(string $column, string $op, mixed $value, string $glue = 'AND'){
    $this->where->append($column, $op, $value, $glue);

    return $this;
  }

  /**
   * Filter records based on the records that matches the possible values 
   */
  public function in(string $column, mixed $value, string $glue = 'AND'){
    $this->where($column, 'IN', $value, $glue);
    
    return $this;
  }

  /**
   * Filter records between a certain range
   */
  public function between(string $column, string $a, string $b, string $glue = 'AND'){
    $this->where($column, 'BETWEEN', [$a, $b], $glue);

    return $this;
  }

  /**
   * Filter records with aggregating functions
   */
  public function having(string $column, string $op, mixed $value, string $glue = 'AND'){
    $this->having($column, $op, $value, $glue);

    return $this;
  }

  /**
   * Groups record into their summary rows
   */
  public function group(string ...$columns){
    foreach($columns as $column)
      $this->group->append($column, '', '', ',');

    return $this;
  }

  /**
   * Sort the result set in ascending or descending order 
   */
  public function order(string $column, string $order = 'DESC'){
    if(!in_array($order, ['DESC', 'ASC']))
      throw new ErrorException('Order does not exists');

    $this->order->append("$column $order", '', '', ',');

    return $this;
  }

  /**
   * Limit the total amount of result 
   */
  public function limit(int $size){
    $this->limit = $size;
    
    return $this;
  }

  /**
   * Skips the offset rows  
   */
  public function offset(int $offset){
    $this->offset = $offset;

    return $this;
  }

  public function clear(){
    $this->where->clear();
    $this->group->clear();
    $this->having->clear();
    $this->order->clear();
    $this->offset = null;
    $this->limit = null;
  }

  public function build(){
    $this->add(sprintf('SELECT %s FROM %s ', join(',', $this->columns), $this->table));
    if(count($this->join))
      foreach($this->join as $k=>$v)
        $this->add("$v[type] JOIN $k ON $v[conditions] ");

    if($this->where->size()){
      $this->add(' WHERE '.$this->where, $this->where->data());
    }

    if($this->group->size())
      $this->add(' GROUP BY '.$this->group);

    if($this->having->size())
      $this->add(' HAVING '.$this->having, $this->having->data());

    if($this->order->size())
      $this->add(' ORDER BY '.$this->order);

    if(isset($this->offset))
      $this->add(" OFFSET $this->offset");
    
    if(isset($this->limit))
      $this->add(" LIMIT $this->limit");

    $this->clear();

    if(isset($this->database))
      return $this->database;
    else
      return $this;
  }
}