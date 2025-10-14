<?php
require_once __DIR__ . '/../../init.php';

abstract class Model {
    protected mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }
}
