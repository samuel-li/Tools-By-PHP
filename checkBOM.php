<?php
/**
 *  转换指定目录下签名的utf8文件为非签名的utf8文件
 */


$_gTotalNum = 0;
$dir_array = array();
$option_array = array();

$basedir = ".";

if ($argc == 1) {
    array_push($dir_array, ".");
} else {
    $_cmdArgs = array_splice($argv, 1);

    foreach ($_cmdArgs as $arg) {
        if (strpos($arg, "-") === 0) {
            checkargs($arg);
        } else {
            array_push($dir_array, $arg);
        }
    }
}

echo "----------- start check ------------\n";

foreach ($dir_array as $basedir) {
    checkdir($basedir);
}

echo "----------- check complete. found {$_gTotalNum} BOM files\n";

function checkdir($basedir) {
    global $_gTotalNum;
    if ($dh = opendir($basedir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                if (!is_dir($basedir . "/" . $file)) {
                    $ret = checkBOM("$basedir/$file");
                    if ($ret) {
                        $_gTotalNum++;
                    }
                } else {
                    $dirname = $basedir . "/" . $file;
                    checkdir($dirname);
                }
            }
        }
        closedir($dh);
    }
}

function checkBOM($filename) {
    global $option_array;

    $contents = file_get_contents($filename);
    $charset[1] = substr($contents, 0, 1);
    $charset[2] = substr($contents, 1, 1);
    $charset[3] = substr($contents, 2, 1);
    if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
        if (count($option_array) > 0) {
            foreach ($option_array as $op) {
                $op($filename);
            }
            return true;
        } else {
            echo "BOM File: {$filename}\n";
            return true;
        }
    }
    return false;
}

function rewrite($filename, $data) {
    $filenum = fopen($filename, "w");
    flock($filenum, LOCK_EX);
    fwrite($filenum, $data);
    fclose($filenum);
}

function usage() {
    echo "Usage: php checkBOM.php [option] <basedir>\n";
    echo "       option: -remove : remove BOM flag from the BOM file\n\n";
    exit;
}

function checkargs($arg) {
    global $option_array;

    $arg = substr($arg, 1);

    switch ($arg) {
        case "remove": // remove BOM flag from bom file
            array_push($option_array, $arg);
            break;
        default:
            echo "ERROR: option {$arg} is invalid\n";
            usage();
            break;
    }
    return true;
}

function remove($filename) {
    $contents = file_get_contents($filename);
    $rest = substr($contents, 3);
    rewrite($filename, $rest);
    echo "Remove BOM Flag : {$filename} \n";
    return true;
}

?>
