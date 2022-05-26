<?php

namespace Gateway;

use PDO;

class User extends PDO
{
    protected PDO $connection;
	protected string $tableName = 'Users';
	private string $error;

	public function __construct(PDO $connection) {
		$this->connection = $connection;
	}

	
    /**
     * Возвращает список пользователей старше заданного возраста.
     * @param int $age
     * @param int $limit
     * @return array
     */
    public function getFromAge(int $age, ?int $limit = null): array
    {
		$query = 'SELECT id, name, lastName, from, age, settings FROM '.$this->tableName.' WHERE age > ?';
		$queryParams = [$age];
		if($limit){
			$query .= ' LIMIT ?';
			$queryParams[] = $limit;
		};
        $stmt = $this->connection->prepare($query);
        $stmt->execute($queryParams);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $settings = json_decode($row['settings']);
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age'],
                'key' => $settings['key']??'',
            ];
        }

        return $users;
    }

    /**
     * Возвращает список пользователей по списку имён.
     * @param array $names
     * @return array
     */
    public function getByNames(array $names): array
    {
		$names = array_map(fn($name)=>(string)$name, $names);
		$in  = str_repeat('?,', count($names) - 1) . '?';
        $stmt = $this->connection->prepare('SELECT id, name, lastName, from, age, settings FROM '.$this->tableName.' WHERE name IN ('.$in.')');
        $stmt->execute($names);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $settings = json_decode($row['settings']);
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age'],
                'key' => $settings['key']??'',
            ];
        }

        return $users;
    }

    /**
     * Добавляет пользователя в базу данных. (я тут сделал бы один входной параметр с внутренней проверкой наличия обязательных ключей)
     * @param string $name
     * @param string $lastName
     * @param int $age
     * @param int $from
     * @param array $settings
     * @return string
     */
    public function add(string $name, string $lastName, int $age, int $from = 0, array $settings = []): string
    {
		$sth = $this->connection->prepare("INSERT INTO Users (name, lastName, age, from, settings) VALUES (:name, :age, :lastName, :from, :settings)");
		$queryParams = [
			'name' => $name, 
			'age' => $age, 
			'lastName' => $lastName,
			'from' => $from,
			'settings' => json_encode($settings),
		];
        $sth->execute($queryParams);

        return $this->connection->lastInsertId();
    }
	/**
	 * Добавление списка пользователей
	 * @param array $users
	 * @return array
	 * @throws \Exception
	 */
	public function addList(array $users): array {
		$ids = [];
		$this->error = null;
		try {
			$this->connection->beginTransaction();
			foreach ($users as $user) {
				if (!isset($user['name']) || !isset($user['lastName']) || !isset($user['age'])) {
					throw new \Exception('Wrong format user data');
				};
				$ids[] = $this->gwUser->add($user['name'], $user['lastName'], $user['age'], $user['from']??0, $user['settings']??[]);
			};
			$this->connection->commit();
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
			$this->connection->rollBack();
		};
		return $ids;
	}
	/**
	 * Возвращает текст ошибки для некоторых действий
	 * @return string|null
	 */
	public function getError(): ?string{
		return $this->error; 
	}
	
}