<?php
class Home extends CI_Controller {

	public function index(){
		$user = $this->user->check($this->input->get_request_header('Authorization'));
		$wisecoin = $this->db->select('address, wisecoin')->where('user_id', $user->id)->get('wisecoin_address')->row();
		if (!$wisecoin) {			
		    $data = [
		    	'user_id' => $user->id,
		    	'address' => $address = $this->core->address(),
		    	'wisecoin' => 0
		    ];
		    $this->db->insert('wisecoin_address', $data);
		    unset($data['user_id']);
		    $user->wisecoin = $data['wisecoin'];
		}else{
			$user->wisecoin = $wisecoin->wisecoin;
		}
		


		$user->transactions = $this->db->select('cota, data, wsc, descricao')->where('user_id', $user->id)->limit(5)->order_by('id', 'desc')->get('wisecoin_extrato')->result();
		$address = $this->db->select('address, btc, unconfirmed')->where('btc', 0)->where('type', 'coin')->where('unconfirmed', 0)->where('user_id', $user->id)->get('address')->row();
		if (!$address) {
			$data = [
		    	'user_id' => $user->id,
		    	'address' => $address = $this->core->address(),
		    	"type" => "coin"
		    ];
		    $this->db->insert('address', $data);
		    $user->address = $data['address'];
		}else{
			$user->address = $address->address;
		}
        unset($user->id);
        unset($user->nivel);
        unset($user->pai_id);
        unset($user->rend);
        unset($user->pts);
        unset($user->cadastro);
        unset($user->btc);

        $user->price = 	
		$config = $this->db->get("config")->row()->wise_cota;
		$this->output->set_content_type('application/json')->set_output(json_encode($user));
	}

	
}
