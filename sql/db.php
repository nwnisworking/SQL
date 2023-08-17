<?php
namespace SQL;
use ErrorException;
use PDO;
use PDOException;
use SQL\Delete;
use SQL\Select;
use SQL\Update;
use SQL\Insert;


if(!function_exists('array_is_list')){
  function array_is_list(array $array){
    return count(array_filter(array_keys($array), 'is_string')) > 0;
  }
}

abstract class DB{
  private ?PDO $conn;

  private Select|Delete|Update|Insert $query;

  public function connect(string $host, string $dbname, string $user, string $pass){
    $this->conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    return $this;
  }

  public function select(string $table, array $column = ['*']): Select{
    if(!array_is_list($column))
      throw new ErrorException('$column requires a sequential array');
    return $this->query = (new Select($table, $column))->setConnection($this)->prependQuery(isset($this->query) ? $this->query : null);
  }

  public function insert(string $table, array $column): Insert{
    return $this->query = (new Insert($table, $column))->setConnection($this)->prependQuery(isset($this->query) ? $this->query : null);
  }

  public function delete(string $table): Delete{
    return $this->query = (new Delete($table))->setConnection($this)->prependQuery(isset($this->query) ? $this->query : null);
  }

  public function update(string $table, array $column): Update{
    if(!array_is_list($column))
      throw new ErrorException('$column requires an associative array');

    return $this->query = (new Update($table, $column))->setConnection($this)->prependQuery(isset($this->query) ? $this->query : null);
  }

  public function fetchAssoc(){
    $prepare = $this->conn->prepare($this->query->stmt);

    try{
      $prepare->execute($this->query->data);

      return ['status'=>'OK', 'result'=>$prepare->fetchAll(PDO::FETCH_ASSOC)];
    }
    catch(PDOException $err){
      return ['status'=>'ERROR', 'code'=>$err->errorInfo[1], 'error_message'=>$err->errorInfo[2]];
    }
  }

  public function close(){
    $this->conn = null;
    return $this;
  }
}