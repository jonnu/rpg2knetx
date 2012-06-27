<?php

class Register extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
	}

	public function index() {

		if ($this->user->isAuthenticated()) {

			// @TODO.
			//die('already registered');
		}

		$this->load->library('form_validation');

		if ($this->form_validation->run('account_register')) {
			exit;
		}

		echo 'satan';
	}
}