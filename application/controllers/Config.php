<?php
class Config extends CI_Controller {

	public function index(){
		$this->user->check($this->input->get_request_header('Authorization'));
		$config = $this->db->get("config")->row();
		$config->raised = $this->db->select_sum('wisecoin')->get('wisecoin_address')->row()->wisecoin;
		$config->start = $this->db->select('cota')->order_by('id',"desc")->limit(1)->get('wisecoin_chart')->row()->cota;
		$this->output->set_content_type('application/json')->set_output(json_encode($config));
	}

	
}
