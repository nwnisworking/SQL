<?php
require_once 'autoload.php';

use SQL\DB;
use SQL\Enums\Condition;

class Auth extends DB{}

$auth = (new Auth())
->connect('localhost', 'sakila', 'root', '')
->select('payment')
->limit(10)
->innerJoin('customer', 'customer.customer_id', Condition::EQ, 'payment.customer_id')
->where('payment.customer_id', Condition::EQ, 535)
->build();