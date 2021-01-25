<?php

include "dbConnection.php";

/**
 * Класс для работы с отображением страниц и обработки получченых от них данных
 *
 * Class mainController
 */
class mainController
{
    protected $conn;

    /**
     * Конструктор для класса mainController выполняется при создании экземпляра класса
     *
     * mainController constructor.
     */
    public function __construct() {
        $this->conn = new Common\dbConnection\dbConnection();
    }

    /**
     * Эта функция берет параметры из БД о всех ДТП и отображает на главной странице
     *
     * @route index
     * @return string
     */
    public function index(): string
    {
//        var_dump($this->conn->findRecordWithTwoVaribles('drivers','fio', 'accident_id', 'guilty', 95, 1)[0]['fio']);
var_dump($this->conn->getGuiltyFio(95)[0][0]);

        $basTemplate = $this->renderPage('mainView');
        $allIncidents = $this->conn->getAllRecordsFromTable('accidents', 100);

        $bodyTable = $this->setTableTags($allIncidents);
        $sContent = preg_replace_callback('|({{)(.+)(}})|iUs',
            function($matches) use ($bodyTable)
            {
                $matches[2] = $bodyTable;
                return $matches[2];
            }
            ,$basTemplate);

        return html_entity_decode($sContent);

    }

    /**
     * эта функция отображает форму для заполнения информации о новом ДТП
     *
     * @route newAccident
     * @return string
     */
    public function newAccident(): string
    {
        return $this->renderPage('newAccident');
    }

    /**
     * Эта функция отобраает форму об участниках ДТП
     *
     * @return string
     */
    public function addDrivers(): string
    {
        $params = $_POST;
        $render = $this->renderPage('add_drivers');

        if (!empty($params['number_of_ref'])) {
            $_SESSION['number_of_ref'] = $params['number_of_ref'];
            $params['accident_date_time'] = date('Y-m-d H:i:s', strtotime($params['accident_date_time']));
            $_SESSION['last_insert_id'] = $this->conn->insertWithKeys('accidents', $params);
        } elseif (!empty($_SESSION['number_of_ref']) && $_SESSION['number_of_ref'] > 1) {
            if (array_key_exists('fio', $params)) {
                $params['accident_id'] = $_SESSION['last_insert_id'];
                $_SESSION['guilty'] = $params['guilty'] === 'on';
                $this->conn->insertWithKeys('drivers', $params);
            }
            $_SESSION['number_of_ref'] -= 1;
        } else {
            $params['accident_id'] = $_SESSION['last_insert_id'];
            $this->conn->insertWithKeys('drivers', $params);
            unset($_SESSION);
            $_SERVER["REQUEST_URI"] = 'index';
            $render = $this->index();
            $_SESSION['end'] = true;
        }

        if ($_SESSION['guilty']) {
            $render = $this->replaceTag($render,'disabled');
        } else {
            $render = $this->replaceTag($render,'');
        }

        return $render;
    }

    public function details()
    {
       $id = str_replace('submit=','', $_SESSION['get']);

       $accident = $this->conn->getAccident($id);
       $drivers = $this->conn->getAllDriversOfAccident($id);
        var_dump($drivers, $accident[0]);
        $table ='<table>
                <th>Дата и время ДТП</th>
                <th>Номер справки в ГИБДД</th>
                <th>Участники</th>
                <th>Повреждения</th><tr>';

        foreach($accident[0] as $key => $row) {
            if ($key === 'number_of_ref' ) {
                $table .= '<td>';
                foreach ($drivers as $driver) {
                    if ($driver['guilty']) {
                        $table .= $driver['fio'] . '(Виновник)<br>';
                    } else {
                        $table .= $driver['fio'] . '<br>';
                    }
                }
                $table .= '</td>';
            } else {
                 $table .= '<td>' . $row . '</td>';
            }
        }

        $table .= '</tr></table>';

//var_dump($table);

        return html_entity_decode($this->replaceTag($this->renderPage('details'), htmlentities($table)));

//       return $this->renderPage('details');

    }


    /**
     * Эта базовая функция отображения страниц из шаблонов, расположенных в папке View
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    private function renderPage(string $name, array $data = []): string
    {
        return file_get_contents('mainTemplate.html') . file_get_contents(__DIR__ . "/Views/" .$name . '.html');
    }

    /**
     * Эта функция генерирует таблицу ДТП для дальнейшего отображения на главной странице
     *
     * @param array $data
     * @return string
     */
    private function setTableTags(array $data): string
    {
        $this->conn = new Common\dbConnection\dbConnection();


        $result = '<form action="details"><table>
        <th>Номер справки ГИБДД</th>
        <th>Дата и время</th>
        <th>Причина ДТП</th>
        <th>Количество участников</th>
        <th>Место ДТП</th>
        <th>ФИО виновника</th>
        <th>Открыть</th>
    ';
        $id = 0;
        foreach ($data as $rows) {
            $result = $result . "<tr>";
            foreach ($rows as $key => $element) {
                if ($key === 'id') {
                    $id = $element;
                }
              $result = $result . "<td>" . $element . "</td>";
            }
            $guiltyFio = $this->conn->getGuiltyFio($id)[0][0];
            $result .= "<td>" . $guiltyFio . "</td>";
            $result .= '<td><button name="submit" value=' . $id . '>Подробнее</button></td>';
            $result = $result . "</tr>";
        }
        $result = $result . '</table></form>';
        return htmlentities($result);
    }

    /**
     * @param string $page
     * @param string $tag
     * @return string|string[]|null
     */
    private function replaceTag(string $page, string $tag)
    {
        return preg_replace_callback('|({{)(.+)(}})|iUs',
            function($matches) use ($tag)
            {
                $matches[2] = $tag;
                return $matches[2];
            }
            ,$page);
    }
}