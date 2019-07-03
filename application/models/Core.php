<?php
class Core extends CI_Model {
    
    function address(){
        $ch = curl_init();  
        $options = array(
            CURLOPT_URL            => 'https://api.opencash.com.br/wallet/wisecoin/getnewaddress',
            CURLOPT_HTTPHEADER     => ['Authorization: 5bd24a740b00ae3584de97b6e56dbf9f7e090c1a'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => 0
        ); 
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch); 
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return json_decode($content);
    }
}