<?php
include "mainController.php";

/**
 * Работа с сессиями. Долна быть здесь, до инициализации любых действий по отображению контента
 */
if (!$_SESSION) {
    session_start();
}
if ($_SESSION) {
//    session_destroy();
}

/**
 * создание экземпляра класса
 */
new index();

/**
 * Класс для общения системы с пользоватеем. Все запросы идут через него
 *
 * Class index
 */
class index
{
    protected $conn;

    public function __construct()
    {

        $this->index();
        $this->conn = new Common\dbConnection\dbConnection();
    }

    public function index()
    {

        if ($_SERVER["REQUEST_URI"] === '/') {
            $_SERVER["REQUEST_URI"] = 'index';
        }

        if ($_SERVER["REQUEST_URI"] === '/edit_accident') {
            $_SERVER["REQUEST_URI"] = 'editAccident';
        }

        if (in_array('details',explode('/',$_SERVER['DOCUMENT_URI']))) {
            if (strpos($_SERVER['argv'][0],'delete') !== false) {
                $_SESSION['get'] = str_replace('delete=','', $_SERVER['argv'][0]);
                $_SERVER["REQUEST_URI"] = 'deleteRecord';
            } else {
                $_SERVER["REQUEST_URI"] = 'details';
                $_SESSION['get'] = str_replace('submit=','', $_SERVER['QUERY_STRING']);
            }

        }

        $start = new mainController();
        $a = ltrim($_SERVER["REQUEST_URI"], '/');
        if ($_SESSION['login']) {
            echo $start->$a();
        } elseif ($_SERVER["REQUEST_URI"] === '/register') {
                echo $start->register();
            } else {
                echo $start->login();
        }
    }

    private function checkSign(string $login, string $password): bool
    {
        $user = $this->conn->getLogin($login);

        return $user[0]['login'] && $password === $user[0]['password'];
    }
}
