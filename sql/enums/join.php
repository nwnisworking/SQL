<?php
namespace SQL\Enums;

enum Join{
  case INNER;

  case CROSS;

  case LEFT;

  case RIGHT;

  case OUTER;

  public function build(array $data){
    $str = "{$this->name} JOIN $data[table] ON ";

    array_pop($data['args'][count($data['args']) - 1]);

    return $str.=join(" ", array_map(fn($e)=>join(' ', $e), $data['args']));
  }
}