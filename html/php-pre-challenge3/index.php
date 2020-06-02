<?php
$limit = $_GET['target'];
settype($limit,"int");

//DB接続
$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';
try{
    $db = new PDO("$dsn", "$dbuser", "$dbpassword");
}catch(PDOException $e){
    echo "DB接続エラー:" . $e->getMessage();
    exit();
}
$dbdata = $db->query("SELECT * FROM prechallenge3");
$prechallenge3 = $dbdata -> fetchAll();
$value = array_column($prechallenge3, "value");


rsort($value);
for($i=0; $i < count($value); $i++){
    settype($value[$i],"int");
}

//合計値が$limitになる組み合わせを$jsonに保存する関数
function array_json($value, $limit, $i, $j, $k){
    global $json;
    global $diff;
    global $answer;
    global $check;
    if($limit === $value[$i]){//組み合わせなしでtargetになる場合
        $json[] = [$value[$i]];
        unset($answer);
        $i++;
        array_json($value, $limit, $i, $j, $k);
    }
    if($j === 1){
        $answer = [$value[$i]];
        $diff = $limit - $value[$i];
    }
    $range = array_slice($value, $i+$j);
    $max = $range[$k];
    //関数を終わらせるかどうか
    if($i < count($value)){
        if($diff > array_sum($range) && $k < count($value)){
            unset($answer);
            $j = 1;
            $k++;
        }elseif($diff > array_sum($range) or $diff < 0){
            unset($answer);
            $i++;
            $j = 1;
            $k = 0;
        }elseif($diff<$max){
            //target13で[7,5.1]が得られた時に[7,2,3,1]を取りこぼさない措置
            if(isset($check) && in_array(min($answer),$check)){
                $del_key=array_search(min($answer),$check);
                unset($check[$del_key]);
                $diff = $diff + min($answer);
                array_pop($answer);
            }else{
                $j++;
            }
        }elseif($diff>$max){
            $diff = $diff - $max;
            $j++;
            $answer[] = $max;
        }elseif($diff ===$max){
            $answer[] = $max;
            //target12で[7,5]が得られた時に[7,4,1]を取りこぼさない措置
            if(isset($json)&&in_array($answer,$json)){
                $j++;
                array_pop($answer);
            }else{
                $json[] = $answer;
                $check=$answer;
                unset($answer);
                $j = 1;
            }
        }
        array_json($value,$limit,$i,$j,$k);
    }
    if(is_null($json)){
        $json=array();
    }
}

array_json($value,$limit,0,1,0);
echo(json_encode($json));
