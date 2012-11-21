<?php
require_once 'Profiler.php';
require_once 'DairikiDiff.php';
require_once 'GCompare.php';



$rev1 = "
111
123
456
abcyz
456
中国文
";
$rev2 = "
123
456
xyz
123
123
456
中文
";


echo "<br />[1]<hr />";
echo $rev1."<br>\n";
echo $rev2."<br>\n";
$gCompare = new GCompare();
$data = $gCompare->compareWord($rev1, $rev2);
echo "<br />[2]<hr />";
echo $data[0]."<br>\n";
echo $data[1]."<br>\n";
$data = $gCompare->compareDetail($rev1, $rev2);

echo "<br />[3]<hr />";
echo $data[0]."<br>\n";
echo $data[1]."<br>\n";



echo "<br />[-]<hr />";
$testData = array(
  '当前票数:1票',  
  '当前票数:2票',  
  '当前票数:3票',  
  '当前票数:4票',  
  '当前票数:5票',  
  '当前票数:6票',  
  '当前票数:7票',  
  '当前票数:7票',  
  '当前票数:7票',  
);



$upLimit = new UPLimitCompare();
$list = $upLimit->calcList($testData);
foreach($list['data'][0] as $pos=>$item){
    echo "[$pos]\t{$item['compare']}\n<br>";
}
echo "::::::::::::::::::::::::::::::::::::上限值[{$list['num']}]\n<br>";
foreach($list['data'][1] as $pos=>$item){
    echo "[$pos]\t{$item['compare']}\n<br>";
}
echo "<br />[-]<hr />";

$testData = array(
  '投票成功 ',  
  '投票成功 ',  
  '投票成功 ',  
  '投票失败 ',  
  '投票失败 ',  
  '投票失败 ',  
);


$upLimit = new UPLimitCompare();
$list = $upLimit->calcList($testData);
foreach($list['data'][0] as $pos=>$item){
    echo "[$pos]\t{$item['compare']}\n<br>";
}
echo "::::::::::::::::::::::::::::::::::::上限值[{$list['num']}]\n<br>";
foreach($list['data'][1] as $pos=>$item){
    echo "[$pos]\t{$item['compare']}\n<br>";
}

?>
