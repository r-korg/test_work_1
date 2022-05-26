<?php

namespace Manager;

use Gateway\User as GwUser;

class User {

	protected GwUser $gwUser;

	function __construct() {
		$this->gwUser = DbConnectionFactory::create();
	}

	/**
	 * Возвращает пользователей старше заданного возраста.
	 * @param int $ageFrom
	 * @return array
	 */
	function getUsersFromAge(int $age, int $limit = 10): array {
		return $this->gwUser->getFromAge($age, $limit);
	}

	/**
	 * Возвращает пользователей по списку имен.
	 * @return array
	 */
	public static function getByNames(array $names): array {
		return $this->gwUser->getByNames($names);
	}

	/**
	 * Добавляет пользователей в базу данных.
	 * @param array $users
	 * @return array
	 */
	public function addUsers(array $users): array {
		return $this->gwUser->addList($users);
	}

}
