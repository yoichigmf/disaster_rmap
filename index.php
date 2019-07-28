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

$sheetname = 'シート1';
$spreadsheetId = getenv('SPREADSHEET_ID');
 
$sheetd = GetSheet( $spreadsheetId, $sheetname ); 
 
 
 //var_dump( $sheetd );
 
echo "<script>\n";


echo sprintf('var tgjson="{\\"type\\":\\"FeatureCollection\\", \\"features\\":[ ');

$isdone = false;

foreach ($sheetd as $index => $cols) {

//echo "\n ${index} col =>${cols[0]},${cols[1]},${cols[2]},${cols[3]}";

 // if ( intval($index) > 0 ) {  //  1行目は項目名だからスキップ
  
     $dated = $cols[0];
     $userd = $cols[1];
    
     $kind = $cols[2]; 

     
   //  echo "\nkind ${kind}  ";

   
     if ( strcmp( $kind ,'location' ) == 0 ) {
     
          if ( $isdone   ) {
      	    $topc = " ,{";
     		  }
     		else   {
         		 $topc = "{ ";
         		 $isdone = true;
     
     		}
     
        $xcod =$cols[6];
        $ycod = $cols[5];
     
    echo ${topc};
    echo sprintf(' \\"type\\":\\"Feature\\",\\"geometry\\":{\\"type\\": \\"Point\\", \\"coordinates\\":[%s,%s]}, \\"properties\\":{\\"日付\\":\\"%s\\",\\"ユーザ\\":\\"%s\\"}}',$xcod,$ycod, $dated,$userd);
  
   //   echo sprintf(' \\"type\\":\\"Feature\\",\\"properties\\":{\\"日付\\":\\"%s\\",\\"ユーザ\\":\\"%s\\"},\\"geometry\\":{\\"type\\": \\"Point\\", \\"coordinate\\":[%s,%s]}}',$dated,$userd,$xcod,$ycod);
      //  $itemd = "${topc} \"type\\":\\"Feature\\",\\"properties\\":{\\"日付\\":\\"${dated}\\",\\"ユーザ\\":\\"${userd}\\"},\\"geometry\\":{\\"type\\": \\"Point\\", \\"coordinate\\":[${xcod},${ycod}]}}";
        
       // echo "${itemd};\n";
  
       }
     }
 
    
echo "]} \" ; \n";

echo "var features = JSON.parse(tgjson);\n";

echo "dSearch = L.geoJSON(features);\n";

echo "default_d = dSearch;\n";
// { onEachFeature: function (feature, layer) {\n";

//echo "  if (feature.properties && feature.properties.popupContent) { \n";
//echo "        layer.bindPopup(feature.properties.popupContent); \n";
// echo "   } }} ); \n";
    
echo "\n</script>\n";
//var_dump( $sheetd );


include ('webpg.html'); 



?>