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


    public function register(): string
    {
        $_SESSION['post'] = $_POST;

        if ($_POST['login'] && $_POST['password'] && !$this->checkLoginPassword()) {
            $this->conn->insertWithKeys('users',$_POST);
            $_SESSION['login'] = true;
            return $this->login();
        } elseif ($_POST['login'] && $this->checkLoginPassword()) {
            $_SESSION['login'] = true;
            $this->index();
        } else {
            return $this->renderPageWithoutStylesAndHead('register');
        }
    }

    public function login(): string
    {
        if ($_POST['login'] && $this->checkLoginPassword()) {
            $_SESSION['login'] = true;
           return $this->index();
        } else {
            return $this->renderPageWithoutStylesAndHead('login');
        }
    }

    /**
     * Эта функция берет параметры из БД о всех ДТП и отображает на главной странице
     *
     * @route index
     * @return string
     */
    public function index(): string
    {
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
//            $params['accident_date_time'] = date('Y-m-d H:i:s', strtotime($params['accident_date_time'])); // Русский вариант даты и времени
            $_SESSION['last_insert_id'] = $this->conn->insertWithKeys('accidents', $params);
        } elseif (!empty($_SESSION['number_of_ref']) && $_SESSION['number_of_ref'] > 1) {
            if (array_key_exists('fio', $params)) {
                $params['accident_id'] = $_SESSION['last_insert_id'];
                $params['guilty'] = $params['guilty'] === 'on';
                $car_params = [
                    "mark"    => $params['car_mark'],
                    'number'  => $params['car_number'],
                    'color'   => $params['car_color'],

                ];
                unset($params['car_mark']);
                unset($params['car_number']);
                unset($params['car_color']);

                $car_params['owner_id'] = $this->conn->insertWithKeys('drivers', $params);
var_dump($car_params);
                $this->conn->insertWithKeys('cars', $car_params);
            }
            $_SESSION['number_of_ref'] -= 1;
        } else {
            $params['accident_id'] = $_SESSION['last_insert_id'];
            $car_params = [
                "mark"    => $params['car_mark'],
                'number'  => $params['car_number'],
                'color'   => $params['car_color'],

            ];
            unset($params['car_mark']);
            unset($params['car_number']);
            unset($params['car_color']);

            $car_params['owner_id'] = $this->conn->insertWithKeys('drivers', $params);
            $this->conn->insertWithKeys('cars', $car_params);

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

    /**
     * @return string
     */
    public function details(): string
    {

        $id = $_SESSION['get'];

        $accident = $this->conn->getAccident($id);
        $drivers = $this->conn->getAllDriversOfAccident($id);
        $car = $this->conn->getCarViaAccidentId($id);
        $tableHeader = '<table>
                <th>ГосНомер автомобиля</th>
                <th>Дата и время ДТП</th>
                <th>Номер справки в ГИБДД</th>
                <th>Участники</th>
                <th>Повреждения</th>
                <th>Обновить</th>
                <th>Выгрузить отчет</th>
                <tr>';
        $table = '';
        $table .= '<form action="edit_accident" method="post"><input type="hidden" name="id" value="' . $id . '">';
        $sheetOfDrivers = '';

        $counter = 0;
        foreach($accident[0] as $key => $row) {
            if ($key === 'number_of_ref' ) { // если поле об участниках ДТП
                $table .= '<td>';
                foreach ($drivers as $driver) {
                    if ($driver['guilty']) { // если поле о виновности участника правда
                        $table .= '<input type="text" name="fio_guilty" value="' . $driver['fio'] . '">(Виновник)<br>';
                    } else {
                        $table .= '<input type="text" name="fio' . $driver['id'] . '" value="' . $driver['fio'] . '"><br>';
                        $counter++;
                    }
                }
                $table .= '</td>';
            } elseif ($key !== 'id') {
                if ($key === 'accident_date_time') { // если поле о дате и времени
                    $table .= '<td><input type="text" name="accident_date_time" value="' . $row . '"></td>';
                } elseif ($key === 'cause_accident') {
                    $table .= '<td><input type="text" name="cause_accident" value="' . $row . '"></td>';
                } elseif ($key === 'accident_address') {
                    $table .= '<td><input type="text" name="accident_address" value="' . $row . '"></td>';
                }
            } else {
                $table .= '<td>';
                foreach ($drivers as $driver) {
                    foreach ($car as $oneCar) {
                        if ($driver['id'] === $oneCar['owner_id'])
                            $table .= '<input type="text" name="car_number' . $oneCar['id'] . '" value="' . $car[0]['number'] . '"><br>';
                        }
                    }
                $table .= '</td>';

                }
        }

        $table .= '<td><input type="submit"></td><td></form><form action="getReport" method="post"><button name="report" value="' . $id . '">Скачать отчет</button></form></td></tr></table>';

        foreach ($drivers as $driver) {
            if ($driver['guilty']) {
                $sheetOfDrivers .= $driver['fio'] . '(Виновник); ';
            } else {
                $sheetOfDrivers .= $driver['fio'] . '; ';
            }
        }

        $header[0] = ['Номер Справки ГИБДД', 'Дата и время ДТП', 'Номер справки в ГИБДД', 'Участники', 'Повреждения'];
        $header[1] = [];
        $accident[0]['number_of_ref'] = $sheetOfDrivers;
        $header[2] = $accident[0];


        return html_entity_decode($this->replaceTag($this->renderPage('details'), htmlentities($tableHeader . $table)));
    }

    /**
     * @return void
     */
    public function getReport(): void
    {
        $id = $_POST['report'];
        $accident = $this->conn->getAccident($id);
        $drivers = $this->conn->getAllDriversOfAccident($id);
        $sheetOfDrivers = '';


        foreach ($drivers as $driver) {
            if ($driver['guilty']) {
                $sheetOfDrivers .= $driver['fio'] . '(Виновник); ';
            } else {
                $sheetOfDrivers .= $driver['fio'] . '; ';
            }
        }

        $header[0] = ['Номер Справки ГИБДД', 'Дата и время ДТП', 'Номер справки в ГИБДД', 'Участники', 'Место ДТП'];
        $header[1] = [];
        $accident[0]['number_of_ref'] = $sheetOfDrivers;
        $header[2] = $accident[0];

        $this->exportToCSV($header,"DTP1.csv");
    }

    /**
     * @return string
     */
    public function deleteRecord(): string
    {
        $this->conn->deleteRecordAboutAccident($_SESSION['get']);
        return $this->index();
    }

    public function editAccident(): string
    {
        $this->conn->editAccidentInfo($_POST);

       return $this->index();
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
     * Эта базовая функция отображения страниц из шаблонов, расположенных в папке View
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    private function renderPageWithoutStylesAndHead(string $name, array $data = []): string
    {
        return file_get_contents(__DIR__ . "/Views/" .$name . '.html');
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
            $result .= '<td><button name="submit" value=' . $id . '>Подробнее</button><button name="delete" value="' . $id . '">Удалить</button></td>';
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

    private function exportToCSV($table, $filename = 'export.csv')
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        $fp = fopen('php://output', 'w');

        foreach ($table as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    private function checkLoginPassword(): bool
    {
        $user = $this->conn->getLogin($_POST['login']);
        var_dump($user);
        return $user[0]['password'] && $_POST['password'] === $user[0]['password'];
    }
}