<?php

require_once '../../../globals.php';

$path = STORAGE_PATH.'xhprof';
ini_set( "xhprof.output_dir",$path );

// 自动清理
if (is_dir($path)) {
    if ($dh = opendir($path)) {
        while (($file = readdir($dh)) !== false) {
            //var_dump(is_file($path .'/'.$file));
            if(is_file($path .'/'.$file)){
                $part_file = pathinfo($path .'/'.$file);
                if($part_file['extension']=='xhprof'){
                    if(time()-filemtime($path .'/'.$file)>3600){
                        unlink($path .'/'.$file);
                    }
                }
            }
            //echo "filename: $file : filetype: " . filetype($path . $file) . "\n<br>";
        }
        closedir($dh);
    }
}

if( isset($_GET['file']) ) {
    if(file_exists($_GET['file']) ){
        $part = pathinfo($_GET['file']);
        copy ($_GET['file'],STORAGE_PATH.'xhprof/'.$part['basename']);
    }
}