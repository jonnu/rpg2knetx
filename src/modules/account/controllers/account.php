<?php

class Account extends CI_Controller {

//	public function __construct() {
//		parent::__construct();
//	}

	public function index() {

		$this->output->enable_profiler(1);
		//Keep in mind though, that the session (or related cookie) should only ever be used for identification. It should not be used for authentication.
		//var_dump(User_Authenticated::login('example@rpg2knet.com', 'test'));

		//var_dump($this->user->isAuthenticated());
		//if (false !== $this->session->get('auth/user/user_id')) {
		if ($this->user->isAuthenticated()) {
			echo 'We are logged in!<br />';
			echo 'Instead of polling the db, we can store any data in the session<br /><br />';
		}
		else {
			// redirect to login page.
			echo 'We are not logged in :(<br />';
			echo 'Sorry!<br /><br />';
		}

		$this->load->model('user_model', 'user_model');
		$this->user_model->validateUniqueEmail('jo.ntc.e@gmail.com');

		echo 'User Object:';
		var_dump($this->user);
		
		echo 'Cookie:';
		var_dump($_COOKIE);

		echo 'Session (' . session_name() . ': ' . session_id() . '):';
		var_dump($_SESSION);

		echo '<ul>';
		echo '<li>' . anchor('account/c/1', 'Create PERSISTENT...') . '</li>';
		echo '<li>' . anchor('account/c/0', 'Create normal...') . '</li>';
		echo '<li>' . anchor('account/b', 'Destroy Session only...') . '</li>';
		echo '<li>' . anchor('account/d', 'Destroy COOKIE only...') . '</li>';
		echo '<li>' . anchor('account/e', 'Destroy EVERYTHING.') . '</li>';
		echo '</ul>';

		return;

		/*
		$id = 2;

		$this->load->model('user_model', 'usr');

		$user = $this->usr->getById($id);

		
		var_dump($user);

		var_dump($this->user);

		var_dump($this->user == $user);
		*/
		$data = 'test';
		$salt = substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22);
		$hash = crypt($data, '$2a$12$' . $salt . '$');
		$base = base64_encode($hash);

		echo 'Data: ' . $data . '<br />';
		echo 'Salt: ' . $salt . '<br />';
		echo 'Hash: ' . $hash . ' (' . strlen($hash) . ')<br />';
		echo 'Base: ' . $base . ' (' . strlen($base) . ')<br /><br />';

		var_dump(crypt($data, $hash) === $hash);

		echo Authentication::encrypt($data);

	}

	/**
	 * login
	 *
	 *
	 */
	public function login() {

		if($this->form_validation->run('account-login-form')) {
			
			if(false !== User_Authenticated::login($this->form_validation->value('account_email'), $this->form_validation->value('account_password'), true)) {
				$this->session->set_flashdata('core/message', sprintf('Welcome back, %s!', $this->user->name()));
				return redirect('account');
			}

			$this->form_validation->add_error(null, 'account_email', true);
			$this->form_validation->add_error('bad user or pass', 'account_password');
		}
		
		//$this->load->view('common/login.view.php');
		// display login form?
	}


	/**
	 * logout
	 *
	 *
	 */
	public function logout() {

	}




	public function b() {

		//get_instance()->db->truncate('security');
		$this->session->destroy();

		redirect('account');
	}

	public function c($persist) {
		User_Authenticated::login('test@rpg2knet.com', 'test', $persist == 1);
		redirect('account');
	}

	public function d() {
		$this->input->set_cookie(Authentication::AUTH_PERSISTENCE_COOKIE, '', -1);
		redirect('account');
	}

	public function e() {
		
		// Delete from db (wipe persistence).
		$this->db->delete('security', array('security_user_id' => $this->user->id()));

		// Destroy sess.
		$this->session->destroy();

		// Delete data cookie
		$this->input->set_cookie(Authentication::AUTH_PERSISTENCE_COOKIE, '', -1);

		redirect('account');

	}
}