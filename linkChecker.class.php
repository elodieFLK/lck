<?php

Class LinkChecker{
    //information de l'objet en cours
    private $link = "";
    private $host = "";
    private $httpReturnCode = 0;
    private $is_valid = false;
    private $is_premium = false;
    private $is_dir = false;
    private $size = 0;

    //statistiques
    private $time = 0;
    private $memory = 0;
    
    //utilisé pour l'éxécution
    private $db;
    private $current_host= array();
    private $config;
    private $start = 0;
    private $HTML = "";
    private $headers = "";
    private $error = array();
    private $file_host = "host.json";

    const BYTE_MULTIPLICATOR = 1024;

    public function __construct($link, $check_size = true){
        global $db;
        $this->db = $db;
        $this->check_size = $check_size;

        //on génère les configs
        $this->config["size_regex"] = "~([0-9]+[\\.|,]*[0-9]*)\s*([K|M|G|T]*)[i]*[B|O]*~is";

        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();

        $this->check($link);


        $this->setExecutionTime();
        $this->setExecutionMemory();


    }


    function check($link){
        //on met le lien dans l'objet
        $this->setLink($link);



        $this->foundHost();

        if($this->linkUnreachable())
            return false;

        //l'hébergeur n'est pas reconnu
        if(empty($this->host))
            return false;

        $this->checkLink();

    }



    public function checkLink(){
        //on télécharge la page distante
        $this->downloadHtmlLink();

        //on enlève les &nbsp;
        $this->HTML = preg_replace("~&nbsp;~", " ", $this->HTML);

        if($this->current_host['need_api'])
            return $this->checkApi();

        //on vérifie si le lien est dossier ( si les regex dossiers sont disponible )
        if(!empty($this->current_host['dir_regex']) && preg_match($this->current_host['dir_regex'], $this->HTML, $matches)){
            $this->is_valid = true;
            $this->is_dir = true;
        }
        else{
            //on regarde si l'on as une regex de validité
            if(!empty($this->current_host['valid_regex'])){
                //on cherche à valider la regex
                if(preg_match($this->current_host['valid_regex'], $this->HTML )){
                    $this->is_valid = true;
                }
            }
            else{
                //on regarde si l'on as une regex d'invalidité
                if(empty($this->current_host['invalid_regex']))
                    return false;
                else{
                    if(preg_match($this->current_host['invalid_regex'], $this->HTML )){
                        $this->is_valid = false;
                    }
                    else
                        $this->is_valid = true;
                }

            }
        }

        //on regarde si le lien necessite un premium
        if(!empty($this->current_host['premium']) && preg_match($this->current_host['premium'], $this->HTML )){
            $this->is_valid = false;
            $this->is_premium = true;
        }

        //on récupère la taille des fichiers
        if($this->is_valid && $this->check_size){
            if($this->is_dir){
                //si c'est un dossier > plusieurs tailles
                preg_match_all($this->current_host['size_multiple_regex'], $this->HTML, $matches);
                foreach ($matches[1] as $value) {
                    $this->size += $this->sizeUnFormat($value);
                }
            }
            else{
                //si c'est un fichier > une seule taille
                preg_match($this->current_host['size_single_regex'], $this->HTML, $matches);
                $this->size = $this->sizeUnFormat($matches[1]);
            }
        }


        return $this->is_valid;

    }

    public function checkAPI(){
        require __DIR__.DIRECTORY_SEPARATOR."checkApi.php";
        return false;
    }

    public function getHttpResponseCode() {
        if(!is_file(__DIR__."/cache/".urlencode($this->link).".headers")){
            $headers = get_headers($this->link);

            return substr($headers[0], 9, 3);
        }
        else{
            return unserialize(fread(fopen(__DIR__."/cache/".urlencode($this->link).".headers", "a+"), filesize(__DIR__."/cache/".urlencode($this->link).".headers")))["http_code"];
        }
    }

    public function foundHost(){
        $handle = fopen($this->file_host, "r");
        $contents = fread($handle, filesize($this->file_host));
        $list_host = json_decode($contents, true);
        foreach ($list_host as $value) {
            if(preg_match($value['name_regex'], $this->link)){
                $this->current_host = $value;
                $this->host = $value['name'];
            }
        }

        return $this->is_unknow_host();
    }
    
    public function getHttpCode(){
        if(!empty($this->html))
            return $this->headers["http_code"];
        else
            return $this->getHttpResponseCode();
    }

    public function downloadHtmlLink(){
        if(!is_file(__DIR__."/cache/".urlencode($this->link).".html") || !is_file(__DIR__."/cache/".urlencode($this->link).".headers")){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->link); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            $headers = curl_getinfo($ch);
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            

            $this->setError(
                array(
                    "error" => $curl_error,
                    "errno" => $curl_errno
                ), "curl"
            );
            
            $this->headers = unserialize(fread(fopen(__DIR__."/cache/".urlencode($this->link).".headers", "a+"), filesize(__DIR__."/cache/".urlencode($this->link).".headers")));
            $this->headers = $headers;
            $this->HTML = $result;

            fwrite(fopen(__DIR__."/cache/".urlencode($this->link).".html", "w+"), $this->HTML);
            fwrite(fopen(__DIR__."/cache/".urlencode($this->link).".headers", "w+"), serialize($this->headers));
            
            curl_close($ch);
        }
        else{
            $this->HTML = fread(fopen(__DIR__."/cache/".urlencode($this->link).".html", "a+"), filesize(__DIR__."/cache/".urlencode($this->link).".html"));
        }
    }

    public function setLink($link){
        $this->link = $link;
    }

    public function is_valid(){
        return $this->is_valid;
    }

    public function errorInfo(){
        if($this->linkUnreachable())
            return "HTTP ERROR (".$this->getHttpCode().")";
        
        if($this->is_unknow_host())
            return "unknow host";

        return "lien invalide";
    }

    public function linkUnreachable(){
        return ($this->getHttpCode() >= 400);
    }

    public function httpReturnCode(){
        return $this->getHttpCode();
    }

    public function getSize(){
        return $this->size();
    }

    public function getFormatSize(){
        return $this->formatSize();
    }

    public function getError(){
        if(!empty($name))
            if(!empty($this->error[$name]))
                return $this->error[$name];
            else
                return false;
        else
            return $this->error;
    }

    public function getHost(){
        return $this->host;
    }


    public function is_unknow_host(){
        return empty($this->host);
    }

    public function getTime(){
        return $this->time;
    }

    public function getMemory(){
        return $this->memory;
    }

    public function setError($value, $name = null){
        if(!empty($name))
            $this->error[$name] = $value;
        else
            $this->error[] = $value;
        }

    public function formatSize(){
        switch ($this->size) {
            case ($this->size / 1073741824) > 1:
                return round(($this->size/1073741824), 2) . "Gb";
            case ($this->size / 1048576) > 1:
                return round(($this->size/1048576), 2) . "Mb";
            case ($this->size / 1024) > 1:
                return round(($this->size/1024), 2) . "Kb";
            default:
                return $this->size . ' bytes';
        }
    }

    function setExecutionMemory(){
        $this->memory = memory_get_usage()-$this->startMemory;
        return $this->formatMemoryUsed();
    }

    function setExecutionTime(){
        $total = number_format(microtime(true)-$this->startTime,3);
        if(intval($total)>0){
            $total_time = $total.'s'; 
        }
        else{
            $decimal = substr($total, strpos($total, '.')+1);
            $total_time = $decimal.'ms';
        }
        $this->time = $total_time;
    }

    public function formatMemoryUsed(){
        switch ($this->memory) {
            case ($this->memory / 1073741824) > 1:
                $this->memory = round(($this->memory/1073741824), 2) . "Gb";
            case ($this->memory / 1048576) > 1:
                $this->memory = round(($this->memory/1048576), 2) . "Mb";
            case ($this->memory / 1024) > 1:
                $this->memory = round(($this->memory/1024), 2) . "Kb";
            default:
                $this->memory = $this->memory. ' bytes';
        }
    }

    private function sizeUnFormat($sizeText){
        preg_match($this->config['size_regex'], $sizeText, $matches);
        $number = $matches[1];
        $sizeType = strtoupper(trim($matches[2]));
        // 10**9 vaux : 10⁹ ou pow(10,9)
        switch ($sizeType) {
            case "K":
                return $number*self::BYTE_MULTIPLICATOR;
            break;
            case "M":
                return $number*pow(self::BYTE_MULTIPLICATOR, 2);
            break;
            case "G":
                return $number*pow(self::BYTE_MULTIPLICATOR ,3);
            break;
            case "T":
                return $number*pow(self::BYTE_MULTIPLICATOR ,4);
            break;
            
            default:
                return $number;
            break;
        }

    }

    private function slugIt($name) {
        /*
           Cleans the string given:
            - Removes all special caracters
            - Sets every string in lower case
            - Removes all similar caracters
        */
        
        $a = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ@()/[]|\'&';
        $b = 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn---------';
        $url = utf8_encode(strtr(utf8_decode($name), utf8_decode($a), utf8_decode($b)));
        $url = preg_replace('/ /', '-', $url);  
        $url = trim(preg_replace('/[^a-z|A-Z|0-9|-]/', '', strtolower($url)), '-');
        $url = preg_replace('/\-+/', '-', $url);
        $url = urlencode($url);

        return $url;
    }
}