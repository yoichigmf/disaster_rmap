<?php

require_once __DIR__ . '/vendor/autoload.php';

require 'functions.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

date_default_timezone_set('Asia/Tokyo');

header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。





 $sheetname = 'シート1';

 if(isset($_GET['sheetname'])) {
 $sheetname = $_GET['sheetname'];
 }


$spreadsheetId = getenv('SPREADSHEET_ID');


 if(isset($_GET['sheetid'])) {
 $spreadsheetId = $_GET['sheetid'];
 }


$sheetd = GetSheet( $spreadsheetId, $sheetname );


//var_dump( $sheetd ); spread sheet

echo "<script>\n";


echo sprintf('var tgjson="{\\"type\\":\\"FeatureCollection\\", \\"features\\":[ ');

$isdone = false;


$uid_ar = array();   //  array of user id

$non_loc_ar = array();  // array of non location data

$ckey = 0;

$non_locr = array();    //  arrray of non location data for a user

foreach ($sheetd as $index => $cols) {

//echo "\nindex ${index}  ";  //////

     $dated = $cols[0];
     $userd = $cols[1];

     $kind = $cols[2];
     $url  = $cols[3];

     $stext = $cols[4];




   //  echo "\nkind ${kind}  ";  sample

     $topc = "";

     if ( strcmp( $kind ,'location' ) == 0 ) {   //  if record is location data

          $topc = "{";

          if ( $isdone   ) {
      	    $topc = " ,{";
     		  }
     		else   {
         		 $topc = "{ ";
         		 $isdone = true;

     		}

        $xcod =$cols[6];    //  coordinate
        $ycod = $cols[5];

        if (array_key_exists( $userd, $uid_ar)){   //  is the user id in the array ?

            $ckey = $uid_ar[$userd] + 1;
            $uid_ar[$userd] = $ckey;
               }
        else   {
            $ckey = 0;
            $uid_ar[$userd] = $ckey;

            $non_loc_ar[$userd] = array();
            }



         echo ${topc};
         echo sprintf(' \\"type\\":\\"Feature\\",\\"geometry\\":{\\"type\\": \\"Point\\", \\"coordinates\\":[%s,%s]}, \\"properties\\":{\\"日付\\":\\"%s\\",\\"ユーザ\\":\\"%s\\",\\"種別\\":\\"%s\\",\\"uid\\":\\"%d\\",\\"url\\":\\"%s\\",\\"テキスト\\":\\"%s\\"}}',$xcod,$ycod, $dated,$userd,$kind,$ckey,$url,$stext);



       }    // location
       else  {

   //    echo "  index2  ${index} ";

     //  echo "// kind " . $kind . " date ". $dated . "\n";

       if ( $index > 0 ){


           if (array_key_exists( $userd, $uid_ar)){


                   $ukey = $uid_ar[$userd];

                   if (array_key_exists($ukey ,$non_loc_ar[$userd] ) ) {


                  }
            else  {


                             $non_loc_ar[$userd][$ukey] = array();
                     }

                $non_locr = array( "日付"=> $dated,"ユーザ"=>$userd, "種別"=>$kind, 'url'=>$url, 'TEXT'=> $stext );

              $non_loc_ar[$userd][$ukey][] = $non_locr ;


                }
           else {
             //      $ckey = 0;
              //     $uid_ar[$userd] = $ckey;

             //      $non_loc_ar[$userd] = array();

           }




         //     $non_locr = array( "日付"=> $dated,"ユーザ"=>$userd, "種別"=>$kind, 'url'=>$url, 'TEXT'=> $stext );

           //   $non_loc_ar[$userd][$ukey][] = $non_locr ;

          }
       }


     }


echo "]} \" ; \n";

echo "</script>\n";
//var_dump( $non_loc_ar );


echo "<script>\n";

echo "var nlj = {};\n";


$line_array = ["\r\n", "\r", "\n"];

foreach( $non_loc_ar as $ikey => $ivalue ) {

  //    echo 'key => '. $ikey  .' value ' . $ivalue . ' <br>';

      echo "if( \"' .$ikey .'\" in nlj ) { \n";
      echo "  } \n";
      echo " else { \n";
      echo "     nlj[\"" . $ikey ."\"]= new Array();\n";
      echo " }\n";



     if ( count($ivalue) > 0 ) {
     echo "var vproc ={};\n";



      //  ユーザ別データ
      foreach ( $ivalue  as $vkey => $vrec ) {

     //  echo 'vkey => '. $vkey  .' value ' . $vrec . ' <br>';
       echo "var varr = new Array();\n";

          foreach ( $vrec as $vv ) {
            //  echo 'ercord '. $vv . 'hh<br>';

             echo  "var vvc = {};\n";

             echo "vvc.date=\"". $vv["日付"] ."\";\n";
             echo "vvc.user=\"". $vv["ユーザ"] ."\";\n";
             echo "vvc.kind=\"". $vv["種別"] ."\";\n";

              echo "vvc.url=\"". $vv["url"] ."\";\n";


             $vtext= str_replace($line_array, '',  $vv["TEXT"]);
             echo "vvc.text=\"". $vtext ."\";\n";

             echo "varr.push( vvc );\n";

          }

          if ( strlen($vkey) > 0 ) {

          echo "vproc[\"" . $vkey ."\"]= varr;\n" ;
          }
          else {

          echo "vproc[\"" . $vkey ."\"]= {};\n" ;
          }


          echo "nlj[\"". $ikey . "\"]=vproc;\n";
         }
        }


    }




?>
