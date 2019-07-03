<?php
class Consierge extends CI_Controller {

	public function index(){
		$user = $this->user->check($this->input->get_request_header('Authorization'));
		$saldo = [
			'wsc' => $this->db->select_sum('wsc')->where('user_id', $user->id)->where('descricao', 'consierge')->get('wisecoin_extrato')->row()->wsc,
			'btc' => $this->db->select_sum('btc')->where('user_id', $user->id)->where('descricao', 'consierge')->get('extrato')->row()->btc,
			'users' => $this->db->where('pai_id', $user->id)->count_all_results('users'),
			'bought' => $this->db->select_sum('wisecoin')->where('pai_id', $user->id)->from('users')->join('wisecoin_address', 'wisecoin_address.user_id=users.id', 'left')->get()->row()->wisecoin
		];
		$this->output->set_content_type('application/json')->set_output(json_encode($saldo));
	}

	
}
