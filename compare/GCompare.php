<?php

class GCompare extends _DiffEngine{
    public function compare($str1,$str2){
        $str1 = str_replace("\n", " ", $str1);
        $str2 = str_replace("\n", " ", $str2);
        $ary1 = explode(" ",$str1);
        $ary2 = explode(" ",$str2);
        
        $de = new _DiffEngine();
        $list = $de->diff($ary1,$ary2);
        return $list;
    }
    
    /**
     * 单词级对比
     * @param type $str1
     * @param type $str2
     * @return array
     */
    public function compareWord($str1,$str2){
        $list = $this->compare($str1, $str2);
        $line1 = "";
        $line2 = "";
        foreach($list as $obj){

            $oName = get_class($obj);
            $oOrig = @implode("\n",$obj->orig);
            $oClosing = @implode("\n",$obj->closing);

            if($oName == "_DiffOp_Copy"){
                $line1.=$oOrig."\n";
                $line2.=$oClosing."\n";
            }else{
                $line1.="<font color='orange'>{$oOrig}</font>\n";
                $line2.="<font color='orange'>{$oClosing}</font>\n";
            }
        }
        return array($line1,$line2);
    }
    
    /**
     * 单字级对比
     * @param type $str1
     * @param type $str2
     * @return array
     */
    public function compareDetail($str1,$str2){
        $list = $this->compare($str1, $str2);
        $line1 = "";
        $line2 = "";
        foreach($list as $obj){
            $oName = get_class($obj);
            $oOrig = @implode("\n",$obj->orig);
            $oClosing = @implode("\n",$obj->closing);

            if($oName == "_DiffOp_Copy"){
                $line1.=$oOrig."\n";
                $line2.=$oClosing."\n";
            }else{
//                echo "\n::::::::::::1\n";
//                print_r($obj);
//                echo "\n::::::::::::2\n";
                $data = $this->compare2Detail($obj);
//                print_r($data);
//                echo "\n::::::::::::3\n";
                $line1.="<span style='background-color: #dddddd;'>{$data[0]}</span>";
                $line2.="<span style='background-color: #dddddd;'>{$data[1]}</span>";
            }
        }
        return array($line1,$line2);
    }
    
    /**
     * 相似度计算
     * @param type $strList
     */
    public function similarRate($strList) {
        $rate = 0;
        $data = array();
        $rateAvg = 0;
        for ($i = 0,$total=count($strList); $i < $total; $i++) {
            $pos = $total - $i - 1;
            if($pos == ($total -1)){
                $compareData = $this->compareDetail($strList[$pos], $strList[$pos-1]);
                similar_text($strList[$pos], $strList[$pos],$rate);
                $rate = ceil($rate);
                $data[$pos]['compare']=$compareData[0];
            }else{
                $compareData = $this->compareDetail($strList[$pos+1], $strList[$pos]);
                
                similar_text($strList[$pos+1], $strList[$pos],$rate);
                $rate = ceil($rate);
                $data[$pos]['compare']=$compareData[1];
            }
            $rateAvg += $rate;
        }
        
        $data2 = array();
        for($i=0,$total=count($data);$i<$total;$i++){
            $item = $data[$i];
            $pos = $i;
            $data2[$pos]=$item;
        }

        return $data2;
    }
    
    private function str_split($text) {
        $data = array();
        $len = mb_strlen($text, 'utf-8');
        for ($i = 0; $i < $len; $i++) {
            $data[$i] = mb_substr($text, $i, 1, 'utf-8');
        }
        return $data;
    }

    private function compare2Detail($obj){
        $text1 = @implode(" ", $obj->orig);
        $text2 = @implode(" ", $obj->closing);
        $ary1 = $this->str_split($text1);
        $ary2 = $this->str_split($text2);

        $de = new _DiffEngine();
        $list = $de->diff($ary1,$ary2);
        $line1 = "";
        $line2 = "";
        foreach($list as $obj){

            $oName = get_class($obj);
            $oOrig = @implode("",$obj->orig);
            $oClosing = @implode("",$obj->closing);

            if($oName == "_DiffOp_Copy"){
                $line1.=$oOrig."\n";
                $line2.=$oClosing."\n";
            }else{
                $line1.="<font color='orange'>{$oOrig}</font>";
                $line2.="<font color='orange'>{$oClosing}</font>";
            }
        }
        return array($line1,$line2);
    }

    
}

/**
 * 上限比较：基于达到上限后，结果会保持一致这种情况
 */
class UPLimitCompare extends GCompare{
    
    public function calcList($strList){
        $data = $this->getUpLimitForTM($strList);
        $list0 = $this->similarRate($data['data'][0]);
        $list1 = $this->similarRate($data['data'][1]);
        
        $ret = array(
            'num'=>$data['num'],
            'data'=>array($list0,$list1),
        );
        return $ret;
    }
    
    public function getUpLimit($strList){
        $rate = 0;
        $data = array();
        $rateAvg = 0;
        $total=count($strList);
        $upLimit = -1;
        $lastLine = $strList[$total-1];
        for ($i = 0; $i < $total; $i++) {
            $pos = $total - $i - 1;
            $data[$pos]['origin'] = $strList[$pos];
            similar_text($strList[$pos], $lastLine,$rate);
            $rate = ceil($rate);
            $data[$pos]['rate'] = $rate;
            if(($upLimit == -1 ) && ($rate != 100)){
                $upLimit = $pos+1;
            }
            $rateAvg += $rate;
        }
        $limitUp = array_slice($strList,0,$upLimit);
        $limitDown = array_slice($strList,$upLimit);
        $ret = array(
            'num'=>$upLimit,
            'data'=> array($limitUp,$limitDown),
        );
        
        return $ret;
    }
    
    private function isSameItemInList($strList){
        $total = count($strList);
        $lastLine = $strList[$total-1];
        for ($i = 0; $i < $total; $i++) {
            $pos = $total - $i - 1;
            similar_text($strList[$pos], $lastLine,$rate);
            $rate = ceil($rate);
            if($rate < 100){
                return false;
            }
            $lastLine = $strList[$pos];
        }
        return true;
        
    }
    
    public function getUpLimitForTM($strList){
        $rate = 0;
        $data = array();
        $rateAvg = 0;
        $total=count($strList);
        $upLimit = -1;
        $lastLine = $strList[$total-1];
        for ($i = 0; $i < $total; $i++) {
            $pos = $total - $i - 1;
            $data[$pos]['origin'] = $strList[$pos];
            similar_text($strList[$pos], $lastLine,$rate);
            $rate = ceil($rate);
            $data[$pos]['rate'] = $rate;
            if(($upLimit == -1 ) && ($rate != 100)){
                $upLimit = $pos+1;
            }
            $rateAvg += $rate;
        }
        $limitUp = array_slice($strList,0,$upLimit);
        $limitDown = array_slice($strList,$upLimit);
        if(!$this->isSameItemInList($limitUp) || !$this->isSameItemInList($limitUp)){
            
            $upLimit++;
            $limitUp = array_slice($strList,0,$upLimit);
            $limitDown = array_slice($strList,$upLimit);
        }
        $ret = array(
            'num'=>$upLimit,
            'data'=> array($limitUp,$limitDown),
        );
        
        return $ret;
    }
    
    
}
?>
