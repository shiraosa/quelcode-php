<?php
$limit = $_GET['target'];
settype($limit,"int");

$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';
try{
    $db = new PDO("$dsn","$dbuser","$dbpassword");
}catch(PDOException $e){
    echo "DB接続エラー:" . $e->getMessage();
}
$dbdata = $db->query("SELECT * FROM prechallenge3");
$prechallenge3 = $dbdata -> fetchAll();
$value = array_column($prechallenge3,"value");
rsort($value);
$count=count($value);
for($i=0; $i < $count;$i++){
    settype($value[$i],"int");
}

function array_json($value,$limit,$diff,$i,$j,$k,$answer){
    global $json;
    if($limit=== $value[$i]){
        $json[] = [$value[$i]];
        unset($answer);
        $i++;
        array_json($value,$limit,$diff,$i,$j,$k,$answer);
    }
    if($j===1){
        $answer=[$value[$i]];
        $diff = $limit - $value[$i];
    }
    $range = array_slice($value,$i+$j);
    $max = $range[$k];
    if($i < count($value)){//関数を終わらせるかどうか
        if($diff>array_sum($range) && $k < count($value)){
            unset($answer);
            $j=1;
            $k++;
        }elseif($diff>array_sum($range) or $diff < 0){
            unset($answer);
            $i++;
            $j = 1;
            $k = 0;
        }elseif($diff<$max){
            $j++;
        }elseif($diff>$max){
            $diff = $diff - $max;
            $j++;
            $answer[] = $max;
        }elseif($diff - $max===0 ){
            $answer[] = $max;
            if(isset($json)&&in_array($answer,$json)){
                $j++;
                array_pop($answer);
            }else{
                $json[] = $answer;
                unset($answer);
                $j = 1;
            }
        }
        array_json($value,$limit,$diff,$i,$j,$k,$answer);
    }
}
array_json($value,$limit,$diff,0,1,0,$answer);
echo(json_encode($json));
// [[19,11,3,1],[19,7,5,3],[17,13,3,1],[17,11,5,1],[13,11,7,3]]
