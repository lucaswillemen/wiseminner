<?php
use Mailgun\Mailgun;
class Bitcoin extends CI_Controller {

	function create(){
		$user = $this->user->check($this->input->get_request_header('Authorization'));
        $address = $this->core->address();
        $insert = [
        	"user_id" => $user->id,
        	"address" => $address,
        	"type" => "coin"
        ];
        $this->db->insert("address", $insert);
		$this->output->set_content_type('application/json')->set_output(json_encode($address));
	}	
	function hook(){

		$data = json_decode(file_get_contents("php://input"));

		if ($this->input->get("secret") != '093c8712cbc180b7a76e451d473952152ecc43f0') {
			$this->output->set_content_type('application/json')->set_status_header(403);
			exit();
		}


		if ($data->confirmations == 0) {

			//Verificar se a carteira possui saldo, em caso positivo, negar o pagamento
			$id = $this->db->where("address", $data->address)->where('unconfirmed', 0)->get("address")->row();
			if (!$id) {
				exit();
			}else{
				//se a carteira nao possuir saldo, informar o saldo nao confirmado
				$this->db->where("address", $data->address)->set("unconfirmed", $data->amount)->update("address");
			}

			//Carregar os dados de configuracao
			$config = $this->db->get("config")->row();
			$valor = $data->amount;

			//Encontrar o valor da Wisecoin
			$wisecoin = $valor*$config->cota/$config->wise_cota;
			
			//Carregar dados do usuário
			$user = $this->db->where("id", $id->user_id)->get("users")->row();


			//Verificar se existe o corretor
			$consierge = $this->db->where('id', $user->pai_id)->where('corretor', 1)->get('users')->row();
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
			
			//Efetuar o pagamento WSC
			$this->db->where("user_id", $id->user_id)->set("wisecoin", "wisecoin+".$wisecoin, FALSE)->update("wisecoin_address");
			
			//Injetar o extrato
			$extrato_wise = [
				"user_id" => $user->id,
				"wsc" => $wisecoin,
				"cota" => $config->wise_cota,
				"descricao" => "buy"
			];
			$this->db->insert("wisecoin_extrato", $extrato_wise);

			//Atualizar o valor da wisecoin
			$wisecoin = $this->db->select_sum('wisecoin')->get("wisecoin_address")->row();
			$tomorrow = $wisecoin->wisecoin/$config->wise_function+0.01;
			$this->db->set('wise_cota', $tomorrow)->update('config');

			//Enviar emails
			$mg = Mailgun::create('key-4f5c5c70ab45c128df806e89628be784');
			$mg->messages()->send('wiseminner.com', [
			  'from'    => 'postmaster@wiseminner.com',
			  'to'      => $user->email.',lucaswillemen@gmail.com',
			  'subject' => $data->amount.' bitcoins deposited!',
			  'text'    => 'Ready, we identified the '.$data->amount.' btc deposit in your account, now just wait to confirm. :D'
			]);
		
		}		
		if ($data->confirmations == 1) {
			$id = $this->db->where("address", $data->address)->get("address")->row();	
			$this->db->where("address", $data->address)->set("btc", $data->amount)->set("unconfirmed", 0)->update("address");
			$this->extratar->inserir($id->user_id, $data->amount, 0, 0, 'deposit');
		}
		print_r($data);

	}





}
