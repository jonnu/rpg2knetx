<?php

/**
 * User_Model
 *
 *
 */
class User_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }


    /**
     * create
     *
     * Generate a new account
     */
    public function create() {
        
        // Create New User
        $user_utcdate = new DateTime(null, 'UTC');
        $user_payload = array(
            'user_email'        => $this->form_validation->value('user_email'),
            'user_password'     => Authentication::encrypt($this->form_validation->value('user_password')),
            'user_name'         => $this->form_validation->value('user_name'),
            'user_ip'           => sprintf('%u', long2ip($this->input->ip_address())),
            'user_date_created' => $user_utcdate->format('Y-m-d H:i:s'),
            'user_date_pulsed'  => null
        );
        
        $this->db->insert('user', $user_payload);
        $user_id = $this->db->insert_id();

        return $user_id;
    }


    public function getById($id) {

        if (!is_numeric($id)) {
            die('must be numeric');
        }

        $this->db->select('u.*');
        $this->db->from('user u');
        $this->db->where('u.user_id', (int)$id);
        
        $this->db->group_by('u.user_id');
        $user_result = $this->db->get();

        // work out type of user by db record...
        // for now, just return an auth'd user.
        // this seems sort of...wrong to do.
        // perhaps the base user should not be abstract.
        $user = $user_result->row(0, 'User_Authenticated');
        return $user;
        //return new $user_result->
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
        $this->db->select('g.group_id as group_id');
        $this->db->select('g.group_name as group_name');
        $this->db->select('group_concat(p.permission_key) as group_permissions');

        $this->db->join('user_link ul', 'ul.link_user_id = u.user_id', 'left');
        $this->db->join('user_group g', 'g.group_id = ul.link_group_id', 'left');
        $this->db->join('permission_group gp', 'gp.link_group_id = g.group_id', 'left');
        $this->db->join('permission p', 'p.permission_id = gp.link_permission_id', 'left');
        */

        //if (!$password_encrypted) {
        //  $password = Authentication::encrypt($password);
        //}

        //echo $password_encrypted === true ? 't' : 'f' . '<br />';
        //echo $password . '<br />';

        $this->db->where($identifying_field, $identifier);
        //$this->db->where('u.user_password', $password);
        
        $this->db->group_by('u.user_id');
        $this->db->limit(1);



        // Process additional clauses.
        foreach($clauses as $clause_field => $clause_value) {
            $this->db->where($clause_field, $clause_value);
        }
        
        $user_result = $this->db->get();
        //var_dump($user_result->num_rows());
        if ($user_result->num_rows() !== 1) {
            return new User_Guest;
        }

        //echo $user_result->row('user_password') . '<br />';
        //echo crypt($password, $user_result->row('user_password')) . '<br />';
        
        // Incorrect password... doesn't match the pw/hash.
        if ($user_result->row('user_password') !== crypt($password, $user_result->row('user_password'))) {
            return new User_Guest;
        }

        return $user_result->row(0, $class);
    }


    public function getBySession($class) {

        if ($class == 'User_Guest') {
            return new User_Guest;
        }

        $user = new $class;
        foreach ($this->session->get('auth/' . $class::key()) as $key => $data) {
            $user->$key = $data;
        }

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