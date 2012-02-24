<?php
class RandomText{
    private $charactorsA = array('a','e','i','o','u');
    private $charactorsB = array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z');
    private $sign = array('','','-','','',',','','','.','','','','_','','','','','!','','','','','#');
    
    private $filename = "output.txt";
   
    public function generate($length,$filename='') {
            
        if(!empty($filename)) {
            $this->filename = $filename;
        }
        
        $fp = @fopen($this->filename, "w+");
        if(!$fp) {
            echo "ERROR: cant  write the file.";
            return false;
        }
        echo "start generating....\n";
        while($length > 0) {
            $wordLength = mt_rand(2,10);
            fputs($fp, $this->getWord($wordLength)." ", $wordLength+1);
            $length-=$wordLength;
            echo ".";
        }
        echo "\n OK! file is ".$this->filename."\n";
        fclose($fp);
        return true;
    }
    
    private function getWord($wordLength) {
        shuffle($this->charactorsB);
        $word .= $this->charactorsB[0];
        $wordLength--;
        while($wordLength>1){
            shuffle($this->charactorsA);
            $word .= $this->charactorsA[$wordLength--];
            shuffle($this->sign);
        }
        $word .= $this->sign[$wordLength--];
        return $word;
    }
}


$textTool = new RandomText();
$textTool->generate(1024);

?>
