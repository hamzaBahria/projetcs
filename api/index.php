<?php

$_ENV['APP_BASE_PATH'] = dirname(__DIR__).'/backend';
$_SERVER['SCRIPT_NAME'] = '/api/index.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require dirname(__DIR__).'/backend/public/index.php';
