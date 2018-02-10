<?php

/**
 * Created by PhpStorm.
 * User: Nick_2
 * Date: 4/10/2017
 * Time: 7:30 PM
 */

class Registration extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library(array('session', 'form_validation', 'email'));
        $this->load->database();
        $this->load->model('Registration_model');
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

        $this->form_validation->set_rules('firstname', 'First Name', 'required|trim|alpha');
        
        //Allow dashes, a-z and A-Z
        $this->form_validation->set_rules('lastname', 'Last Name', 'required|trim|regex_match[/^([-a-z])+$/i]', array(
            'regex_match' => 'Last name must consist of alphabetic characters and dashes'
        ));
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
