<?php

//This is the model, which inserts data received from the controller (Registration.php) into the database and manages database logic
class Registration_model extends CI_Model{
    
    public function __construct() {
        $this->load->database();
    }
    
    //Here, we are escaping the data before inserting it into the database. CodeIgniter recommends doing this for security.
    //The reason why it should work is because the data is going to be translated to a SQL string, which could be dangerous. 
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

