<?php 

require_once __DIR__ . '/vendor/autoload.php';


use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

date_default_timezone_set('Asia/Tokyo');



function Getsheets($spreadsheetID, $client) {
    $sheets = array();    
    // Load Google API library and set up client
    // You need to know $spreadsheetID (can be seen in the URL)
    

    $sheetService = new Google_Service_Sheets($client);   
    $spreadSheet = $sheetService->spreadsheets->get($spreadsheetID);
    $sheets = $spreadSheet->getSheets();
    foreach($sheets as $sheet) {
        $sheets[] = $sheet->properties->sheetId;
    }   
    return $sheets;
}


//  Google Spread Sheet 用クライアント作成
function getClient() {


   $auth_str = getenv('authstr');

   $json = json_decode($auth_str, true);


     $client = new Google_Client();

    $client->setAuthConfig( $json );


    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);



    $client->setApplicationName('ReadSheet');

    return $client;


}


?>

<!DOCTYPE html>
<html>

<head>
  	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>災害情報報告マップ</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />   
<!--
<link rel="stylesheet" href="js/leaflet-0.7.3/leaflet.css" />
-->

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
   integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
   crossorigin=""/>
   
<!--
<script src="js/leaflet-0.7.3/leaflet-src.js"></script>
-->

 <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
   integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
   crossorigin=""></script>
   
<link rel="stylesheet" href="css/jquery.mobile-1.4.5.min.css">

<script src="js/jquery.js"></script>
<script src="js/jquery.mobile-1.4.5.min.js"></script>

<!--
<script src="js/leaflet.ajax.js"></script>
-->

<script>
        $(document).on("mobileinit", function () {
          $.mobile.hashListeningEnabled = false;
          $.mobile.pushStateEnabled = false;
          $.mobile.changePage.defaults.changeHash = false;
        });
        
        
$(document).bind('mobileinit', function() {
    alert('mobileinit');
    $(document).bind('pageinit', function(e, data) {
        // initialize page
        //alert('init');
    });
 $(document).bind('pagebeforeshow', function(e, data) {
        // before show page
        var $container = $('#baselayers').find('.ui-controlgroup-controls');
alert("befor");

        // build radio button list
        for (var i = 0; i < 3; i++) {
            var id = 'option_' + i,
                label = 'Option ' + i;

            $('<input />', {
                'id': id,
                'type': 'radio',
                'name': 'options',
                'value': i
            }).append('<label for="' + id + '">' + label + '</label>').appendTo($container);
        }
        // refresh control group
        $container.find('input[type=radio]').checkboxradio();
    });
});
    </script> 
 
 
<style>
  ul { list-style-type: none; margin: 0; padding: 0; margin-bottom: 10px; }
  li { margin: 5px; padding: 5px; width: 150px; }
  </style>
  
     <style type="text/css">
		body {
			padding: 0;
			margin: 0;
		}
		html, body{
			height: 100%;
		}
 		#map { min-height:717px; height: 100vh;  margin: -15px;}
//		#map {  height: 100%; margin: -15px;}
	

/* Change cursor when mousing over clickable layer */
.leaflet-clickable {
  cursor: crosshair !important;
}
/* Change cursor when over entire map */
.leaflet-container {
  cursor: crosshair !important;
//  cursor: help !important;
}
	</style>
	
	<style>
	table.fudeinfo {
    width: 100%;
    margin:20px 0 50px;
    border-top: 1px solid #CCC;
    border-left: 1px solid #CCC;
    border-spacing:0;
}
table.fudeinfo tr th,table.fudeinfo tr td {
    font-size: 12px;
    border-bottom: 1px solid #CCC;
    border-right: 1px solid #CCC;
    padding: 7px;
}
table.fudeinfo tr th {
    background: #E6EAFF;
}
</style>


   
     <script src="js/layersdef.js"></script>
     
     <script>
     
      var  CbaseLayer;
      
     </script>
     
     
<script src="js/L.TileLayer.BetterWMS.js"></script>

    <script src="js/manage_contents.js"></script>
  

<?php 

function GetSheet( $sheetid, $sheetname ) {
  $client = getClient();
 

    $client->addScope(Google_Service_Sheets::SPREADSHEETS);
    $client->setApplicationName('ReadSheet');
    
    $service = new Google_Service_Sheets($client);
     
    $response = $service->spreadsheets_values->get($sheetid, $sheetname);
    
    $values = $response->getValues();
    
    return $values;
    //var_dump( $values );
    
}

$sheetname = '20190703鹿児島';
//$sheetname = 'シート1';
$spreadsheetId = getenv('SPREADSHEET_ID');
 
$sheetd = GetSheet( $spreadsheetId, $sheetname ); 
 
 
var_dump( $sheetd );
 
echo "<script>\n";


echo sprintf('var tgjson="{\\"type\\":\\"FeatureCollection\\", \\"features\\":[ ');

$isdone = false;


$uid_ar = array();

$non_loc_ar = array();

$ckey = 0;
     
$non_locr = array();

foreach ($sheetd as $index => $cols) {

//echo "\nindex ${index}  <br>";
  
     $dated = $cols[0];
     $userd = $cols[1];
    
     $kind = $cols[2]; 
     $url  = $cols[3];

     $stext = $cols[4];
     
     

     
   //  echo "\nkind ${kind}  ";

     if ( strcmp( $kind ,'location' ) == 0 ) {
     
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
        
        if (array_key_exists( $userd, $uid_ar)){
          
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
  

  
       }
       else  {
       
     //  echo "// kind " . $kind . " date ". $dated . "\n";
       
       if ( $index > 0 ){ 
       
       
           if (array_key_exists( $userd, $uid_ar)){
            }
           else {
                   $ckey = 0;
                   $uid_ar[$userd] = $ckey;
            
                   $non_loc_ar[$userd] = array();
           
           }
           
           $ukey = $uid_ar[$userd];
       
       
           if (array_key_exists($non_loc_ar[$userd], $ukey ) ) {
       

              }
            else  {
              
                             $non_loc_ar[$userd][$ukey] = array();
             }
   
                   
              $non_locr = array( "日付"=> $dated,"ユーザ"=>$userd, "種別"=>$kind, 'url'=>$url, 'TEXT'=> $stext );
              
              array_push( $non_loc_ar[$userd][$ukey], $non_locr );
              
          }
       }
       
       
     }
 
    
echo "]} \" ; \n";

echo "</script>\n";
var_dump( $non_loc_ar );


echo "<script>\n";

echo "var nlj = {};\n";


 

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
             echo "vvc.text=\"". $vv["TEXT"] ."\";\n"; 
                                  
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



echo "var features = JSON.parse(tgjson);\n";
//echo "var sample_contents=\"sample contents\"\n";

echo "dSearch = L.geoJSON(features, \n";
echo " { onEachFeature:function ( feature, layer ) {\n";
echo         " PropContents (feature, layer); \n";
echo " }  } ); \n";

echo "default_d = dSearch;\n";
echo "overlays[\"nsearch\"]=dSearch;\n";
 
echo "\n</script>\n";  

    

//var_dump( $non_loc_ar );


include ('webpg.html'); 



?>