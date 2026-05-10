<?php

// Jaar totalen berekenen vanuit stats.json
$statsFile = 'stats.json';
$yearViews = 0;
$yearUnique = 0;

if (file_exists($statsFile)) {
    $data = json_decode(file_get_contents($statsFile), true);

    if (isset($data['views']['year'])) {
        foreach ($data['views']['year'] as $value) {
            $yearViews += $value;
        }
    }

    if (isset($data['unique']['year'])) {
        foreach ($data['unique']['year'] as $value) {
            $yearUnique += $value;
        }
    }
}

session_start();
if(!isset($_SESSION['login'])) exit;

date_default_timezone_set('Europe/Amsterdam');

$stats = json_decode(file_get_contents('stats.json'), true);

// HEADER
$total = array_sum($stats['views']['day']);
$unique_total = array_sum($stats['unique']['day']);

// LIVE (approx last hour via visitors.json)
$visitors = json_decode(file_get_contents('visitors.json'), true);
$live = count($visitors);

// DATA DIRECT
$h_views = $stats['views']['day'];
$h_unique = $stats['unique']['day'];

$w_views = $stats['views']['week'];
$w_unique = $stats['unique']['week'];

$m_views = $stats['views']['month'];
$m_unique = $stats['unique']['month'];

$y_views = $stats['views']['year'];
$y_unique = $stats['unique']['year'];

$countries = $stats['countries'];
$cities = $stats['cities'];
$refs = $stats['referrers'];

arsort($countries);
arsort($cities);
arsort($refs);
?>

