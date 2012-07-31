<?php
/**
 * RPG2KNET (http://www.rpg2knet.com/)
 *
 * @link      http://github.com/jonnu/rpg2knet for the source repository
 * @copyright Copyright (c) 1999-2012 Phrenzy (http://www.phrenzy.org/)
 * @author    jonnu (http://jonnu.eu/)
 * @license   TBD
 * @version   4.0
 */


/**
 * User_Model
 * 
 * @category  Account
 */
class User_Model extends CI_Model {

    public function __construct() {

        parent::__construct();

        // Load required libraries
        $this->load->library('email', array(
            'protocol'  => 'sendmail'
        ));
    }


    /**
     * create
     *
     * Generate a new account
     *
     * @return int
     */
    public function create($email = true) {
        
        $user_address = sprintf('%u', ip2long($this->input->ip_address()));
        $user_confirm = hash('SHA256', Authentication::seed(64) . $this->input->ip_address());

        // Create New User
        $user_utcdate = new DateTime(null, new DateTimeZone('UTC'));
        $user_payload = array(
            'user_email'         => $this->form_validation->value('user_email'),
            'user_hash_password' => Authentication::encrypt($this->form_validation->value('user_password')),
            //'user_hash_confirm'  => $user_confirm,
            'user_name'          => $this->form_validation->value('user_name'),
            'user_ip_created'    => $user_address,
            'user_date_created'  => $user_utcdate->format('Y-m-d H:i:s')

        );
        
        $this->db->insert('user', $user_payload);
        $user_id = $this->db->insert_id();

        // Create an affirm payload.
        $security_payload = array(
            'security_user_id' => $user_id,
            'security_type'    => 'AFFIRM',
            'security_user_hash' => $user_confirm,
            'security_user_address' => $user_address,
            'security_date_expires' => $user_utcdate->add(new DateInterval('P1W'))
        );

        $this->db->insert('security', $security_payload);

        // Build an email
        
        $msg = 'confirm it...';
        $msg.= "\n";
        $msg.= "http://rpg2knet/account/register/confirm/" . $user_confirm;

        $this->email->to($this->form_validation->value('user_email'));
        $this->email->from('no-reply@rpg2knet.com', 'RPG2KNET');
        $this->email->subject('RPG2KNET ~ Account Registration');
        $this->email->message($msg);
        $this->email->send();

        //echo $this->email->print_debugger();

        //echo anchor('account/register/confirm');

        //exit;

        return $user_id;
    }


    public function confirm($hash) {
        
        $date_confirm = new DateTime(null, new DateTimeZone('UTC'));

        $this->db->select('u.*');
        $this->db->from('security s');
        $this->db->join('user u', 'u.user_id = s.security_user_id', 'inner');
        $this->db->where('s.security_user_hash', $hash);
        $this->db->where('s.security_type', 'AFFIRM');
        $security_result = $this->db->get();

        if ($security_result->num_rows() !== 1) {
            die('bad');
        }

        $data = $security_result->row();

        //$this->db->set('user_hash_confirm', null);
        
        // Affirm this user
        $this->db->set('user_date_confirmed', $date_confirm->format('Y-m-d H:i:s'));
        $this->db->where('user_id', $data->user_id);
        $this->db->update('user');

        if ($this->db->affected_rows() !== 1) {
            return false;
        }

        // Clean up security row.
        $this->db->from('security');
        $this->db->where('security_user_id', $data->user_id);
        $this->db->where('security_type', 'AFFIRM');
        $this->db->delete();

        // Send welcome to rpg2knet email.
        $this->email->to($data->user_email);
        $this->email->from('no-reply@rpg2knet.com', 'RPG2KNET');
        $this->email->subject('RPG2KNET ~ Welcome!');
        $this->email->message('confirmed, welcome 2 da site');
        $this->email->send();

        return true;
    }


    /**
     * update
     *
     * Update an account
     */
    public function update($user_id) {

    }


    /**
     * delete
     *
     * Delete an account
     */
    public function delete($user_id) {

    }



