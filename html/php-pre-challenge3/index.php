<?php
$limit = $_GET['target'];
$limit = 18;

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
$i = 0;
$j = 1;
$k = 0;


function array_json($value,$limit,$i){
    if($i===0){
        $j = 1;
        $k = 0;
    }
    $range = array_slice($value,$j);
    $diff = $limit - $value[$i];
    $max = $range[$k];
    $answer=[$value[$i]];
    $check =array();
    $json =array();
    if($i < count($value)){
        if($diff>array_sum($range)){
            unset($answer);
            $j = 1;
            $k = 0;
            $i++;
            //スタートへ
            array_json($value,$limit,$i);
        }elseif($diff<$max){
            $j++;
            //スタートへ
            array_json($value,$limit,$i);
        }elseif($diff>$max && in_array($max,$check)){
            //$checkから$maxを削除
            $check = array_diff($check,$max);
            $range = array_slice($value,$j++);
            $max = $range[$k];
            if($diff>array_sum($range)){
                unset($answer);
                $j = 1;
                //スタートへ
                array_json($value,$limit,$i);
            }elseif($diff>$max){
                $diff = $diff - $max;
                $j++;
                $answer[] = $max;
                //スタートへ
                array_json($value,$limit,$i);
            }     
        }elseif($diff>$max){
            $diff = $diff - $max;
            $j++;
            $answer[] = $max;
            //スタートへ
            array_json($value,$limit,$i);
        }elseif($diff - $max===0 ){
            $answer[] = $max;
            $check = $answer;
            if(in_array($answer,$json)){
                $k++;
                unset($answer);
                //スタートへ
                array_json($value,$limit,$i);
            }else{
                $json[] = $answer;
                //スタートへ
                array_json($value,$limit,$i);
            }
        }
    }
}
array_json($value,$limit,0);
echo(json_encode($json));
