<?php
use DB\Database;
use DB\Query;
require_once 'autoload.php';

$db = (new Database)->connect('localhost', 'sakila', 'root', '');
$v = $db
->update('payments', ['a'=>'x', 'b'=>'y'])
->changeDataType(':')
->where("a", "=", [':c'=>"b"])
->build()
;