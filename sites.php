<?php include 'functions.php'; include 'header.php';

$data = loadData();

$sites=[];
foreach($data as $d){
 $sites[$d['site']] = ($sites[$d['site']] ?? 0)+1;
}

echo "<div class='card'><h2>Sites</h2><table class='table'><tr><th>Site</th><th>Views</th></tr>";
foreach($sites as $s=>$c){
 echo "<tr><td>$s</td><td>$c</td></tr>";
}
echo "</table></div>";

echo "</div>";
?>
