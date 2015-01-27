<?php
/*

//utilisation de curl : 
//On initialise
$ch = curl_init();
//on definis le lien sur lequel on envoi la requete
$url = "http://www.multiup.org/api/check-file";
curl_setopt($ch, CURLOPT_URL, $url); 
//on lui dit de nous retourner le résutat, et de suivre les redirection ( http 300+ )
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

//permet de ne pas vérifier le SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//on envoi une requete post ( à enlever pour un get )
$dataArray = array("clef" => "valeur");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dataArray);

//on execute la requete curl et on met le résultat html dans $result
$result = curl_exec($ch);

//on ferme la connexion curl
curl_close($ch);


 */
switch($this->host){

	/////////////////////////////////////////////////
	//Différents codes pour les APIS à marquer ICI //
	//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓//
	/////////////////////////////////////////////////

	//API multiup http://www.multiup.org/fr/upload-api
	case "multiup":
		$data = array("link" => $this->link);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.multiup.org/api/check-file"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        if(is_null($result) || $result['error'] != "success")
            return false;

        //on regarde si parmis les hosts, au moins 1 des hébergeur est valide
        foreach ($result['hosts'] as $key => $value) {
            if($value == "valid")
                $this->is_valid = true;
                
            // $this->host["multiup"][$key] = $value;
        }

        if($this->is_valid){
            $this->size = $result['size'];
        }
	break;


	/////////////////////////////////////////////////
	//         FIN DES CODES POUR LES APIS         //
	/////////////////////////////////////////////////
	default:
		return false;
	break;
}
return $this->is_valid;