<?php
 function chmod_R($path, $perm) {
 
   $handle = opendir($path);
   while ( false !== ($file = readdir($handle)) ) {
     if ( ($file !== ".") && ($file !== "..") ) {
       if ( is_file($path."/".$file) ) {
         chmod($path . "/" . $file, $perm);
       }
       else {
         chmod($path . "/" . $file, $perm);
         chmod_R($path . "/" . $file, $perm);
       }
     }
   }
   closedir($handle);
 }
 
 $path = $_SERVER["QUERY_STRING"];

 if ( $path{0} != "/" ) {
    $path = $_SERVER["DOCUMENT_ROOT"] . "/" . $path;
 }

 chmod_R($path, 0777);
 echo $path;
?>