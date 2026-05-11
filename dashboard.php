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

$countries = $stats['countries'] ?? [];
$cities = $stats['cities'] ?? [];
$refs = $stats['referrers'] ?? [];
$os = $stats['os'] ?? [];
$devices = $stats['devices'] ?? [];
$external_refs = $stats['external_referrers'] ?? [];

arsort($countries);
arsort($cities);
arsort($refs);
arsort($os);
arsort($devices);
arsort($external_refs);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1a1f3a 100%);
    color: #e2e8f0;
    min-height: 100vh;
    padding: 20px;
}

/* HEADER */
.header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #fff;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.header h1 {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 20px;
    background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: rgba(30, 41, 59, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.3);
    padding: 20px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(96, 165, 250, 0.5);
    transform: translateY(-2px);
}

.stat-label {
    font-size: 13px;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-value {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 28px;
    font-weight: 700;
    background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* CONTAINER */
.container {
    max-width: 1600px;
    margin: 0 auto;
}

/* CHARTS */
.chartbox {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.4) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(148, 163, 184, 0.2);
    height: 400px;
    margin-bottom: 24px;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transition: all 0.3s ease;
}

.chartbox:hover {
    border-color: rgba(96, 165, 250, 0.3);
}

.chartheader {
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    letter-spacing: 0.3px;
}

canvas {
    width: 100% !important;
}

/* GRID BOXES - Now 6 columns */
.grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 24px;
    margin-top: 30px;
}

.box {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.4) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.box:hover {
    border-color: rgba(96, 165, 250, 0.3);
    transform: translateY(-4px);
}

.box h3 {
    margin: 0;
    padding: 16px;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    color: #fff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.3px;
    word-break: break-word;
}

.listbox {
    height: 300px;
    overflow-y: auto;
    padding: 12px;
}

.listbox::-webkit-scrollbar {
    width: 6px;
}

.listbox::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.1);
    border-radius: 10px;
}

.listbox::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #3b82f6, #8b5cf6);
    border-radius: 10px;
}

.item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    background: rgba(148, 163, 184, 0.05);
    border-radius: 8px;
    border: 1px solid rgba(148, 163, 184, 0.1);
    transition: all 0.2s ease;
    font-size: 14px;
    gap: 8px;
}

.item:hover {
    background: rgba(96, 165, 250, 0.1);
    border-color: rgba(96, 165, 250, 0.2);
}

.item span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.item span:last-child {
    font-weight: 600;
    color: #60a5fa;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.item img {
    border-radius: 3px;
}

/* MAP */
.map-section {
    margin: 40px 0;
}

.map-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: white;
    padding: 16px 20px;
    font-weight: 600;
    border-radius: 16px 16px 0 0;
    font-size: 16px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    letter-spacing: 0.3px;
}