<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{font-family:Arial;background:#f5f5f5;margin:0;}

.header{background:#2c3e50;color:#fff;padding:12px;}
.stats{display:flex;gap:30px;}

.container{width:1300px;margin:20px auto;}

.chartbox{
 width:100%;
 height:220px;
width:100%;height:220px;margin-bottom:20px;background:#fff;padding:10px;border-radius:8px;}

.grid{display:flex;gap:20px;}

.box{width:33.33%;background:#fff;border-radius:8px;overflow:hidden;}
.box h3{margin:0;padding:10px;background:#3498db;color:#fff;}

.listbox{height:260px;overflow-y:auto;padding:10px;}

.item{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee;}
.item span{display:flex;align-items:center;gap:6px;}

.chartlabel{
 font-size:13px;
 font-weight:bold;
 margin-bottom:5px;
 color:#333;
}

.chartheader{
 background:#3b82b1;
 color:#fff;
 font-weight:bold;
 font-size:12px;
 padding:5px 10px;
 border-radius:6px 6px 0 0;
 margin:0 0 6px 0;
}

</style>

</head>
<body>

<div class="header">
<div class="stats">
<div>Views: <b><?= $total ?></b></div>
<div>Unique: <b><?= $unique_total ?></b></div>
<div>Live: <b><?= $live ?></b></div>
</div>
</div>

<div class="container">

<div class="chartbox">
<div class="chartheader"><?php echo "Daily | Views: ".array_sum($h_views)." | Unique: ".array_sum($h_unique); ?></div>
<canvas style="width:100% !important;" id="h"></canvas>
</div>
<div class="chartbox">
<div class="chartheader"><?php echo "Week | Views: ".array_sum($w_views)." | Unique: ".array_sum($w_unique); ?></div>
<canvas style="width:100% !important;" id="w"></canvas>
</div>
<div class="chartbox">
<div class="chartheader"><?php echo "Month | Views: ".array_sum($m_views)." | Unique: ".array_sum($m_unique); ?></div>
<canvas style="width:100% !important;" id="m"></canvas>
</div>
<div class="chartbox">
<div class="chartheader"><?php echo "Year | Views: ".array_sum($y_views)." | Unique: ".array_sum($y_unique); ?></div>
<canvas style="width:100% !important;" id="y"></canvas>
</div>

<div class="grid">

<div class="box">
<h3>Countries</h3>
<div class="listbox">
<?php foreach($countries as $c=>$n): ?>
<div class="item">
<span>
<?php if($c!='UN' && $c!=''): ?>
<img src="https://flagcdn.com/16x12/<?= strtolower($c) ?>.png">
<?php endif; ?>
<?= $c ?>
</span>
<span><?= $n ?></span>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="box">
<h3>Cities</h3>
<div class="listbox">
<?php foreach($cities as $c=>$n): ?>
<div class="item"><span><?= $c ?></span><span><?= $n ?></span></div>
<?php endforeach; ?>
</div>
</div>

<div class="box">
<h3>Referrers</h3>
<div class="listbox">
<?php foreach($refs as $r=>$n): ?>
<div class="item"><span><?= $r ?></span><span><?= $n ?></span></div>
<?php endforeach; ?>
</div>
</div>

</div>

</div>

<script>
function makeChart(id,labels,v,u){
 const ctx = document.getElementById(id).getContext('2d');

 // BLUE gradient
 let gradBlue = ctx.createLinearGradient(0,0,0,200);
 gradBlue.addColorStop(0,'#6fb3e0');
 gradBlue.addColorStop(1,'#1f4e79');

 // RED gradient
 let gradRed = ctx.createLinearGradient(0,0,0,200);
 gradRed.addColorStop(0,'#ff8a80');
 gradRed.addColorStop(1,'#b71c1c');

 new Chart(ctx,{
  type:'bar',
  data:{
   labels:labels,
   datasets:[
    {
      label:'Views',
      data:v,
      backgroundColor:gradBlue,
      borderColor:'#c0c0c0',
      borderWidth:1,
      borderRadius:4
    },
    {
      label:'Unique',
      data:u,
      backgroundColor:gradRed,
      borderColor:'#c0c0c0',
      borderWidth:1,
      borderRadius:4
    }
   ]
  },
  options:{
    animation:false,
    responsive:true,
    maintainAspectRatio:false,
    plugins:{
      legend:{display:true}
    },
    scales:{
      y:{beginAtZero:true},
      x:{grid:{display:false}}
    }
  }
 });
}

// DAY

// DAY CHART (fixed 00-23 axis)
let hours = [];
let dayViews = [];
let dayUnique = [];

let hViews = <?= json_encode($stats['views']['day']) ?>;
let hUnique = <?= json_encode($stats['unique']['day']) ?>;

for(let i=0;i<24;i++){
    let label = i.toString().padStart(2,'0');
    hours.push(label);

    dayViews.push((hViews && hViews[i]) ? hViews[i] : 0);
    dayUnique.push((hUnique && hUnique[i]) ? hUnique[i] : 0);
}

makeChart('h', hours, dayViews, dayUnique, 'Day');


// WEEK
makeChart('w',['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
[
<?= $w_views['Mon']??0 ?>,
<?= $w_views['Tue']??0 ?>,
<?= $w_views['Wed']??0 ?>,
<?= $w_views['Thu']??0 ?>,
<?= $w_views['Fri']??0 ?>,
<?= $w_views['Sat']??0 ?>,
<?= $w_views['Sun']??0 ?>
],
[
<?= $w_unique['Mon']??0 ?>,
<?= $w_unique['Tue']??0 ?>,
<?= $w_unique['Wed']??0 ?>,
<?= $w_unique['Thu']??0 ?>,
<?= $w_unique['Fri']??0 ?>,
<?= $w_unique['Sat']??0 ?>,
<?= $w_unique['Sun']??0 ?>
]);

// MONTH
let mViews = <?= json_encode($m_views) ?>;
let mUnique = <?= json_encode($m_unique) ?>;
let monthLabels=[], monthViews=[], monthUnique=[];
for(let i=1;i<=31;i++){
 let d = i.toString().padStart(2,'0');
 monthLabels.push(d);
 monthViews.push(mViews[d]||0);
 monthUnique.push(mUnique[d]||0);
}
makeChart('m',monthLabels,monthViews,monthUnique);

// YEAR
makeChart('y',['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
[
<?= $y_views['Jan']??0 ?>,
<?= $y_views['Feb']??0 ?>,
<?= $y_views['Mar']??0 ?>,
<?= $y_views['Apr']??0 ?>,
<?= $y_views['May']??0 ?>,
<?= $y_views['Jun']??0 ?>,
<?= $y_views['Jul']??0 ?>,
<?= $y_views['Aug']??0 ?>,
<?= $y_views['Sep']??0 ?>,
<?= $y_views['Oct']??0 ?>,
<?= $y_views['Nov']??0 ?>,
<?= $y_views['Dec']??0 ?>
],
[
<?= $y_unique['Jan']??0 ?>,
<?= $y_unique['Feb']??0 ?>,
<?= $y_unique['Mar']??0 ?>,
<?= $y_unique['Apr']??0 ?>,
<?= $y_unique['May']??0 ?>,
<?= $y_unique['Jun']??0 ?>,
<?= $y_unique['Jul']??0 ?>,
<?= $y_unique['Aug']??0 ?>,
<?= $y_unique['Sep']??0 ?>,
<?= $y_unique['Oct']??0 ?>,
<?= $y_unique['Nov']??0 ?>,
<?= $y_unique['Dec']??0 ?>
]);

setInterval(function(){location.reload();},50000);
</script>


<!-- 🌍 Wereldkaart COMPLETE PRO -->
<div style="margin:40px 0;">
    <div style="
        background: linear-gradient(90deg,#2c3e50,#4ca1af);
        color:white;
        padding:12px 15px;
        font-weight:bold;
        border-radius:8px 8px 0 0;
        font-size:16px;">
        🌍 Bezoekers wereldwijd
    </div>

    <div style="
        border:1px solid #dcdcdc;
        border-top:none;
        border-radius:0 0 8px 8px;
        overflow:hidden;
        box-shadow:0 2px 8px rgba(0,0,0,0.1);">

        <div id="map" style="height:450px;"></div>

        <div style="
            padding:10px;
            font-size:12px;
            background:#f9f9f9;
            border-top:1px solid #eee;">
            🔵 Landen &nbsp;&nbsp; 🔴 Steden (cached)
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
var map = L.map('map').setView([52,5],4);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// cache
var geoCache = JSON.parse(localStorage.getItem('geoCache') || '{}');

function saveCache(){
    localStorage.setItem('geoCache', JSON.stringify(geoCache));
}

async function geocode(city){
    if(geoCache[city]) return geoCache[city];

    try{
        let r = await fetch("https://nominatim.openstreetmap.org/search?format=json&q="+city);
        let d = await r.json();
        if(d[0]){
            let c=[parseFloat(d[0].lat), parseFloat(d[0].lon)];
            geoCache[city]=c;
            saveCache();
            return c;
        }
    }catch(e){}
    return null;
}

fetch('stats.json?' + new Date().getTime())
.then(r=>r.json())
.then(async data=>{

    // landen
    for(let c in data.countries){
        let coordsMap = {
            NL:[52.1,5.3], US:[37,-95], FR:[46,2], GB:[55,-3],
            TR:[39,35], BE:[50.5,4.4], ES:[40,-3], CA:[56,-106]
        };
        if(coordsMap[c]){
            let v=data.countries[c];
            L.circleMarker(coordsMap[c],{
                radius:Math.max(4,Math.log(v)*3),
                color:'#3498db',
                fillOpacity:0.7
            }).addTo(map).bindPopup("<b>"+c+"</b><br>"+v+" bezoekers");
        }
    }

    // steden (met cache)
    for(let city in data.cities){
        let v=data.cities[city];
        let coords = await geocode(city);

        if(coords){
            L.circleMarker(coords,{
                radius:Math.max(3,Math.log(v)*2),
                color:'#e74c3c',
                fillOpacity:0.7
            }).addTo(map).bindPopup("<b>"+city+"</b><br>"+v+" bezoekers");
        }
    }

});
</script>


<div class="dashboard-columns">
    <div class="column" style="width: 16.66%; float: left; padding: 10px;">
        <h3>OS</h3>
        <p><?= $os ?></p>
    </div>
    <div class="column" style="width: 16.66%; float: left; padding: 10px;">
        <h3>Hardware</h3>
        <p><?= $hardware ?></p>
    </div>
    <div class="column" style="width: 16.66%; float: left; padding: 10px;">
        <h3>Referrers</h3>
        <ul>
            <?php foreach ($referrers as $referrer): ?>
                <li><?= $referrer['url'] ?>: <?= $referrer['count'] ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
</body>
</html>
