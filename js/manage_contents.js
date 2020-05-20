

   function  SheetListSetup(){

     //  set sheet name list
       url = 'getsheetList.php'
       $.ajax({
         url: url,
         type: "POST",
         dataType: "json",
         success: function (data, status, xhr) {


             var $buttonlist = $('#sheetlist');

             var sheetnames = data['sheetnames'];

             for(let v of sheetnames ) {
                 var $btn =  '<a href="JavaScript:SelectSheet(\'' + v +'\')" class="ui-btn">' + v + '</a>';

                 console.log( $btn );
                 $( $btn ).appendTo($buttonlist);
                 // console.log(v);
                  }

                },
         error: function (xhr, status, error) {
               alert(error);

             }
           });

   }

   function SelectSheet( sheetname ){
     //  set sheet name list
       url = 'getfeatures.php'
       $.ajax({
         url: url,
         type: "POST",
         data:{sheetname: sheetname},
         dataType: "json",
         success: function (data, status, xhr) {

          for ( var item in data){
               console.log( item['location']['key']);
          }

/*
             var $buttonlist = $('#sheetlist');

             var sheetnames = data['sheetnames'];

             for(let v of sheetnames ) {
                 var $btn =  '<a href="JavaScript:SelectSheet(\'' + v +'\')" class="ui-btn">' + v + '</a>';

                 console.log( $btn );
                 $( $btn ).appendTo($buttonlist);
                 // console.log(v);
                  }
*/
                },
         error: function (xhr, status, error) {
               alert(error);

             }
           });
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
