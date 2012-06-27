<?php

class Main extends CI_Controller {
    
	public function index() {
		
		$this->load->view('home/index.view.php');
    }
}