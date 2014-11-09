<?php 

function makeCleanDirectoryName($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}

function urlExists($url) {
    if (!$fp = curl_init($url)) return false;
    return true;
}

function changeLogo($html, $newImageUrl) {
    return str_replace('asset(\'images/logo.png\')', $newImageUrl, $html);
}

function changeFeature($html, $newFeatureUrl) {
    return str_replace('asset(\'images/feature-bg.jpg\')', $newFeatureUrl, $html);
}

function replaceLogoFile($logoFileName, $url) {
    file_put_contents($logoFileName, file_get_contents($url));
}

function replaceFeatureFile($featureFileName, $url) {
   file_put_contents($featureFileName, file_get_contents($url));
}

function processFiles($src, $newImageUrl, $newFeatureUrl) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                processFiles($src . '/' . $file); 
            } else { 
                // Check for css files
                $fileLocation = $src . '/' . $file;
                if(strpos($file, '.twig')) {
                    $html = file_get_contents($fileLocation);
                    $html = changeLogo($html, $newImageUrl);
                    $html = changeFeature($html, $newFeatureUrl);
                    file_put_contents($fileLocation, $html);
                }
            } 
        } 
    }

    closedir($dir); 
}

function recursiveRmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") recursiveRmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function recurseCopy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurseCopy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

