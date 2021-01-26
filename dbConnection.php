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
        USER_PASSWORD   = "root"
    ;

    /**
     * dbConnection constructor.
     */
    public function __construct()
    {
        $this->connection = new PDO(
            "mysql:host=localhost;dbname=gibdd",
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
            $stm = $this->connection->query("SELECT d.id, fio, guilty FROM drivers as d left join accidents as a on a.id = d.accident_id WHERE accident_id={$id}", PDO::FETCH_ASSOC);
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
     * @param string $id
     * @return array
     */
    public function getCarViaAccidentId(string $id): array
    {
        try {
            $stm = $this->connection->query("select number, owner_id from cars where owner_id in (select id from drivers where accident_id = {$id})", PDO::FETCH_ASSOC);
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

    /**
     * @param array $info
     * @return array
     */
    public function editAccidentInfo(array $info): array
    {
        $accident = $this->getAccident($_POST['id']);

       //Обновление информации о виновном водителе
            $query = "UPDATE `driver` SET `fio` = :name WHERE `accident_id` = :id and guilty = true";
            $params = [
                ':id' => $info['id'],
                ':name' => $info['fio_guilty']
            ];
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            unset($info['fio_guilty']);

            $fioArray =[];
            $carArray = [];
        foreach ($info as $key => $item) {
            if (strpos($key,'fio')) {
                $fioArray[str_replace('fio', '', $key)] = $item;
            } elseif (strpos($key,'car_number')) {
                $carArray[str_replace('car_number', '', $key)] = $item;
            }

            }
        //Обновление информации о других водителях
        foreach ($fioArray as $id => $fio) {
            $query = "UPDATE `driver` SET `fio` = :name WHERE `accident_id` = :id";
            $params = [
                ':id' => $id,
                ':name' => $fio
            ];
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
        }

        //Обновление номера машины
        foreach ($carArray as $id => $number) {
            $query = "UPDATE `cars` SET `number` = :number WHERE `owner_id` = :id";
            $params = [
                ':number' => $number,
                ':id' => $id
            ];
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
        }

        //Обновление Даты и времени ДТП
        $query = "UPDATE `accidents` SET `accident_date_time` = :accident_date_time WHERE `id` = :id";
        $params = [
            ':id' => $info['id'],
            ':accident_date_time' => $info['accident_date_time']
        ];
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        //Обновление причины ДТП
        $query = "UPDATE `accidents` SET `cause_accident` = :cause_accident WHERE `id` = :id";
        $params = [
            ':id' => $info['id'],
            ':cause_accident' => $info['cause_accident']
        ];
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        //Обновление адреса ДТП
        $query = "UPDATE `accidents` SET `accident_address` = :accident_address WHERE `id` = :id";
        $params = [
            ':id' => $info['id'],
            ':accident_address' => $info['accident_address']
        ];
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        $_SESSION = $info;
        return $info;
    }

    public function deleteRecordAboutAccident(int $id)
    {
        $query = "DELETE FROM `accidents` WHERE `id` = ?";
        $params = [$id];
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
    }


    /**
     * @param string $login
     * @return array
     */
    public function getLogin(string $login): array
    {
        $stm = $this->connection->prepare('SELECT login, password FROM users where login = :login');
        $params = [
            ':login' => $login
        ];
        $stm->execute($params);
        return $stm->fetchAll();
    }
}

