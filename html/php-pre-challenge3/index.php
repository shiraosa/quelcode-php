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

function array_json($value,$limit,$diff,$i,$j,$k,$check,$json,$answer){
    global $json;
    $range = array_slice($value,$i+$j);
    $max = $range[$k];
    if($j==1){
        $answer=[$value[$i]];
        $diff = $limit - $value[$i];
    }
    if($i < count($value)){
        if($diff>array_sum($range)&&$k<count($value)){
            unset($answer);
            $k++;
            $j=1;
            //スタートへ
        }elseif($diff>array_sum($range)){
            unset($answer);
            $k = 0;
            $i++;
            $j=1;
            //スタートへ
        }elseif($diff<$max){
            $j++;
            //スタートへ
        }elseif($diff>$max && in_array($max,array($check))){
            $check = array_diff($check,$max);
            $range = array_slice($value,$j++);
            $max = $range[$k];
            if($diff>array_sum($range)){
                unset($answer);
                $j = 1;
                //スタートへ
            }elseif($diff<$max){
                $j++;
                //スタートへ
            }elseif($diff>$max){
                $diff = $diff - $max;
                $j++;
                $answer[] = $max;
                //スタートへ
            }
        }elseif($diff>$max){
            $diff = $diff - $max;
            $j++;
            $answer[] = $max;
            //スタートへ
        }elseif($diff - $max===0 ){
            $answer[] = $max;
            if(isset($json)&&in_array($answer,$json) ){
                $k++;
                $j=1;
                unset($answer);
                //スタートへ
            }else{
                $json[] = $answer;
                $check = $answer;
                unset($answer);
                $j = 1;
                //スタートへ
            }
        }
        array_json($value,$limit,$diff,$i,$j,$k,$check,$json,$answer);
    }
}

array_json($value,$limit,$diff,0,1,0,$check,$json,$answer);
echo(json_encode($json));
