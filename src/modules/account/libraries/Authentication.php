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
 * Authentication
 *
 * 
 *
 * @abstract
 * @category  Account
 */
class Authentication {

    /**
     * Time to live for persistent sessions.
     * 
     * @see DateInterval
     */
    const AUTH_PERSISTENCE_INTERVAL = 'P7D';

    /**
     * Name of the cookie used for persistent sessions.
     */
    const AUTH_PERSISTENCE_COOKIE = 'rpg2knetdata';

    /**
     * @var Db
     */
    private $db;

    /**
     * @var User_Model
     */
    private $model;

    /**
     * @var RPG_Session
     */
    private $session;

    /**
     * @var CI_Input
     */
    private $input;


    /**
     * Authentication Constructor
     */
    public function __construct() {

        // @todo rethink this requirement.
        if (!extension_loaded('openssl')) {
            throw new Exception('needs openssl');
        }
        
        // Grab framework
        $CI = &get_instance();
        $CI->load->model('user_model', 'umod');

        // Set services
        $this->db      = &$CI->db;
        $this->model   = &$CI->umod;
        $this->input   = &$CI->input;
        $this->session = &$CI->session;
        
        // Instantiate existing sessions
        $this->pulse();
    }


    /**
     * login
     *
     * Log a user into the system using supplied credentials
     * 
     * @param string  $identifier 
     * @param string  $password 
     * @param boolean $persistent
     * @param string  $class
     * @param array   $clauses 
     * 
     * @return User
     */
    public function login($identifier, $password, $persistent = false, $class = 'User_Authenticated', $clauses = array()) {

        // Check that we are not already identified for this class
        if (class_exists($class) && false !== $this->session->get('auth/' . $class::key())) {
            return $this->model->getBySession($class);
        }

        // Attempt to log this user in with the supplied credentials.
        $user = $this->model->getByCredentials($identifier, $password, $class, $clauses);
        if ($user instanceof User_Guest) {
            return $user;
        }

        // Regenerate the session ID.
        $this->session->regenerate_id();

        // @TODO Refactor this.
        $user->init(true);
        $this->pulse();

        // Save this login across sessions
        if ($persistent) {
            $this->persist();
        }

        return $user;
    }
    

    /**
     * logout
     *
     * Destroys a user's session, logging them out from any areas
     * of the system that require authentication.
     *
     * @param string $auth_class
     * @return bool
     */
    public function logout($auth_class = 'User_Authenticated') {

        if (!class_exists($auth_class)) {
            return false;
        }
        
        // Check to see if the user session is present.
        if (!$this->session->get('auth/' . $auth_class::key())) {
            return false;
        }
        
        // If we have an object attached, change it to a guest.     
        if (isset(get_instance()->{$auth_class::key()})) {
            get_instance()->user = new User_Guest;
        }

        // Regenerate session ID.
        $this->session->regenerate_id();

        // Remove the data from the active session.
        $this->session->set('auth/' . $auth_class::key(), null);

        // Check for persistence, and clean up.
        if (false !== ($data = $this->check_persist())) {

            $this->db->delete('security', array(
                'security_type'      => 'COOKIE',
                'security_user_id'   => $data['user_id'],
                'security_user_hash' => $data['user_hash']
            ));

            $this->input->set_cookie(self::AUTH_PERSISTENCE_COOKIE, '', -1);
        }

        return true;
    }


    /**
     * persist
     *
     * Allow a login to exist across sessions by setting a security
     * cookie on the client machine, and a record in the database.
     *
     * @param string|null $existing_hash
     *
     * @return void
     */
    public function persist($existing_hash = null) {

        // Generate an expiry date for the cookie
        $now = new DateTime(null, new DateTimeZone('UTC'));
        $ttl = $now->add(new DateInterval(self::AUTH_PERSISTENCE_INTERVAL));

        // Generate the cookie data
        $hash = self::seed();
        $user = &get_instance()->user;
        $data = sprintf('$%07d$%s$', $user->id(), $hash);

        $tuple = array(
            'security_user_id'      => $user->id(),
            'security_type'         => 'COOKIE',
            'security_user_hash'    => self::encrypt($hash),
            'security_user_address' => sprintf('%u', ip2long($this->input->ip_address())),
            'security_date_expires' => $ttl->format('Y-m-d H:i:s')
        );

        // Save persistence data
        if (is_null($existing_hash)) {
            $this->db->insert('security', $tuple);
        }
        else {
            $this->db->update('security', $tuple, array(
                'security_user_id'   => $user->id(),
                'security_user_hash' => $existing_hash,
            ));
        }
        
        $this->input->set_cookie(array(
            'name'   => self::AUTH_PERSISTENCE_COOKIE,
            'value'  => $data,
            'expire' => $ttl->format('U') - time(),
            'domain' => '',
            'path'   => '/'
        ));
    }



