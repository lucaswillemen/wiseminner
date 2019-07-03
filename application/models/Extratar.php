<?php
class Extratar extends CI_Model {
    public function inserir($user_id, $btc, $pts, $rentabilidade, $descricao)
    {  
       
		$extrato = [
			"user_id" => $user_id,
			"btc" => $btc,
			"pts" => $pts,
			"rentabilidade" => $rentabilidade,
			"descricao" => $descricao
		];
		$this->db->insert("extrato", $extrato);
    }
}