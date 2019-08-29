

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
                 }
                 
                     
                  if ( vf.text  ) {
                      tgtext = tgtext +  " " + vf.text + "<br>";
                  
                   }
             
              }
            }
        }
        
        
        layer.bindPopup(tgtext);
    }
}

