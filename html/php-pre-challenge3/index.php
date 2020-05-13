<?php

//URLで入力される値　→　http://localhost:10080/php-pre-challenge3/index.php?target=34
$limit = $_GET['target'];
//1以上の整数ではない場合は、HTTP レスポンスステータスコード[400]を返却 ↓0以上の整数のみ（正規表現:ゼロ埋めなし）
if (!is_numeric($limit) || $limit <= 0 || preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $limit)) {
  http_response_code(400);
  echo json_encode('400 Bad Request :' . $limit);
  exit();
}

//文字列から数値に変換する
$limit = (int) $limit;

//接続情報
$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';

//db接続時の例外処理
//インスタンス->プロパティ名
try {
  $db = new PDO($dsn, $dbuser, $dbpassword);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode('DB接続エラー' . $e->getMessage());
  exit();
}

// DBの値を$saveに格納。（targetの値以下の数値に限る）
$statements = $db->prepare('SELECT * FROM prechallenge3 WHERE value <= ? ORDER BY value ASC');
$statements->bindParam(1, $limit, PDO::PARAM_INT);
$statements->execute();
foreach ($statements as $statement) {
  $save[] = $statement['value'];
}

//targetにある全組み合わせを取得する関数 ※$digitは組み合わせを取得したい桁数
function combination($number, $digit)
{
  $totalnumber = count($number);  //配列の数を数えて$totalnumberに代入
  if ($digit === 1) {               //取得したい桁数が1の場合、配列の中の数を配列（番地）ごとに1つずつ$arrsに代入する。
    for ($i = 0; $i < $totalnumber; $i++) {
      $arrs[$i] = array($number[$i]);
    }
  } elseif ($digit > 1) {
    $j = 0;
    for ($i = 0; $i < $totalnumber - $digit + 1; $i++) {
      $ts = combination(array_slice($number, $i + 1), $digit - 1); //array_sliceは配列の一部を展開する。
      foreach ($ts as $t) {
        array_unshift($t, $number[$i]);
        $arrs[$j] = $t;
        $j++;
      }
    }
  }
  return $arrs;
}

//ターゲットの値と取得した組み合わせの和比較する　※配列の中に配列があり、値が入っている。
//2次元配列の外側を回す
$length = count($save);
for ($i  = 0; $i < $length; $i++) {
  $count = $i + 1;
  $temps = combination($save, $count);
  //2次元配列の内側を回す
  $innerlength = count($temps);
  for ($j = 0; $j < $innerlength; $j++) {
    $sum = array_sum($temps[$j]);
    //内側の配列の合計値が$limiteと一致した場合、$answerに配列として代入する。
    if ($sum === $limit) {
      $answers[] = $temps[$j];
    }
  }
}

if (is_null($answers)) {
  $answers = [];
}

//答えをjson形式で出力
echo json_encode($answers, JSON_NUMERIC_CHECK);
