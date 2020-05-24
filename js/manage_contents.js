var default_d;     //default display layer

var overlays = {};    // overlay layers

var backGrounds = {};    // backGround layers list

var CbaseLayer;           // Current　Base Layer


//var dSearch;

//   レイヤ情報の設定
function SetLayerinfo(　mapsheetId) {


    var $overlaylist = $('#overlaylist');


    var $btn =  '<input id="ov1"  name="ov1" type="checkbox" value="default_d" onChange=\'changechk( this )\'    checked /><label for="ov1">調査データ</label>'

     console.log( $btn );
    $( $btn ).appendTo($overlaylist );


  OSMLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                      attribution : '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors', minZoom: 3, maxZoom: 18, zIndex:5
                  });

   backGrounds["オープンストリートマップ"]= OSMLayer;

   CbaseLayer = OSMLayer;

   CbaseLayer.setZIndex(0);

    CbaseLayer.addTo(map);

    //overlays[nsearch]=dSearch;
    SagaOOmati  = L.tileLayer('https://dronebird.github.io/oam_saga20190904omachi01/xyztile_lowest/{z}/{x}/{y}.png',
    {    attribution : 'Dronebird', minZoom: 13, maxZoom: 20, zIndex:3 });

    overlays["20190904oomati"]=SagaOOmati ;


    t15  = L.tileLayer('https://tile.geospatial.jp/aeroasahi/t15chiba/{z}/{x}/{y}.png',
    {    attribution : 'Aeroasahi Corporation', minZoom: 5, maxZoom: 18, zIndex:3 });


    overlays["t15"]=t15 ;


}
   function  SheetListSetup(sheetid){

     //  set sheet name list
       url = 'getsheetList.php'
       $.ajax({
         url: url,
         type: "POST",
        data:{sheetid: sheetid},
         dataType: "json",
         success: function (data, status, xhr) {


             var $buttonlist = $('#sheetlist');

             var sheetnames = data['sheetnames'];

             for(let v of sheetnames ) {

               if ( v !== 'config'){   //  設定ページを除く
                    var $btn =  '<a href="JavaScript:SelectSheet(\'' + v +'\')" class="ui-btn">' + v + '</a>';

                       console.log( $btn );
                       $( $btn ).appendTo($buttonlist);
                 // console.log(v);
                        }

                  }

                },
         error: function (xhr, status, error) {
               alert(error);

             }
           });

   }

//   シートデータ設定　最初の場合
  function SelectSheetInit(sheetid, sheetname){

    tgSheetname ="シート1"
    if (sheetname){
      tgSheetname = sheetname;
    }


    SelectSheet  (tgSheetname);
   }



function SelectSheet( sheetname ){
     //  set sheet name list
       url = 'getfeatures.php'
       $.ajax({
         url: url,
         type: "POST",
         data:{sheetname: sheetname, sheetid:mapSheetId},
         dataType: "json",
         success: function (data, status, xhr) {
            //  console.log( data.length)
              console.log( data );

            var PointACluster;
            PointACluster = CreatePointCluster( data  , PointACluster);
              //マーカークラスター設定


            if ( default_d){
                  map.removeLayer(default_d);
              }

            PointACluster.setZIndex(250);
            PointACluster.addTo(map);

            default_d = PointACluster;
            featureG = L.featureGroup([ default_d ]);

            overlays["default_d"] = default_d;
                FitBound();


          //  map.addLayer(PointACluster);



                },
         error: function (xhr, status, error) {
               alert(error);

             }
           });
   }