.map-container {
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-top: none;
    border-radius: 0 0 16px 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

#map {
    height: 500px;
    background: linear-gradient(135deg, #1a1f3a 0%, #0f172a 100%);
}

.map-footer {
    padding: 12px 16px;
    font-size: 12px;
    background: rgba(15, 23, 42, 0.8);
    border-top: 1px solid rgba(148, 163, 184, 0.1);
    color: #94a3b8;
}

/* RESPONSIVE */
@media (max-width: 1400px) {
    .grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .header h1 {
        font-size: 24px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .grid {
        grid-template-columns: 1fr;
    }

    .chartbox {
        height: 350px;
    }
}
</style>

</head>
<body>

<div class="header">
    <h1>📊 Analytics Dashboard</h1>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Views Today</div>
            <div class="stat-value"><?= number_format($total) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Unique Visitors</div>
            <div class="stat-value"><?= number_format($unique_total) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Live Now</div>
            <div class="stat-value" style="color: #10b981;"><?= $live ?></div>
        </div>
    </div>
</div>

<div class="container">

<div class="chartbox">
    <div class="chartheader">📅 Daily Stats | Views: <?= number_format(array_sum($h_views)) ?> | Unique: <?= number_format(array_sum($h_unique)) ?></div>
    <canvas id="h"></canvas>
</div>

<div class="chartbox">
    <div class="chartheader">📆 Weekly Stats | Views: <?= number_format(array_sum($w_views)) ?> | Unique: <?= number_format(array_sum($w_unique)) ?></div>
    <canvas id="w"></canvas>
</div>

<div class="chartbox">
    <div class="chartheader">📊 Monthly Stats | Views: <?= number_format(array_sum($m_views)) ?> | Unique: <?= number_format(array_sum($m_unique)) ?></div>
    <canvas id="m"></canvas>
</div>

<div class="chartbox">
    <div class="chartheader">📈 Yearly Stats | Views: <?= number_format(array_sum($y_views)) ?> | Unique: <?= number_format(array_sum($y_unique)) ?></div>
    <canvas id="y"></canvas>
</div>

<!-- 6 COLUMN GRID -->
<div class="grid">

<div class="box">
    <h3>🌍 Countries</h3>
    <div class="listbox">
        <?php foreach(array_slice($countries, 0, 15) as $c=>$n): ?>
        <div class="item">
            <span>
                <?php if($c!='UN' && $c!=''): ?>
                <img src="https://flagcdn.com/16x12/<?= strtolower($c) ?>.png" alt="<?= $c ?>">
                <?php endif; ?>
                <?= $c ?>
            </span>
            <span><?= number_format($n) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="box">
    <h3>🏙️ Cities</h3>
    <div class="listbox">
        <?php foreach(array_slice($cities, 0, 15) as $c=>$n): ?>
        <div class="item">
            <span><?= htmlspecialchars($c) ?></span>
            <span><?= number_format($n) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="box">
    <h3>🔗 Internal Referrers</h3>
    <div class="listbox">
        <?php foreach(array_slice($refs, 0, 15) as $r=>$n): ?>
        <div class="item">
            <span><?= htmlspecialchars($r) ?></span>
            <span><?= number_format($n) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="box">
    <h3>💻 Operating Systems</h3>
    <div class="listbox">
        <?php if(!empty($os)): ?>
            <?php foreach(array_slice($os, 0, 15) as $o=>$n): ?>
            <div class="item">
                <span><?= htmlspecialchars($o) ?></span>
                <span><?= number_format($n) ?></span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="item" style="justify-content: center; color: #94a3b8;">
                <span>No data yet</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="box">
    <h3>📱 Devices</h3>
    <div class="listbox">
        <?php if(!empty($devices)): ?>
            <?php foreach(array_slice($devices, 0, 15) as $d=>$n): ?>
            <div class="item">
                <span><?= htmlspecialchars($d) ?></span>
                <span><?= number_format($n) ?></span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="item" style="justify-content: center; color: #94a3b8;">
                <span>No data yet</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="box">
    <h3>🌐 External Referrers</h3>
    <div class="listbox">
        <?php if(!empty($external_refs)): ?>
            <?php foreach(array_slice($external_refs, 0, 15) as $r=>$n): ?>
            <div class="item">
                <span><?= htmlspecialchars($r) ?></span>
                <span><?= number_format($n) ?></span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="item" style="justify-content: center; color: #94a3b8;">
                <span>No data yet</span>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

</div>

<!-- MAP -->
<div class="container">
    <div class="map-section">
        <div class="map-header">🌍 Bezoekers Wereldwijd</div>
        <div class="map-container">
            <div id="map"></div>
            <div class="map-footer">
                🔵 Landen &nbsp;&nbsp; 🔴 Steden (cached)
            </div>
        </div>
    </div>
</div>

<script>
// CHART CONFIG
const chartDefaults = {
    font: {
        family: "'Plus Jakarta Sans', sans-serif"
    },
    color: '#cbd5e1'
};

Chart.defaults.set(chartDefaults);

function makeChart(id, labels, v, u) {
    const ctx = document.getElementById(id).getContext('2d');

    // Modern gradient colors
    let gradBlue = ctx.createLinearGradient(0, 0, 0, 250);
    gradBlue.addColorStop(0, 'rgba(96, 165, 250, 0.8)');
    gradBlue.addColorStop(1, 'rgba(59, 130, 246, 0.4)');

    let gradPurple = ctx.createLinearGradient(0, 0, 0, 250);
    gradPurple.addColorStop(0, 'rgba(168, 85, 247, 0.8)');
    gradPurple.addColorStop(1, 'rgba(139, 92, 246, 0.4)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Views',
                    data: v,
                    backgroundColor: gradBlue,
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                    tension: 0.4
                },
                {
                    label: 'Unique',
                    data: u,
                    backgroundColor: gradPurple,
                    borderColor: '#8b5cf6',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            },
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: {
                            size: 12,
                            weight: 500,
                            family: "'Plus Jakarta Sans', sans-serif"
                        },
                        color: '#cbd5e1',
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, weight: 600, family: "'Plus Jakarta Sans', sans-serif" },
                    bodyFont: { size: 12, family: "'Inter', sans-serif" },
                    borderColor: 'rgba(96, 165, 250, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 12 },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 13, weight: 500 },
                        maxRotation: 45,
                        minRotation: 0,
                        autoSkip: false,
                        padding: 8
                    }
                }
            }
        }
    });
}

