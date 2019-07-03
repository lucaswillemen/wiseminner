<?php
class User extends CI_Model {

    public function check($token)
    {   
        $user = $this->db->select('*')->where("token", $token)->get('users')->row();
        if (!$user && $this->input->method()!="options") {      
            $this->output->set_content_type('application/json')->set_status_header(401);
            exit();
        }
        if ($this->input->method()=="options") {
            $this->output->set_content_type('application/json')->set_status_header(200);
            exit();
        }
        unset($user->senha);
        return $user;
    }
}

