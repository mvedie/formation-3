<?php
/**
 * Created by PhpStorm.
 * User: gstenek
 * Date: 27/02/2017
 * Time: 16:42
 *
 * Builds the DAO
 */

namespace OCFram;


class PDOFactory {
	public static function getMysqlConnexion()
	{
		$db = new \PDO('mysql:host=localhost;dbname=formation', 'root', 'root');
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		
		return $db;
	}
}