    public function getById($id) {

        if (!is_numeric($id) || $id === 0) {
            throw new User_Exception('Must be an integer greater than zero');
        }

        $this->db->select('u.*');
        $this->db->from('user u');
        $this->db->where('u.user_id', (int)$id);
        
        $this->db->group_by('u.user_id');
        $user_result = $this->db->get();

        if ($user_result->num_rows() !== 1) {
            return new User_Guest;
        }

        // work out type of user by db record...
        // for now, just return an auth'd user.
        // this seems sort of...wrong to do.
        // perhaps the base user should not be abstract.
        $user = $user_result->first_row('User_Authenticated');

        return $user;
    }


    public function getByIdTemp($id, $class = 'User_Authenticated') {

        if (!is_numeric($id)) {
            die('must be numeric');
        }

        $this->db->select('u.*');
        $this->db->from('user u');
        $this->db->where('u.user_id', $id);
        $user_result = $this->db->get();

        if ($user_result->num_rows() !== 1) {
            return new User_Guest;
        }

        // work out type of user by db record...
        // for now, just return an auth'd user.
        // this seems sort of...wrong to do.
        // perhaps the base user should not be abstract.
        $user = $user_result->first_row($class);//0, 'User_Authenticated');

        return $user;
        //return new $user_result->
    }


    /**
     * getByCredentials
     *
     * Pull user data from the database based upon the provided
     * credentials.
     * 
     * @param mixed $identifier
     * @param string $password
     * @param string $class
     * @param array $clauses
     * @param bool $password_encrypted
     * @param string $identifying_field
     *
     * @return User
     */
    public function getByCredentials($identifier, $password, $class = 'User_Authenticated', $clauses = array(), $password_encrypted = false, $identifying_field = 'user_email') {

        if ($class == 'User_Guest') {
            return new User_Guest;
        }
        
        $this->db->select('u.*');
        $this->db->from('user u');

        /*
        
        @todo Roll in permissions?
        
        $this->db->select('g.group_id as group_id');
        $this->db->select('g.group_name as group_name');
        $this->db->select('group_concat(p.permission_key) as group_permissions');

        $this->db->join('user_link ul', 'ul.link_user_id = u.user_id', 'left');
        $this->db->join('user_group g', 'g.group_id = ul.link_group_id', 'left');
        $this->db->join('permission_group gp', 'gp.link_group_id = g.group_id', 'left');
        $this->db->join('permission p', 'p.permission_id = gp.link_permission_id', 'left');
        */

        $this->db->where($identifying_field, $identifier);        
        $this->db->group_by('u.user_id');
        $this->db->limit(1);

        // Process additional clauses.
        foreach($clauses as $clause_field => $clause_value) {
            $this->db->where($clause_field, $clause_value);
        }
        
        $user_result = $this->db->get();

        // User did not exist (bad identifier)
        if ($user_result->num_rows() !== 1) {
            return new User_Guest;
        }

        $crypt_password = $user_result->row()->user_hash_password;

        // User gave an incorrect password... doesn't match the hash
        if ($crypt_password !== crypt($password, $crypt_password)) {
            return new User_Guest;
        }

        // Return the User object
        return $user_result->first_row($class);
    }


    /**
     * 
     * @param string $class
     * 
     * @return mixed
     */
    public function getBySession($class) {

        if ($class == 'User_Guest') {
            return new User_Guest;
        }

        $user = new $class;
        foreach ($this->session->get('auth/' . $class::key()) as $key => $data) {
            $user->$key = $data;
        }

        //$user->init();

        return $user;
    }


    public function validateUniqueEmail($email) {
        
        // gmail accounts allow for periods 'n' plusses.
        //if (preg_match('/@g(oogle)?mail\./i', $email)) {
            
            // gmails have a nasty habit of being hard to catch.

        //}
        

        //exit;

        $this->db->select('count(user.user_id) as user_count');
        $this->db->from('user');
        $this->db->where('user.user_email', $email);
        $account_result = $this->db->get();
        
        if((int)$account_result->row('user_count') !== 0) {
            $this->form_validation->set_message('module_callback', 'Your %s has been registered before. ' . anchor('account/recovery', 'Have you lost your password?'));
            return false;
        }
        
        return true;
    }


    public function setPulseTime($user_id) {

        $time = new DateTime(null, new DateTimeZone('UTC'));

        $this->db->set('user_date_pulsed', $time->format('Y-m-d H:i:s'));
        $this->db->where('user_id', $user_id);
        $this->db->update('user');
    }


}