<?php
namespace DB;
use DB\Query\Delete;
use DB\Query\Insert;
use DB\Query\Select;
use DB\Query\Update;
use ErrorException;
use PDO;
use PDOException;
use PDOStatement;

class Database{
  public ?PDO $pdo = null;

  protected Select|Insert|Update|Delete $query;

  private PDOStatement $statement;

  /**
   * Connect to MySQL server 
   */
  public function connect(?string $host = null, ?string $db = null, ?string $user = null, ?string $pass = null){
    $host = $host ?? $_ENV['SQL_HOST'];
    $db = $db ?? $_ENV['SQL_DB'];
    $user = $user ?? $_ENV['SQL_USER'];
    $pass = $pass ?? $_ENV['SQL_PASS'];

    if(!isset($this->pdo))
      $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

    return $this;
  }

  /**
   * Commits a transaction
   */
  public function commit(){
    $this->connect();
    $this->pdo->commit();
    
    return $this;
  }

  /**
   * Rollback to the previous savepoint
   */
  public function rollback(){
    $this->connect();
    $this->pdo->rollBack();

    return $this;
  }

  /**
   * Start transaction and disable autocommit
   */
  public function startTransaction(){
    $this->connect();
    $this->pdo->beginTransaction();

    return $this;
  }

  /**
   * Get the last inserted id
   */
  public function insertId(){
    return $this->pdo->lastInsertId();
  }

  /**
   * Execute SQL statement
   */
  public function execute(){
    $query = $this->query->getStatement($data);
    $prepare = $this->statement = $this->pdo->prepare($query);

    try{
      $prepare->execute($data);
      return true;
    }
    catch(PDOException $err){
      return false;
    }
  }

  public function errorInfo(){
    if(isset($this->statement))
      return $this->statement->errorInfo();
    return null;
  }

  /**
   * Fetch row as an associative array
   */
  public function fetchAssoc(bool $fetch_all = null){
    return $this->fetch(PDO::FETCH_ASSOC, [], $fetch_all);
  }

  public function fetchArray(bool $fetch_all = null){
    return $this->fetch(PDO::FETCH_NUM, [], $fetch_all);
  }

  public function fetchClass(string $class, bool $fetch_all = false){
    return $this->fetch(PDO::FETCH_CLASS, [$class], $fetch_all);
  }

  private function fetch(int $mode, array $params, bool $fetch_all = false){
    if(!isset($this->statement))
      throw new ErrorException('Database was not executed');

    if($fetch_all)
      return call_user_func_array([$this->statement, 'fetchAll'], [$mode, ...$params]);
    
    return call_user_func_array([$this->statement, 'fetch'], [$mode, ...$params]);
  }

  public function freeResult(){
    if(isset($this->statement))
      $this->statement->closeCursor();

    return $this;
  }

  public function select(string $table, array $columns = ['*']){
    return ($this->query = new Select($table, $columns))->setDatabase($this);
  }

  public function insert(string $table, array $columns){
    return ($this->query = new Insert($table, $columns))->setDatabase($this);
  }

  public function update(string $table, array $columns){
    return ($this->query = new Update($table, $columns))->setDatabase($this);
  }

  public function delete(string $table){
    return ($this->query = new Delete($table))->setDatabase($this);
  }
}