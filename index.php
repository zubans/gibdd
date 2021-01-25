<?php
include "mainController.php";

/**
 * Работа с сессиями. Долна быть здесь, до инициализации любых действий по отображению контента
 */
if (!$_SESSION['number_of_ref']) {
    session_start();
}
if ($_SESSION['end']) {
    session_destroy();
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
    public function __construct()
    {

        $this->index();
    }

    public function index()
    {
        if ($_SERVER["REQUEST_URI"] === '/') {
            $_SERVER["REQUEST_URI"] = 'index';
        }
        if ($_SERVER['QUERY_STRING']) {
            $_SERVER["REQUEST_URI"] = 'details';

        }
        $start = new mainController();
        $a = ltrim($_SERVER["REQUEST_URI"], '/');
        echo $start->$a();
    }
}
