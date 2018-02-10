<?php

/**
 * Created by PhpStorm.
 * User: Nick_2
 * Date: 4/10/2017
 * Time: 7:30 PM
 */

//Note on Security (to be deleted): In index.php, define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
//needs to be changed to 'production' once SFSU Marketplace is ready for release.
//This will prevent harmful information from being printed through PHP's native error message
//system. 
//validate_input needs to be called only right before output, in order to prevent slashes being removed from passwords.
//If it's called right after input, this may lead to data corruption and
//difficulties with comparing data. 
//htmlspecialchars() is automatically called as an intermediate function of set_value in registration_view. 
//$config['csrf_protection'] = TRUE; needs to be set in the config to protect against
//cross-site request forgery. 

class Registration extends CI_Controller {

    public function __construct() {
        parent::__construct();
        /**
         * and form helper
         * Url helper is needed for bootstrap.
         */
        $this->load->model('Registration_model');
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->model('Login_model');
    }
    
    //Needs to be called before outputting any data to avoid executing XSS scripts
    public function validate_input($data) {
        $data = htmlspecialchars($data);
        $data = stripslashes($data);
        $data = $this->security->xss_clean($data);
        return $data;
    }

    public function index() {
        //Codeigniter preprocesses input for security
        $this->load->helper(array('form'));
        $this->load->library('form_validation');
        $this->load->database(); //Required to use is_unique
        $TOS = 'FALSE'; //false by default
                
        //Email rules
        $this->form_validation->set_rules(
                'email', 'Email', 'required|trim|valid_email|is_unique[Users.email]', array(
            'is_unique' => 'This %s already exists.'
                )
        );
        //Default error messages first, SFSU address message second
        if ($this->form_validation->run() == TRUE) {
            //email_check('email')
            $this->form_validation->set_rules('email', 'Email', 'callback_email_format');
        }
        
        //Username rules
        $this->form_validation->set_rules(
                'username', 'Username', 'required|min_length[5]|max_length[12]|is_unique[Users.username]', array(
                    'is_unique' => 'This %s already exists.'
                )
        );

        $this->form_validation->set_rules('firstname', 'First Name', 'required|trim');
        $this->form_validation->set_rules('lastname', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[8]');
        $this->form_validation->set_rules('confirmpassword', 'Confirm Password', 'required|trim|matches[password]');
        $this->form_validation->set_rules('accept_terms', '', 'callback_accept_terms');
        
        if ($this->form_validation->run() == FALSE) {
            //If the email (and all other variables) had an incorrect format, do the following: 
            $title = array(
                'title' => 'Registration');
            $this->load->view('header', $title);
            $this->load->view('registration_view');
            $this->load->view('footer');
        } else {
            //Otherwise, load a 'registration succeeded' page
            
            $username = $this->input->post('username');
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $password = $this->Login_model->encrypt($this->input->post('password'));
            $email = $this->input->post('email');
            $TOS = 'TRUE'; //This will only be executed if the TOS checkbox was checked
                            
            $data = array(
                'username' => $username,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'password' => $password,
                'email' => $email,
                'tos' => $TOS
            );

            //Redirect to Home after 5 seconds if db_submit was successful
            if ($this->Registration_model->db_submit($data)) {
                $title = array(
                    'title' => 'Registration success');
                $this->load->view('header', $title);
                $this->load->view('registration_success');
                $this->load->view('footer');
                header("refresh:5;url=" . base_url() . "index.php/Home");
            }         
        }
    }

    //Pass 'email' as $str through the callback
    public function email_format($str) {

        //Checks if the given pattern is found in 'email' 
        if (stristr($str, '@mail.sfsu.edu') !== false || stristr($str, '@sfsu.edu') !== false) {
            return true;
        }

        $this->form_validation->set_message('email_format', 'Please provide a SFSU email address');
        return false;
    }
    
    function accept_terms($str) {
        if (isset($str)) {
            return true;
        }

        $this->form_validation->set_message('accept_terms', 'Please read and accept the terms and conditions.');
        return false;
    }

}
