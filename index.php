<?php

require_once __DIR__ . '/vendor/autoload.php';

require 'functions.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

date_default_timezone_set('Asia/Tokyo');



 $spreadsheetId = getenv('SPREADSHEET_ID');


if (isset($_GET['sheetid'])) {
$spreadsheetId = $_GET['sheetid'];
  }
$arg_str = "/webpg.html?SEETID=$spreadsheetId";



if (isset($_GET['sheetname'])) {
  $sheetname = $_GET['sheetname'];

  $arg_str = $arg_str . "&SHEETNAME=$sheetname";
}


//$tgurl = "webpg.html" . $arg_str;

$log->addWarning( $arg_str );

include ($arg_str);



?>
