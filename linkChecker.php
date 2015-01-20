<?php
echo '<meta charset="utf-8">';
$start = microtime(true);
$startMemory = memory_get_usage();
$resultat = array();
require "linkChecker.class.php";

// $handle = fopen("host.json", "r");
//         $contents = fread($handle, filesize("host.json"));
//         $list_host = json_decode($contents);
//         var_dump($list_host);
//         die();

$links = array(
    "https://1fichier.com/?4jf62b2mr4",
    "https://pzjcjv2sq9.1fichier.com/",
    "https://1fichier.com/dir/CiLKCBI6",
    "https://1fichier.com/dir/CiLKCBI7",
    "http://www.multiup.org/fr/download/a8ee679c6c86342eeacac0c828c2344f/Cal6loDAdv1ancedWar9fareUpd1-DarkZero.rar",
    "http://www.multiup.org/fr/download/3be2d8b9a2a7ad491e556821075e0944/bzh2696.rar",
    "https://depositfiles.com/files/iwuf30bkz",
    "http://dfiles.eu/files/fsusoerxm",
    );

echo "<fieldset><legend>Résultat</legend>";
foreach ($links as $name) {
    $linkChecker = new linkChecker($name);
    echo 'le lien <a href="'.$name.'">'.$name.'</a> '.($linkChecker->is_valid()? '<span style="background:#1AE993">est valide</span> : '.$linkChecker->getFormatSize()." " : '<span style="background:#E31D22">semble ne pas fonctionner</span> "'.$linkChecker->errorInfo().'" ');
    if(!$linkChecker->is_unknow_host()){
        //on connais l'host utilisé        
        $host = $linkChecker->getHost();
        if(!is_array($host))
            echo "hébergeur : $host <br>";
        else{
            echo "multi hébergeur ($host[0])";
            if($linkChecker->is_valid()){
                echo ": ";
                foreach ($host[$host[0]] as $name => $state) {
                    echo "$name is $state, ";
                }
                
            }
            echo "<br>";
        }
    }
    else
        echo "Cet hébergeur n'est pas reconnu <br>";
    echo "Temps : ".$linkChecker->getTime()."<br>
    mémoire utilisé : ".$linkChecker->getMemory()."<br>
    <hr><br>"; 
}
echo "</fieldset>";

$short = true;
$total = number_format(microtime(true)-$start,3);
if(intval($total)>0){
    $total_time = $total.'s'; 
}
else{
    $decimal = substr($total, strpos($total, '.')+1);
    $total_time = $decimal.'ms';
}

//calcul de la mémoire
$total_memory = memory_get_usage()-$startMemory;
switch ($total_memory) {
    case ($total_memory / 1073741824) > 1:
        $total_memory = round(($total_memory/1073741824), 2) . "Gb";
    case ($total_memory / 1048576) > 1:
        $total_memory = round(($total_memory/1048576), 2) . "Mb";
    case ($total_memory / 1024) > 1:
        $total_memory = round(($total_memory/1024), 2) . "Kb";
    default:
        $total_memory = $total_memory. ' bytes';
}

echo "temps total : $total_time <br> Mémoire totale : $total_memory";
// var_dump($linkChecker->check("https://1fichier.com/?4jf62b2mr4"));
// var_dump($linkChecker->check("https://pzjcjv2sq9.1fichier.com/"));
?>