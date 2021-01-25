<?php

namespace Common\dbConnection;

use PDO;
use PDOException;

/**
 * Класс для работы с БД
 *
 * Class dbConnection
 * @package Common\dbConnection
 */
class dbConnection
{
    /**
     * @var
     */
    public $connection;

    const
        USER_NAME       = "root",
        USER_PASSWORD   = "rootpassword"
    ;

    /**
     * dbConnection constructor.
     */
    public function __construct()
    {
        $this->connection = new PDO(
            "mysql:host=db;dbname=gibdd",
            self::USER_NAME,
            self::USER_PASSWORD
        );
    }

    /**
     * Получем все записи из нужной таблицы
     *
     * @param string $table
     * @param int $limit
     * @return array
     */
    public function getAllRecordsFromTable(string $table, int $limit = 10):array
    {
        $result = [];
        try {
            foreach($this->connection->query('SELECT * from ' . $table  . ' order by id desc ' . ' limit ' . $limit) as $key => $row) {
                $result[$key] = $row;
            }
            for ($i = 0; $i < count($result); $i++) {
                for($j=0; $j < count($result[$i]); $j++) {
                    unset($result[$i][$j]);
                }
            }
            $this->connection = null;
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
        return $result;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getGuiltyFio(int $id): array
    {
        try {
            $stm = $this->connection->query("SELECT fio FROM drivers as d left join accidents as a on a.id = d.accident_id WHERE accident_id={$id} AND guilty=1");
        } catch (PDOException $e) {
            print_r("Error!: " . $e->getMessage() . "<br/>");
            die();
        }
        return $stm->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAllDriversOfAccident(int $id): array
    {
        try {
            $stm = $this->connection->query("SELECT fio, guilty FROM drivers as d left join accidents as a on a.id = d.accident_id WHERE accident_id={$id}", PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print_r("Error!: " . $e->getMessage() . "<br/>");
            die();
        }
        return $stm->fetchAll();
    }

    /**
     * @param string $id
     * @return array
     */
    public function getAccident(string $id): array
    {
        try {
            $stm = $this->connection->query("SELECT * FROM accidents WHERE id={$id}", PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print_r("Error!: " . $e->getMessage() . "<br/>");
            die();
        }
        return $stm->fetchAll();
    }

    /**
     * Добавляем в нужную таблицу определенную запись
     *
     * @param string $table
     * @param array $values
     * @return false|int
     */
    public function insertWithKeys(string $table, array $values)
    {
        $array_keys = implode(",", array_keys($values));
        $values = "'" . implode("','", $values) . "'";
       try {
          $this
               ->connection
               ->exec(
                   "INSERT INTO gibdd.{$table} ($array_keys) VALUES ($values)"
               );
           $result = $this->connection->lastInsertId();
       } catch (PDOException $e) {
           $result = "Error!: " . $e->getMessage() . "<br/>";
       }

       return $result;
    }
}
