<?php
class Auth extends CI_Controller {
	public function register()

	{

		if (!$this->input->post('consierge')) {			
			$consierge = (object)['id' => 0];
		}else{
			$consierge = $this->db->where('nome', $this->input->post('consierge'))->get('users')->row();
			if (!$consierge) {			
				$response = ['error'=>'consierge'];
				return $this->output->set_content_type('application/json')->set_status_header(403)->set_output(json_encode($response));
				exit();
			}
		}
		if ($this->db->where('email', $this->input->post('email'))->get('users')->row()) {			
			$response = ['error'=>'email'];
			return $this->output->set_content_type('application/json')->set_status_header(400)->set_output(json_encode($consierge));
			exit();
		}
		if ($this->db->where('nome', $this->input->post('nome'))->get('users')->row()) {			
			$response = ['error'=>'nome'];
			return $this->output->set_content_type('application/json')->set_status_header(402)->set_output(json_encode($response));
			exit();
		}

		$user = [
			'nome' => $this->input->post('nome'),
			'email' => $this->input->post('email'),
			'senha' => password_hash($this->input->post ( "senha" ), PASSWORD_BCRYPT, ['cost' => 7]),
			'token' => hash('ripemd160', $this->input->post('email')),
			'pai_id' => $consierge->id
		];

		$this->db->insert('users', $user);
		unset($user['senha']);

		$this->output->set_content_type('application/json')->set_output(json_encode($user));
	}
	public function referer()

	{

		$user = $this->user->check($this->input->get_request_header('Authorization'));
		if ($this->db->where('email', $this->input->post('email'))->get('users')->row()) {			
			$response = ['error'=>'email'];
			$this->output->set_content_type('application/json')->set_status_header(400)->set_output(json_encode($response));
			exit();
		}
		if ($this->db->where('nome', $this->input->post('nome'))->get('users')->row()) {			
			$response = ['error'=>'nome'];
			$this->output->set_content_type('application/json')->set_status_header(402)->set_output(json_encode($response));
			exit();
		}

		$user = [
			'nome' => $this->input->post('nome'),
			'email' => $this->input->post('email'),
			'senha' => password_hash($this->input->post ( "senha" ), PASSWORD_BCRYPT, ['cost' => 7]),
			'token' => hash('ripemd160', $this->input->post('email')),
			'pai_id' => $user->id
		];

		$this->db->insert('users', $user);
		unset($user['senha']);

		$this->output->set_content_type('application/json')->set_output(json_encode($user));
	}


	public function check(){
		$user = $this->user->check($this->input->get("token"));
		$this->output->set_content_type('application/json')->set_output(json_encode($user));
	}

	public function login(){
		$nome = $this->input->post('nome');
		$senha = $this->input->post('senha');

		$user = $this->db->select('*')->where('nome', $nome)->get('users')->row();

		if (!$user) {			
			$this->output->set_content_type('application/json')->set_status_header(400);
			exit();
		}
	    if (!password_verify($senha, $user->senha)) {
	      	$this->output->set_content_type('application/json')->set_status_header(401);
	      	exit();
	    }
		unset($user->senha, $user->id);
		unset($user->senha, $user->admin);
		$this->output->set_content_type('application/json')->set_output(json_encode($user));
	}
	public function facebook(){
		$token = $this->input->post('token');

        $fb = new \Facebook\Facebook([
		  	'app_id' => '2163128903949223',
		  	'app_secret' => 'cbf6647b123d6c429c58c6db013d2e57',
		  	'default_graph_version' => 'v2.10',
		]);
		try {
		 	 $response = $fb->get('/me?fields=name,email', $token);
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
	      	$this->output->set_content_type('application/json')->set_status_header(401);
	      	exit();
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
	      	$this->output->set_content_type('application/json')->set_status_header(401);
	      	exit();
		}

		$me = $response->getGraphUser();
		$email = $me->getField('email');
		$name = $me->getField('name');
		$fb_id = $me->getField('id');


		$user = $this->db->where('fb_id', $fb_id)->get('users')->row();	
		if ($user) {
			unset($user->senha);
			return $this->output->set_content_type('application/json')->set_output(json_encode($user));
			exit();
		}else{			
			$user = $this->db->where('email', $email)->get('users')->row();
			if ($user) {
				unset($user->senha);
				$this->db->where('email', $email)->set('fb_id', $fb_id)->update('users');
				return $this->output->set_content_type('application/json')->set_output(json_encode($user));
				exit();
			}else{
				$data = [
					'email' => $email,
					'fb_id' => $fb_id,
					'token' => hash('ripemd160', $this->input->post('email')),
				];
				$this->db->insert('users', $data);
				$user = $this->db->where('fb_id', $fb_id)->get('users')->row();	
				$user->senha = 1;

				return $this->output->set_content_type('application/json')->set_output(json_encode($user));
				exit();
			}
		}
	}
}
