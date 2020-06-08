<?php
//targetが正の整数か確認
$limit = $_GET['target'];
if(! ctype_digit($limit) || substr("$limit", 0, 1) === "0"){
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
$dbdata = $db -> query("SELECT * FROM prechallenge3");
$prechallenge3 = $dbdata -> fetchAll();
$values = array_column($prechallenge3, "value");
$values_count = count($values);
for($i = 0; $i < $values_count; $i++){
    settype($values[$i], "int");
}

//https://stabucky.com/wp/archives/2188 を利用しました。変数を理解しやすいように変えてあります。組み合わせをツリーにした際に階層が進むにつれて枝が少なくなるのをarray_sliceで再現したものだと理解してます。
function array_combi($values, $pick){
    $count_values = count($values);
    if($count_values < $pick ){
        return;
    }elseif($pick === 1){
        for($i =0; $i < $count_values; $i++){
            $array_combi[$i] = array($values[$i]);
        }
    }elseif($pick > 1){
        $j = 0;
        for($i = 0; $i < $count_values - $pick + 1; $i++){
            $read_array_combi = array_combi(array_slice($values, $i+1), $pick-1);
            foreach($read_array_combi as $combi){
                array_unshift($combi, $values[$i]);
                $array_combi[$j] = $combi;
                $j++;
            }
        }
    }
    return $array_combi;
}

//全組み合わせの取得
$all_array_combi = array();
for($i = 1; $i <= $values_count; $i++){
    $array_combi = array_combi($values, $i);
    $all_array_combi = array_merge($all_array_combi, $array_combi);
}

//$limitと$checkを照合
$answer =array();
foreach($all_array_combi as $check){
    if(array_sum($check) === $limit){
        $answer[] = $check; 
    }
}

//json形式で出力
echo(json_encode($answer));
