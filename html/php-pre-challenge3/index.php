<?php
//targetが正の整数か確認
$limit = $_GET['target'];
if(! ctype_digit($limit) || substr("$limit",0,1) === "0"){
    http_response_code(400);
    exit();
}
settype($limit, "int");

//DB接続
$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';
try{
    $db = new PDO($dsn, $dbuser, $dbpassword);
}catch(PDOException $e){
    http_response_code(500);
    exit();
}

//DBの配列をソートと型変換
$dbdata = $db -> query("SELECT * FROM prechallenge3 ORDER BY value DESC");
$prechallenge3 = $dbdata -> fetchAll();
$values = array_column($prechallenge3, "value");
$values_count = count($values);
for($i = 0; $i < $values_count; $i++){
    settype($values[$i], "int");
}

//合計値が$limitになる組み合わせを$jsonに保存する関数
function array_json($values, $limit, $i = 0, $j = 1, $k = 0, $json = array(), $diff = null, $answer = null, $check = null){
    //$json;合計値が$limitになる組み合わせを保存する変数
    //$diff;差を保存する変数
    //$answer;組み合わせを一時的に保存する変数
    //$check;取りこぼしがないか確認する変数
    if($limit === $values[$i]){//組み合わせなしで$limitになる場合
        $json[] = [$values[$i]];
        unset($answer);
        $i++;
        return array_json($values, $limit, $i, $j, $k, $json, $diff, $answer, $check);
    }
    if($j === 1){
        $answer = [$values[$i]];
        $diff = $limit - $values[$i];
    }
    $range = array_slice($values, $i+$j);//配列の範囲を絞る
    $max = $range[$k];//絞られた配列の中で最大の数値
    if($i < count($values)){//関数を終わらせるかどうか
        if($diff < 0){
            unset($answer);
            unset($check);
            $i++;
            $j = 1;
            $k = 0;
        }elseif($diff > array_sum($range)){
            if($k < count($values)){
                unset($answer);
                $j = 1;
                $k++;
            }else{
                unset($answer);
                unset($check);
                $i++;
                $j = 1;
                $k = 0;
            }
        }elseif($diff < $max){
            if(isset($check) && in_array(min($answer),$check)){//target13で[7,5.1]が得られた時に[7,2,3,1]を取りこぼさない措置
                $del_key = array_search(min($answer),$check);
                unset($check[$del_key]);
                $diff = $diff + min($answer);
                array_pop($answer);
            }else{
                $j++;
            }
        }elseif($diff > $max){
            $diff = $diff - $max;
            $j++;
            $answer[] = $max;
        }elseif($diff === $max){//合計値がlimitになった場合
            $answer[] = $max;
            if(isset($json) && in_array($answer,$json)){//target12で[7,5]が得られた時に[7,4,1]を取りこぼさない措置
                $j++;
                array_pop($answer);
            }else{
                $json[] = $answer;
                $check = $answer;
                unset($answer);
                $j = 1;
            }
        }
        return array_json($values, $limit, $i, $j, $k, $json, $diff, $answer, $check);
    }
    return $json;
}

$out_json = array_json($values, $limit);
//$out_jsonをjson形式で出力
echo(json_encode($out_json));
