<?php

class Account extends CI_Controller {

    public function __construct() {

        parent::__construct();

        $this->load->library('form_validation');
    }


    /**
     * index
     * 
     */
    public function index() {

        // Must be authenticated to see dashboard
        if (!$this->user->authenticated()) {
         redirect('account/login');
        }

        $this->load->view('account/dashboard.view.php');
    }


    /**
     * login
     *
     */
    public function login() {

        if ($this->user->authenticated()) {
            return redirect('account');
        }

        if ($this->form_validation->run('account-login')) {

            // Is this going to be a persistent login?
            $persistent = $this->form_validation->is_checked('user_persistent');

            // Attempt to authenticate a 'User'
            if (false !== User_Authenticated::login($this->form_validation->value('user_email'), $this->form_validation->value('user_password'), $persistent)) {
                $this->session->set_flashdata('core/message', sprintf('Welcome back, %s!', $this->user->name()));
                return redirect('account');
            }

            // Failed to authenticate - highlight fields
            $this->form_validation->add_error(null, 'user_email', true);
            $this->form_validation->add_error('Invalid authentication details', 'user_password');
        }

        $this->load->view('account/login.view.php');
    }


    /**
     * logout
     *
     *
     */
    public function logout() {

        $referer = $this->input->server('HTTP_REFERER');

        // If we have not been refered to this page locally, we
        // might want to check if the user actually does want this.
        // Idea is to stop CS <img src="lol.com/logout" />

        // @todo Make a proper 'view' for this.
        if (is_null($referer)) {
            //var_dump(parse_url($referer));
            echo 'ARE U SURE? ';
            echo anchor('account/logout', 'YEP');

            return;
        }

        // Perform Logout
        User_Authenticated::logout();
        return redirect('account');
    }


    /**
     * lost
     * 
     */
    public function lost() {

        if ($this->form_validation->run('account-lost')) {

            $email = $this->form_validation->value('user_email');

            $this->db->select('u.user_id');
            $this->db->from('user u');
            $this->db->where('u.user_email', $email);
            $user_result = $this->db->get();

            if ($user_result->num_rows() === 1) {

                $user_object = $user_result->row();

                // if the password is lost, destroy all persistent sessions
                $this->db->from('security');
                $this->db->where('security_user_id', $user_object->user_id);
                $this->db->where('security_type', 'COOKIE');
                $this->db->delete();

                // generate a hash
                $user_hash = hash('SHA256', Authentication::seed(64) . $email);

                // generate security thing.
                $security_utcdate = new DateTime(null, new DateTimeZone('UTC'));
                $security_payload = array(
                    'security_user_id' => $user_object->user_id,
                    'security_type' => 'LOSTPW',
                    'security_user_hash' => $user_hash,
                    'security_user_address' => sprintf('%s', ip2long($this->input->ip_address())),
                    'security_date_expires' => $security_utcdate->add(new DateInterval('P1D'))->format('Y-m-d H:i:s')
                );

                $this->db->insert('security', $security_payload);

                // email the user a link.
                $msg = 'So, you lost your password.  You can reset it here.';
                $msg.= "\n";
                $msg.= "http://rpg2knet/account/reset/" . $user_hash;

                $this->email->to($email);
                $this->email->from('no-reply@rpg2knet.com', 'RPG2KNET');
                $this->email->subject('RPG2KNET ~ Reset your password');
                $this->email->message($msg);
                $this->email->send();

                return redirect('account/reset');
            }

            // No such usr.
            $this->form_validation->add_error('No such email address', 'user_email');
        }

        $this->load->view('account/lost.view.php');
    }


    /**
     * reset
     * 
     */
    public function reset($hash = null) {

        if (is_null($hash)) {
            echo 'we have emailed you a reset token.  please click the link in yr email';
            exit;
        }

        // Look up user
        $this->db->select('*');
        $this->db->from('security s');
        $this->db->join('user u', 'u.user_id = s.security_user_id', 'inner');
        $this->db->where('s.security_type', 'LOSTPW');
        $this->db->where('s.security_user_hash', $hash);

        $security_result = $this->db->get();
        if ($security_result->num_rows() !== 1) {
            die('invalid hash: ' . $hash);
        }

        $security_data = $security_result->row();

        if ($this->form_validation->run('account-reset')) {

            // Reset dat pw.
            $new_password_plain = $this->form_validation->value('user_password');
            $new_password_hash  = Authentication::encrypt($new_password_plain);

            // Update user pass
            $this->db->set('user_hash_password', $new_password_hash);
            $this->db->where('user_id', $security_data->security_user_id);
            $this->db->update('user');

            // Clean up security.
            $this->db->from('security');
            $this->db->where('security_type', 'LOSTPW');
            $this->db->where('security_user_id', $security_data->security_user_id);
            $this->db->delete();

            // Email
            // email the user a link.
            $msg = 'ur password was reset successfully!';
            $msg.= "\n";
            $msg.= "be more careful with it next time, geez...";

            $this->email->to($security_data->user_email);
            $this->email->from('no-reply@rpg2knet.com', 'RPG2KNET');
            $this->email->subject('RPG2KNET ~ Password has been reset');
            $this->email->message($msg);
            $this->email->send();

            // Set flash msg.
            $this->session->set_flashdata('message', 'Password was reset. Please log in.');

            // Redirect
            return redirect('account/login');
        }

        $this->load->view('account/reset.view.php');
    }


}
