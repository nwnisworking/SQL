<?php
namespace SQL;

use Exception;
use PDO;
use SensitiveParameter;
use SQL\Query\Delete;
use SQL\Query\Insert;
use SQL\Query\Update;
use SQL\Query\Select;

class Driver{
  private PDO $db;
  
  private Select|Insert|Update|Delete $query;
  
  public static string $host = 'localhost';

  public static string $user = 'root';

  public static string $dbname = 'colab';

  public static string $password = '';

  public function __construct(){
    $this->db = new PDO('mysql:host='.self::$host.';dbname='.self::$dbname, self::$user, self::$password);
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
  }

  public function select(string $table, array $columns = ["*"]): Select{
    return $this->query = (new Select($table, $columns))->setDriver($this);
  }

  public function insert(string $table, array $columns = []): Insert{
    return $this->query = (new Insert($table, $columns))->setDriver($this);
  }

  public function update(string $table, array $columns): Update{
    return $this->query = (new Update($table, $columns))->setDriver($this);
  }

  public function execute(int $type = PDO::FETCH_ASSOC, int|string|callable|null $opt = null): array{
    if(!isset($this->db))
      throw new Exception();
    
    $prepare = $this->db->prepare($this->query);
    
    if(!$prepare->execute($this->query->data()))
      return [
        'code'=>$prepare->errorCode(),
        'message'=>$prepare->errorInfo()[2]
      ];

    if($this->query->getName() === 'Select'){
      $fetch = $prepare->fetchAll(...array_filter([$type, $opt], fn($e)=>$e));
      
      if(count($fetch) == 1)
        return $fetch[0];
      
        elseif(count($fetch) > 2)
          return $fetch;

      else
        return [];
    }
    else
      return [
        'rows'=>$prepare->rowCount(),
        'lastId'=>$this->db->lastInsertId()
      ];
  }

  public static function init(): Driver{
    return new self();
  }
}