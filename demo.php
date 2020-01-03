<?php
require_once "./vendor/autoload.php";

use Framework\SwServer\ServerManager;

$a=ServerManager::getInstance();

print_r($a);