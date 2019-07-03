<?php
class Wisecoin extends CI_Controller {

	public function index(){
		$this->user->check($this->input->get_request_header('Authorization'));
		$wisecoin = $this->db->select_sum('wisecoin')->get("wisecoin_address")->row();		
		$config = $this->db->get("config")->row();
		$data = [
			'supply' => $wisecoin->wisecoin,
			'tomorrow' => $wisecoin->wisecoin/$config->wise_function+0.01
		];
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	public function chart(){
		$user = $this->user->check($this->input->get_request_header('Authorization'));
		$data = $this->db->select('DATE(time) data, cota')->limit(10)->order_by('id', "desc")->get("wisecoin_chart")->result();
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	public function create(){
		$user = $this->user->check($this->input->get_request_header('Authorization'));
		$wise_address = $this->db->where("user_id", $user->id)->get("wisecoin_address")->row();
		if ($wise_address) {	    	
	      	return $this->output->set_content_type('application/json')->set_status_header(404);
	      	exit();
	    }

	    $data = [
	    	'user_id' => $user->id,
	    	'address' => $address = $this->core->address(),
	    	'wisecoin' => 0
	    ];
	    $this->db->insert('wisecoin_address', $data);
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function buy(){
		$user = $this->user->check($this->input->get_request_header('Authorization'));		
		$user = $this->db->select('*')->where('token', $this->input->get_request_header('Authorization'))->get('users')->row();
		$config = $this->db->get("config")->row();
		$valor = $this->input->post("valor");

		$wisecoin = $valor*$config->cota/$config->wise_cota;
		$consierge = $this->input->post("consierge");

		if ($consierge) {
			$consierge = $this->db->select('*')->where('nome', $consierge)->where('corretor', 1)->get('users')->row();
			if (!$consierge) {
				$this->output->set_content_type('application/json')->set_status_header(403);
	      		exit();
			}
		}




	    if ($valor > $user->btc) {	    	
	      	$this->output->set_content_type('application/json')->set_status_header(402);
	      	exit();
	    }





		$this->db->where("id", $user->id)->set("btc", "btc-".$valor, FALSE)->update("users");
		$this->db->where("user_id", $user->id)->set("wisecoin", "wisecoin+".$wisecoin, FALSE)->update("wisecoin_address");
		$this->extratar->inserir($user->id, 0-$valor, 0, 0, 'wisecoin');

		if ($consierge) {			
			$this->db->where("user_id", $consierge->id)->set("wisecoin", "wisecoin+".$wisecoin*0.1, FALSE)->update("wisecoin_address");
			$this->db->where("id", $consierge->id)->set("btc", "btc+".$valor*0.1, FALSE)->update("users");

			$this->extratar->inserir($consierge->id, $valor*0.1, 0, 0, 'consierge');

			$extrato_wise = [
				"user_id" => $consierge->id,
				"wsc" => $wisecoin*0.1,
				"cota" => $config->wise_cota,
				"descricao" => "consierge"
			];
			$this->db->insert("wisecoin_extrato", $extrato_wise);
		}

		$extrato_wise = [
			"user_id" => $user->id,
			"wsc" => $wisecoin,
			"cota" => $config->wise_cota,
			"descricao" => "buy"
		];
		$this->db->insert("wisecoin_extrato", $extrato_wise);

		$this->output->set_content_type('application/json')->set_output(json_encode($wisecoin));
	}	
}
