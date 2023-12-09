<?php
namespace SQL;

abstract class Query{
  protected ?array $columns;

  protected ?string $table;

  private Driver $driver;

  public function __construct(?string $table, ?array $columns = []){
    $this->columns = $columns;
    $this->table = $table;
  }

  public function execute(){
    return $this->driver->execute();
  }

  public function setDriver(Driver $driver): Query{
    $this->driver = $driver;

    return $this;
  }

  public function getName(): string{
    return substr(static::class, strrpos(static::class, '\\') + 1);
  }

  public abstract function data(): array;

  public abstract function __toString();
}