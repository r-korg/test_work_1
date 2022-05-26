<?php

use PDO;

class DbConnectionFactory {
	/**
	 * Создаёт соединение с БД
	 * @return PDO
	 */
	public static function create(): PDO {
		$connectConfigData = include _DIR__.'/config.php';
		$dsn = 'mysql:dbname='.$connectConfigData['db_name'].';host='.$connectConfigData['db_host'];
        $user = $connectConfigData['db_user'];
        $password = $connectConfigData['db_pass'];
        $connection = new PDO($dsn, $user, $password);
		return $connection;
	}
}
