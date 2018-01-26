<?php
//  Copyright (c) 2009 Facebook
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

//
// XHProf: A Hierarchical Profiler for PHP
//
// XHProf has two components:
//
//  * This module is the UI/reporting component, used
//    for viewing results of XHProf runs from a browser.
//
//  * Data collection component: This is implemented
//    as a PHP extension (XHProf).
//
//
//
// @author(s)  Kannan Muthukkaruppan
//             Changhao Jiang
//

// by default assume that xhprof_html & xhprof_lib directories
// are at the same level.
$GLOBALS['XHPROF_LIB_ROOT'] = dirname(__FILE__) . '/../xhprof_lib';

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';

// param name, its type, and default value
$params = array('run'        => array(XHPROF_STRING_PARAM, ''),
                'wts'        => array(XHPROF_STRING_PARAM, ''),
                'symbol'     => array(XHPROF_STRING_PARAM, ''),
                'sort'       => array(XHPROF_STRING_PARAM, 'wt'), // wall time
                'run1'       => array(XHPROF_STRING_PARAM, ''),
                'run2'       => array(XHPROF_STRING_PARAM, ''),
                'source'     => array(XHPROF_STRING_PARAM, ''),
                'all'        => array(XHPROF_UINT_PARAM, 0),
                );

// pull values of these params, and create named globals for each param
xhprof_param_init($params);

/* reset params to be a array of variable names to values
   by the end of this page, param should only contain values that need
   to be preserved for the next page. unset all unwanted keys in $params.
 */
foreach ($params as $k => $v) {
  $params[$k] = $$k;

  // unset key from params that are using default values. So URLs aren't
  // ridiculously long.
  if ($params[$k] == $v[1]) {
    unset($params[$k]);
  }
}

echo "<html>";

echo "<head><title>XHProf: Hierarchical Profiler Report</title>";
xhprof_include_js_css();
echo "</head>";

echo "<body>";

$vbar  = ' class="vbar"';
$vwbar = ' class="vwbar"';
$vwlbar = ' class="vwlbar"';
$vbbar = ' class="vbbar"';
$vrbar = ' class="vrbar"';
$vgbar = ' class="vgbar"';

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
 

$xhprof_runs_impl = new XHProfRuns_Default( $path ); 
 
$file_name = $xhprof_runs_impl->file_name($run, $source); 
if( !file_exists($file_name) ){ 
	die($file_name .' not exist');
} 
//var_dump($file_name);
displayXHProfReport($xhprof_runs_impl, $params, $source, $run, $wts,
                    $symbol, $sort, $run1, $run2);


echo "</body>";
echo "</html>";
