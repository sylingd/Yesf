<?php
namespace YesfApp\Model;

use Yesf\ModelAbstract;
use Yesf\ModelInterface;

class User extends ModelAbstract implements ModelInterface {
	protected $_table_name = 'user';
	protected $_primary_key = 'id';
}