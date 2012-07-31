<?php

class Register extends CI_Controller {

    public function __construct() {
        
        parent::__construct();

        $this->load->model('user_model', 'model');
    }


    /**
     * index
     * 
     */
    public function index() {

        if ($this->user->authenticated()) {

            // @TODO.
            die('already registered');
        }

        $this->load->library('form_validation');

        if ($this->form_validation->run('account-register')) {

            // Insert the user.
            $user_id = $this->model->create(true);

            // Log user in?

            // Forward user to TY page.
            redirect('account/register/confirm');
        }

        $this->load->view('account/register.view.php');
    }


    /**
     * confirm
     *
     * Confirm an email address is valid by sending back the hash
     * that is generated upon user registration.
     * 
     * @param string $confirm_hash
     */
    public function confirm($confirm_hash = null) {

        if (is_null($confirm_hash)) {

            echo 'You have been emailed!  You should confirm your account';

            return;
        }

        // Attempt to confirm.
        if (!$this->model->confirm($confirm_hash)) {
            die('could not confirm');
        }

        // success!

        return redirect('account');
    }


}