// ==========================================
// DAY CHART (00:00 - 23:00)
// ==========================================
let hours = [];
let dayViews = [];
let dayUnique = [];

let hViews = <?= json_encode($stats['views']['day']) ?>;
let hUnique = <?= json_encode($stats['unique']['day']) ?>;

for(let i=0;i<24;i++){
    let label = i.toString().padStart(2,'0') + ':00';
    hours.push(label);
    dayViews.push((hViews && hViews[i]) ? hViews[i] : 0);
    dayUnique.push((hUnique && hUnique[i]) ? hUnique[i] : 0);
}

makeChart('h', hours, dayViews, dayUnique);

// ==========================================
// WEEK CHART (Mon - Sun)
// ==========================================
let wViews = <?= json_encode($w_views) ?>;
let wUnique = <?= json_encode($w_unique) ?>;
let weekLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
let weekViewsData = [];
let weekUniqueData = [];

weekLabels.forEach(day => {
    weekViewsData.push(wViews[day] || 0);
    weekUniqueData.push(wUnique[day] || 0);
});

makeChart('w', weekLabels, weekViewsData, weekUniqueData);

// ==========================================
// MONTH CHART (01 - 31)
// ==========================================
let mViews = <?= json_encode($m_views) ?>;
let mUnique = <?= json_encode($m_unique) ?>;
let monthLabels = [], monthViews = [], monthUnique = [];
for(let i=1;i<=31;i++){
    let d = i.toString().padStart(2,'0');
    monthLabels.push(d);
    monthViews.push(mViews[d]||0);
    monthUnique.push(mUnique[d]||0);
}
makeChart('m', monthLabels, monthViews, monthUnique);

// ==========================================
// YEAR CHART (Jan - Dec)
// ==========================================
let yViews = <?= json_encode($y_views) ?>;
let yUnique = <?= json_encode($y_unique) ?>;
let yearLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
let yearViewsData = [];
let yearUniqueData = [];

yearLabels.forEach(month => {
    yearViewsData.push(yViews[month] || 0);
    yearUniqueData.push(yUnique[month] || 0);
});

makeChart('y', yearLabels, yearViewsData, yearUniqueData);

// AUTO REFRESH
setInterval(function(){location.reload();}, 50000);

// MAP
var map = L.map('map', {
    zoomControl: true,
    scrollWheelZoom: true
}).setView([52, 5], 4);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
    maxZoom: 19
}).addTo(map);

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

    // Countries
    for(let c in data.countries){
        let coordsMap = {
            NL:[52.1,5.3], US:[37,-95], FR:[46,2], GB:[55,-3],
            TR:[39,35], BE:[50.5,4.4], ES:[40,-3], CA:[56,-106]
        };
        if(coordsMap[c]){
            let v=data.countries[c];
            L.circleMarker(coordsMap[c],{
                radius: Math.max(5, Math.log(v)*4),
                color:'#3b82f6',
                fillColor: '#60a5fa',
                fillOpacity:0.7,
                weight: 2
            }).addTo(map).bindPopup("<b style='color:#3b82f6;'>"+c+"</b><br>"+v.toLocaleString()+" visitors", {
                className: 'custom-popup'
            });
        }
    }

    // Cities
    for(let city in data.cities){
        let v=data.cities[city];
        let coords = await geocode(city);

        if(coords){
            L.circleMarker(coords,{
                radius: Math.max(4, Math.log(v)*3),
                color:'#8b5cf6',
                fillColor: '#a78bfa',
                fillOpacity:0.7,
                weight: 2
            }).addTo(map).bindPopup("<b style='color:#8b5cf6;'>"+city+"</b><br>"+v.toLocaleString()+" visitors", {
                className: 'custom-popup'
            });
        }
    }

});
</script>

<style>
.leaflet-popup-content-wrapper {
    background-color: rgba(30, 41, 59, 0.95) !important;
    border: 1px solid rgba(96, 165, 250, 0.3) !important;
    border-radius: 10px !important;
    font-family: 'Inter', sans-serif !important;
    color: #cbd5e1 !important;
}

.leaflet-popup-content {
    font-size: 13px !important;
    line-height: 1.5 !important;
}

.leaflet-container a.leaflet-popup-close-button {
    color: #94a3b8 !important;
}
</style>

</body>
</html>
