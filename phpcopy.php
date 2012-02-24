<?php

/**
 * 复制源文件夹中指定类型的文件到目标文件夹中，并保持原来的目录结构
 *
 * @author samuel.li
 */
class PHPCopy {

    protected $source_dir = '';
    protected $dest_dir = '';
    protected $fileTypes = array();
    protected $excludes = array();
    
    protected $copy_filenum = 0;

    public function PHPCopy($source_dir, $dest_dir) {
        $this->source_dir = $source_dir;
        $this->dest_dir = $dest_dir;
    }

    public function setFileType($types = array()) {
        $this->fileTypes = $types;
    }
    
    public function setExcludes($excludes = array()) {
        $this->excludes = $excludes;
    }
    
    public function doPHPCopy()
    {
        try{
            $this->doCopy($this->source_dir);
        }
        catch(Exception $e) {
            die($e->getMessage());
        }
    }
    
    public function getCopyResult(){
        echo "\n---- Total copy {$this->copy_filenum} files ---\n";
    }

    protected function isExcluded($filepath) {
        if(empty($this->excludes)) return false;
        foreach($this->excludes as $exclude) {
            if(false!==strpos($filepath, $exclude)) {
                return true; 
            }
        }
        return false;
    }
    
    protected function getFileType($file) {
        $info = pathinfo($file);
        return $info['extension'];
    }
    
    protected function copy($file) {
        $dir = dirname($file);
        
        $d = substr($dir, strlen($this->source_dir));
        $dest = $this->dest_dir.$d;
        // TODO mkdir in dest_dir
        if(!is_dir($dest)) {
            mkdir($dest,0755, true);
        }
        if(is_dir($dest)) {
            copy($file, $dest."/".basename($file));
            return true;
        }
        return false;
    }
    
    protected function doCopy($basedir) {
        if ($dh = opendir($basedir)) {
            while (($file = readdir($dh)) !== false) {
                $filepath = $basedir . "/" . $file;
                
                if ($file != '.' && $file != '..') {
                    if (!is_dir($filepath)) {
                        $ext = $this->getFileType($filepath);
                        if(in_array($ext, $this->fileTypes)) {
                            if($this->copy($filepath)) {
                                echo ".";
                                $this->copy_filenum++;
                            }
                            else {
                                echo "error: copy {$filepath} failed!\n";
                            }
                        }
 
                    } else if(!$this->isExcluded($filepath)){
                        $this->doCopy($filepath);
                    }
                }
            }
            closedir($dh);
        }
    }
}

require_once("RunningTimeUtil.php");

RunningTimeUtil::run_start();
$phpCopyUtil = new PHPCopy('E:/Workspaces/projects/openpne', "E:/Workspaces/projects/translate");
$phpCopyUtil->setFileType(array('xml','yml'));
$phpCopyUtil->setExcludes(array('.svn', '/lib/vendor','/cache','/mobile_','/test'));
$phpCopyUtil->doPHPCopy();
RunningTimeUtil::run_end();
$phpCopyUtil->getCopyResult();
echo RunningTimeUtil::getResult();
?>
