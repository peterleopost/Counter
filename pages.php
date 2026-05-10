<?php include 'functions.php'; include 'header.php';

$data = loadData();

$pages=[];
foreach($data as $d){
 $pages[$d['page']] = ($pages[$d['page']] ?? 0)+1;
}

arsort($pages);
$max=max($pages ?: [1]);

echo "<div class='card'><h2>Pages</h2><table class='table'><tr><th>Page</th><th>Views</th></tr>";
foreach($pages as $p=>$c){
 $w=($c/$max)*100;
 echo "<tr><td>$p</td><td>$c<div class='bar' style='width:$w%'></div></td></tr>";
}
echo "</table></div>";

echo "</div>";
?>