function CreatePointCluster( data, PointClusterd){
   //マーカークラスター設定
 PointClusterd = L.markerClusterGroup({
   showCoverageOnHover: false,
   spiderfyOnMaxZoom: true,
   removeOutsideVisibleBounds: true,
   disableClusteringAtZoom: 18
       });

     //  ポイント geojson 定義
   var PointArray = {
     "type": "FeatureCollection",
     "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } }
   };

　　　　　　　　　　//  ポイント地物リスト
   var Features = [];



   for ( var item in data  ){
        pfeature = data[item];

        var dheader = pfeature["location"];
        var dprop   = pfeature["attribute"];

        if ( dheader ){
          xpp = dheader['x'];
          ypp = dheader['y'];



          var feature = {};

          var nproperties = {};
          var ngeometry = {};

             //  プロパティの配列化が必要
             nproperties["id"] = dheader["vkey"];
             nproperties["user"] = dheader["user"];
             nproperties["date"] = dheader["date"];

             var property_array = [];

             for ( var iprop in dprop){
               var  propd = {};
              // console.log(dprop[iprop]);
               propd['日付'] = dprop[iprop]['日付'] ;
               propd['ユーザ'] = dprop[iprop]['ユーザ'] ;
               propd['種別'] = dprop[iprop]['種別'] ;
               propd['TEXT'] = dprop[iprop]['TEXT'];
               propd['url'] = dprop[iprop]['url'] ;

               property_array.push( propd );
             }

             nproperties["proplist"] = property_array ;

             ngeometry["type"] = "Point";
             ngeometry["coordinates"] = [];

             ngeometry["coordinates"].push(xpp);
             ngeometry["coordinates"].push(ypp);

             feature["type"] = "Feature";
             feature["properties"]= nproperties;
             feature["geometry"]= ngeometry;

             Features.push(feature);
           }

       //  console.log(feature);
   }

   PointArray["features"]= Features;

   //console.log(PointArray);
   PointClusterd.addLayer(L.geoJson(PointArray,{
   onEachFeature:function (feature, layer) {
     // 地物クリック時の関数記述　プロパティが配列化した場合
          PropContents2(feature,layer);
     //var field = "id: " + feature.properties.id;
     //  layer.bindPopup(field);

    },
clickable: true
}));

  return( PointClusterd  );

}

   function PropContents2(feature, layer) {
       // does this feature have a property named popupContent?

           console.log("propcontents2");
    //   if (feature.properties && feature.properties.日付) {

          var tgtext = "";
          console.log(feature);
           //var kind = feature.properties.種別;

           tgtext = feature.properties.date + "<br>報告者:" +  feature.properties.user ;


               var propList = feature.properties.proplist;


              if ( propList ) {
              for ( let vf of propList ) {
                    console.log(vf);
                   tgtext = tgtext + "<br>" + vf["日付"] ;

                    kind = vf["種別"];


                    if ( vf["url"]   ){
                     imageurl = vf["url"];

                          dlurl = imageurl;

                          mmurl = dlurl.replace('?dl=0', '');
                         mmurl = mmurl.replace('www.dropbox.com', 'dl.dropboxusercontent.com');

                          tgtext = tgtext + "<br><a href=\""+ imageurl + "\" target=\"photo\">" + imageurl + "</a>";

                           if ( kind === 'image' ) {



                          tgtext = tgtext + "<br><a href=\""+ imageurl + "\" target=\"photo\"><img src=\"" + mmurl + "\"  width=\"200\"></a>";

                        　　　　　　}



                    　　　　　}



                     if ( vf["TEXT"]  ) {
                         tgtext = tgtext +  "<br>" + vf["TEXT"]+ "<br>";

                      　　}


                   }　// proplist loop

           　　}  // if proplist


           layer.bindPopup(tgtext);
      // }  //
   }


function PropContents(feature, layer) {
    // does this feature have a property named popupContent?
    if (feature.properties && feature.properties.日付) {

       var tgtext = "";

        var kind = feature.properties.種別;

        tgtext = feature.properties.日付 + "<br>" + kind + "<br>報告者:" +  feature.properties.ユーザ ;




        if ( kind === 'image' ) {

        imageurl = feature.properties.url;

        dlurl = imageurl;

        mmurl = dlurl.replace('?dl=0', '');
        mmurl = mmurl.replace('www.dropbox.com', 'dl.dropboxusercontent.com');

        tgtext = tgtext + "<br><a href=\""+ imageurl + "\" target=\"photo\">" + imageurl + "</a>";

        tgtext = tgtext + "<br><a href=\""+ imageurl + "\" target=\"photo\"><img src=\"" + mmurl + "\"  width=\"200\"></a>";

        }
        else {

        tgtext = tgtext +  "<br>" + feature.properties.テキスト + "<br>";

        }

        if ( nlj === null ) {
         }
        else  {
           if ( typeof nlj !== 'undefined' ){
            var propList = nlj[feature.properties.ユーザ ][feature.properties.uid];


           if ( propList ) {
           for ( let vf of propList ) {
                tgtext = tgtext + "<br>" + vf.date ;

                 kind = vf.kind;


                 if ( vf.url   ){
                  imageurl = vf.url;

                       dlurl = imageurl;

                       mmurl = dlurl.replace('?dl=0', '');
                      mmurl = mmurl.replace('www.dropbox.com', 'dl.dropboxusercontent.com');

                       tgtext = tgtext + "<br><a href=\""+ imageurl + "\" target=\"photo\">" + imageurl + "</a>";

                        if ( kind === 'image' ) {



                       tgtext = tgtext + "<br><a href=\""+ imageurl + "\" target=\"photo\"><img src=\"" + mmurl + "\"  width=\"200\"></a>";

                     }



                 }



                  if ( vf.text  ) {
                      tgtext = tgtext +  " " + vf.text + "<br>";

                   }
                }
              }
            }
        }


        layer.bindPopup(tgtext);
    }
}