    public function check_persist() {

        if (is_null($this->input->cookie(self::AUTH_PERSISTENCE_COOKIE))) {
            return false;
        }
                
        $chunks = preg_split('/\$/', $this->input->cookie(self::AUTH_PERSISTENCE_COOKIE), -1, PREG_SPLIT_NO_EMPTY);

        $this->db->select('*');
        $this->db->from('security');
        $this->db->where('security_type', 'COOKIE');
        $this->db->where('security_user_id', (int)$chunks[0]);
        $possibles = $this->db->get();

        foreach ($possibles->result() as $possible) {

            if ($possible->security_user_hash === crypt($chunks[1], $possible->security_user_hash)) {

                return array(
                    'user_id'   => (int)$chunks[0],
                    'user_hash' => $possible->security_user_hash
                );

            }

        }

        // If we got here, there was no match.
        // Destroy the cookie, it has expired somehow?
        $this->input->set_cookie(self::AUTH_PERSISTENCE_COOKIE, '', -1);

        return false;
    }


    /**
     * pulse
     *
     *
     * @return void
     */
    private function pulse() {

        // If we have no auth data, set the front-end user to be a guest.
        // @todo this is not always true.  e.g., two concurrent sessions where
        // the user is logged in as admin, and also as a site user?
        // also, we should not allow admin users to persist!
        if (!$data = $this->session->get('auth')) {

            if (!$info = $this->check_persist()) {

                // We had no cookie, or there were no valid persistent sessions
                // in the security table. The current user is therefore a Guest
                get_instance()->user = new User_Guest;

                return false;
            }

            // We can log this user in!
            get_instance()->user = $this->model->getById($info['user_id']);
            get_instance()->user->init(false);

            // Update their persistence data
            $this->persist($info['user_hash']);

            return true;

            /*
            // We are not authorised :(
            // Perhaps we have a persistent cookie?
            if (!is_null($this->input->cookie(self::AUTH_PERSISTENCE_COOKIE))) {
                
                $chunks = preg_split('/\$/', $this->input->cookie(self::AUTH_PERSISTENCE_COOKIE), -1, PREG_SPLIT_NO_EMPTY);

                $this->db->select('*');
                $this->db->from('security');
                $this->db->where('security_user_id', (int)$chunks[0]);
                $possibles = $this->db->get();

                foreach ($possibles->result() as $possible) {

                    if ($possible->security_user_hash === crypt($chunks[1], $possible->security_user_hash)) {

                        // We can log this user in!
                        get_instance()->user = $this->model->getById($chunks[0]);
                        get_instance()->user->init(false);

                        // Update their persistence data
                        $this->persist($possible->security_user_hash);

                        return true;
                    }

                }

                // If we got here, there was no match.
                // Destroy the cookie, it has expired somehow?
                $this->input->set_cookie(self::AUTH_PERSISTENCE_COOKIE, '', -1);
            }

            // We had no cookie, or there were no valid persistent sessions
            // in the security table. The current user is therefore a Guest
            get_instance()->user = new User_Guest;

            return false;
            */
        }
        
        // we have data...
        foreach ($data as $key => $sub_data) {

            $auth_class = $sub_data['auth_class'];

            if (!class_exists($auth_class)) {
                $this->session->set('auth/' . $key, null);
                continue;
            }

            $auth_key  = $auth_class::key();
            $auth_user = $this->model->getBySession($auth_class);

            // glue this user to the framework
            get_instance()->$auth_key = $auth_user;

            // Update the pulse time
            // $this->session->set('auth/' . $auth_key . '/pulse_time', time());
        }
    }
    

    /**
     * encrypt
     *
     * @static
     * @param string $string
     * @param string $rounds
     * 
     * @return string
     */
    static public function encrypt($string, $rounds = 11) {
        
        if (!defined('CRYPT_BLOWFISH') || !CRYPT_BLOWFISH) {
            throw new User_Exception('Application depends on the Blowfish algorithm, which is missing from your system.');
        }

        if ($rounds < 4 || $rounds > 31) {
            throw new User_Exception('Blowfish rounds must be within the range 04-31. Rounds specified (' . (int)$rounds . ') is an invalid number.');
        }

        // Generate a 22-character salt (alphabet = 0-9a-zA-Z./)
        $salt = substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22);

        // Generate a blowfish hash of our string
        $hash = crypt($string, sprintf('$2a$%02d$%22s$', $rounds, $salt));

        return $hash;
    }


    /**
     * seed
     *
     * @static
     * @param int $bits
     *
     * @return string
     */
    static public function seed($bits = 128) {
        return bin2hex(openssl_random_pseudo_bytes(round($bits / 2)));
    }


}
