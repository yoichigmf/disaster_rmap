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
$ag_str = "/webpg.html?SEETID=$spreadsheetId";



if (isset($_GET['sheetname'])) {
  $sheetname = $_GET['sheetname'];

  $ag_str = $ag_str . "&SHEETNAME=$sheetname";
}


//$tgurl = "webpg.html" . $arg_str;

$log->addWarning( $ag_str );

include ($ag_str);



?>
