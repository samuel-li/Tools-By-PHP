<?php
/**
 * Compare the src directory and dest directory, support override the targe file by source file.
 *    Usage: 
 *          set env DIFFTOOL=/usr/bin/diff or export DIFFTOOL=/usr/bin/diff (default is /usr/bin/diff)
 *          php diff.php [-cp] srcdir  destdir >diff.log
 *          -cp: override the destfile by srcfile
 * @author samuel.li <weiyesoft@gmail.com>
 */
class PHPDiff {
    
    function __contruct() {
        
    }
    
    private $_srcdir = "";
    private $_destdir = "";
    private $_cp = false;
    private $_diffTool = "/usr/bin/diff";
    
    private function checkdir($basedir, $destdir) {
        if ($dh = opendir($basedir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    if (!is_dir($basedir . "/" . $file)) {
                        $srcfile = $basedir . "/" . $file;
                        $destfile = str_replace($this->_srcdir, $this->_destdir, $srcfile);
                        
                        if(!file_exists($destfile)) {
                            echo "** NEW: {$srcfile}\n";
                            if($this->_cp) {
                                $destpath = dirname($destfile);
                                if(!is_dir($destpath))
                                {                                    
                                    @mkdir($destpath, 0755, true);
                                }
                                passthru("dos2unix {$srcfile}");
                                @copy($srcfile, $destfile);
                            }
                        }
                        else {
                            if(md5_file($srcfile) == md5_file($destfile))
                            {                              
                                echo "++ SAME: {$srcfile} {$destfile}\n";
                            }
                            else
                            {
                                echo "## DIFF {$srcfile} {$destfile}\n";
                                passthru("dos2unix {$destfile}");
                                passthru("dos2unix {$srcfile}");
                                passthru("{$this->_diffTool} {$srcfile} {$destfile}");
                                if($this->_cp) {
                                    // passthru("cp -f $destfile {$destfile}.phpdiff.bak");            
                                    // passthru("cp -f $srcfile {$destfile}");    
                                    @copy($destfile, "{$destfile}.phpdiff.bak");
                                    @copy($srcfile, $destfile);
                                }
                            }
                        }
                        
                    } else {
                        $dirname = $basedir . "/" . $file;
                        $this->checkdir($dirname, $destdir);
                    }
                }
            }
            closedir($dh);
        }
    }
    
    public function diff($src, $dest, $cp=false) 
    {
        // Usage: set env DIFFTOOL=/usr/bin/diff or export DIFFTOOL=/usr/bin/diff
        if(!empty($_ENV['DIFFTOOL'])) {
            $this->_diffTool = $_ENV['DIFFTOOL'];
        }
        
        if(!file_exists($this->_diffTool)) {
            echo "Not found diff tool(/usr/bin/diff). Usage: set env DIFFTOOL=/usr/bin/diff or export DIFFTOOL=/usr/bin/diff";
            return false;
        }
        $this->_srcdir = $src;
        $this->_destdir = $dest;
        $this->_cp = $cp;

        $this->checkdir($src, $dest);
    }
}

if($argc != 3 && $argc != 4) 
{
    echo <<<EOT
   Usage: set env DIFFTOOL=/usr/bin/diff or export DIFFTOOL=/usr/bin/diff (default is /usr/bin/diff)
       php diff.php [-cp] srcdir  destdir >diff.log
       -cp: override the destfile by srcfile
       
EOT;
    exit;
}

if($argc == 3) {
    $srcfile = $argv[1];
    $destfile = $argv[2];
}
elseif($argc == 4) {
    if($argv[1] == "-cp")
    {
        $srcfile = $argv[2];
        $destfile = $argv[3];
    }
    elseif($argv[3] == "-cp")
    {
        $srcfile = $argv[1];
        $destfile = $argv[2];
    }
    else {
        die("ERROR: Now phpdiff only support -cp option.\n");
    }
    $cp = true;
}

if(!file_exists($srcfile)) {
    echo <<<EOT
    {$srcfile} not found. 
EOT;
    exit;
}

if(!file_exists($destfile)) {
    echo <<<EOT
         {$destfile} not found.
EOT;
    exit;
}

$phpDiff = new PHPDiff();
$cp = false;

$phpDiff->diff($srcfile, $destfile, $cp);

?>
