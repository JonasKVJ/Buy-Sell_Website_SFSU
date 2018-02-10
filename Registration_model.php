<?php

class Registration_model extends CI_Model{
    
    public function __construct() {
        $this->load->database();
    }
    
    public function db_submit($data){
        //var_dump($data["firstname"]); Debugging
        //Password is not escaped, since it is not a string and should not be modified
        //escape() adds single quotes around string
        $data["username"] = $this->db->escape($data["username"]); //Tested and works
        $data["firstname"] = $this->db->escape($data["firstname"]);
        $data["lastname"] = $this->db->escape($data["lastname"]);
        $data["email"] = $this->db->escape($data["email"]);
        $data["tos"] = $this->db->escape($data["tos"]);
        //var_dump($data["tos"]); //Debugging
        $this->db->insert("Users", $data);
        return true;
    }
}

