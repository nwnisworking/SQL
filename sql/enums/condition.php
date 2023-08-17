<?php
namespace SQL\Enums;

enum Condition: string{
  case EQ = '=';

  case LT = '<';

  case GT = '>';

  case LTEQ = '<=';

  case GTEQ = '>=';

  case NOT = '<>';

  case IN = 'IN';

  case BETWEEN = 'BETWEEN';

  case LIKE = 'LIKE';

  public function build(array $data){
    $data[1] = $this->value;

    switch($this){
      case self::IN :
        $data[2] = sprintf('(%s)', trim(str_repeat('?,', count($data[2])), ','));
      break;
      case self::BETWEEN : 
        $data[2] = '? AND ?';
      break;
      default : 
        $data[2] = '?';
      break;
    }
    return $data;
  }
}