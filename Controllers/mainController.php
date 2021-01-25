<?php

namespace mainController1;

include "Common\dbConnection.php";
include "Common\Html.php";

use Common\dbConnection\dbConnection;
use Html;

class mainController
{
    protected $conn;

    public $html;

    public function __construct() {
        $this->conn = new dbConnection();
        $this->html = Html::class;
    }

    /**
     * @return array
     */
    public function getAllAccidents(): array
    {
        return $this->conn->getAllRecordsFromTable('accidents.sql');
    }

    public function newAccident(): bool
    {

    }

    public function renderPage(string $name)
    {
        Html::render($name);
    }
}