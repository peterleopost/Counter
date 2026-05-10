<?php
include 'functions.php';
$data = loadData();

$live=0;
$unique_total=0;

foreach($data as $d){
 if($d['unique']===true){
  $unique_total++;
 }
 if(strtotime($d['time']) > strtotime("-5 minutes")){
  $live++;
 }
}

echo json_encode([
 "live"=>$live,
 "unique"=>$unique_total
]);
?>
