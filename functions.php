<?php
function loadData(){
    if(!file_exists('stats.json')) file_put_contents('stats.json',"[]");
    return json_decode(file_get_contents('stats.json'), true) ?: [];
}
function saveData($data){
    file_put_contents('stats.json', json_encode($data));
}
function anonymizeIP($ip){
    return preg_replace('/\d+$/','0',$ip);
}
?>
