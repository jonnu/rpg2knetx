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
 * User
 *
 * Base user class
 *
 * @abstract
 * @category  Account
 */
abstract class User {

    /**
     * @var RPG_Session
     */
    protected $session;

    /**
     * @var string
     */
    static protected $key;

    /**
     * @var array
     */
    protected $permissions = array();


    /**
     * User Constructor
     */
    public function __construct() {
        $this->session = &get_instance()->session;
    }


    /**
     * key
     *
     * Returns the key associated with the class
     *
     * @static
     * @return string
     */
    final static public function key() {
        return static::$key;
    }

    
    /**
     * authenticated
     *
     * Is the current user authenticated?
     *
     * @abstract
     * @return boolean
     */
    abstract public function authenticated();
    

    /**
     * init
     *
     * Initialize a freshly-created User object.  An authentication
     * flag is present in order to discern between initialization
     * from the user (i.e. a login) and system (i.e. persistence)
     * 
     * @param boolean $authenticated
     * 
     * @return void
     */
    public function init($authenticated = false) {
        
        // Determine session key.       
        $key = 'auth/' . static::key();

        // Create session data.
        if (!$this->session->get($key)) {

            $data = array_merge($this->data(), array(
                'login_time' => time(),
                'ip_current' => get_instance()->input->ip_address(),
                'auth_class' => get_class($this),
                'auth_fresh' => $authenticated
            ));

            $this->session->set($key, $data);
        }
    }


    /**
     * data
     *
     * Obtain variables attached to this User.  These can be filtered
     * by specifying a key to this function (e.g. 'user')
     *
     * @param string $key
     *
     * @return array
     */
    protected function data($key = 'user') {

        $vars = get_object_vars($this);

        if (is_null($key) || empty($key)) {
            return $vars;
        }

        // Filter the data if there is a key present
        $data = array_filter(array_keys($vars), function($item) use ($key) {
            return substr($item, 0, 5) == rtrim($key, '_') . '_';
        });

        return array_intersect_key($vars, array_flip($data));
    }

    
    /**
     * id
     *
     * Return the User's numeric database ID
     * 
     * @return int
     */
    public function id() {
        return (int)$this->user_id;
    }
    

    /**
     * name
     *
     * Return the User's name
     * 
     * @return string
     */
    public function name() {
        return $this->user_name;
    }
    

    /**
     * email
     *
     * Return the User's email address
     * 
     * @return string
     */
    public function email() {
        return $this->user_email;
    }


    /**
     * login
     *
     * Create a User object within the session, and
     * set any persistence required
     * 
     * @param string $identifier
     * @param string $password
     * @param boolean $persistent
     * 
     * @return mixed
     */
    static public function login($identifier, $password, $persistent = false) {

        $class = get_called_class();
        $user  = get_instance()->authentication->login($identifier, $password, $persistent, $class);

        // Was authentication successful?
        if (!$user instanceof $class) {
            return false;
        }

        return $user;
    }


    /**
     * logout
     *
     * Destroy the User object within the session, and 
     * also remove any persistence data (cookies etc)
     *
     * @static
     * @return boolean
     */
    static public function logout() {
        return get_instance()->authentication->logout(get_called_class());
    }


    /**
     * can
     *
     * Used to ascertain if the current user has the 
     * access rights to perform a certain action.
     * 
     * @param string $permission
     * @return boolean
     */
    public function can($permission = null) {
        
        if(is_null($this->permissions)) {
            
            // No permissions found... might want to throw an error here!
            if (!isset($this->group_permissions) && !isset($this->user_permissions)) {
                $this->permissions = array();
                return false;
            }
            
            $group_permissions = explode(',', $this->group_permissions);
            $this->permissions = array_combine(array_values($group_permissions), array_fill(0, count($group_permissions), true));
        }
        
        return isset($this->permissions[$permission]) && $this->permissions[$permission];
    }


    /**
     * cannot
     *
     * Opposite of 'can'. Used to keep code clean and
     * to aid in semantics.
     *
     * @see User::can
     * 
     * @param string $permission
     * @return boolean
     */
    public function cannot($permission = null) {
        return !$this->can($permission);
    }
}