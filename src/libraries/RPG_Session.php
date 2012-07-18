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
 * RPG_Session
 * 
 * @category  Core
 */
class RPG_Session extends CI_Session {

    /**
     * Name of the session key that holds it's
     * own regeneration time (for checking staleness)
     *
     * @var string
     */
    public $sess_regeneration_key = 'core/time_regenerated';


    /**
     * RPG_Session Constructor
     *
     * The constructor runs the session routines automatically
     * whenever the class is instantiated.
     * 
     * @return void
     */
    public function __construct($configuration = array()) {

        // Start our session
        session_start();

        // If the session is stale, regenerate the ID
        if ($this->_session_id_expired()) {
            $this->regenerate_id();
        }

        // Delete 'old' flashdata (from last request)
        $this->_flashdata_sweep();

        // Mark all new flashdata as old (data will be deleted before next request)
        $this->_flashdata_mark();
    }


    /**
     * get
     *
     * Get data from the session by path.  Each 'segment' is another 
     * level deeper within a structure of nested arrays, and is defined
     * by a forward-slash character.
     *
     * @param string|null $path  path to the element within the session
     * @param bool|null $remove  when true, the selected element is removed after it has been obtained
     *
     * @return mixed
     */
    public function get($path = null, $remove = false) {

        if (is_null($path)) {
            return $_SESSION;
        }

        $pointer = &$_SESSION;
        foreach ($explode = preg_split('/\//', $path, null, PREG_SPLIT_NO_EMPTY) as $index => $segment) {

            if (!isset($pointer[$segment])) {
                return false;
            }

            if ($index == count($explode) - 1 && $remove) {
                $find_data = $pointer[$segment];
                unset($pointer[$segment]);
                return $find_data;
            }

            if (!is_array($pointer)) {
                return false;
            }

            $pointer = &$pointer[$segment];
        }

        return $pointer;
    }


    /**
     * set
     *
     * Append data to the session
     *
     * @param string $path  path to the element within the session
     * @param mixed $value  the data to append to the session
     *
     * @return bool
     */
    public function set($path, $value = null) {

        $pointer = &$_SESSION;
        foreach ($explode = preg_split('/\//', $path, null, PREG_SPLIT_NO_EMPTY) as $segment) {

            if ($segment !== end($explode)) {

                if (!isset($pointer[$segment])) {
                    $pointer[$segment] = array();
                }

                $pointer =& $pointer[$segment];
                continue;
            }
        }

        // Are we destroying a value?
        if (is_null($value)) {
            unset($pointer[$segment]);
            return true;
        }

        // Set the new value
        $pointer[$segment] = $value;

        return true;
    }


    /**
     * regenerate_id
     *
     * Regenerate the session ID, keeping the data from the old 
     * session but updating the expiry time for the new session.
     *
     * @return void
     */
    public function regenerate_id() {

        // Do not regenerate on AJAX requests
        if (get_instance()->input->is_ajax_request()) {
            return;
        }

        $old_session_id   = session_id();
        $old_session_data = $this->get();

        session_regenerate_id();
        $new_session_id = session_id();

        session_id($old_session_id);
        session_destroy();

        session_id($new_session_id);
        session_start();

        $_SESSION = $old_session_data;
        unset($old_session_data);

        // Set the new expiry time inside the session
        $this->set($this->sess_regeneration_key, time());
    }
 

    /**
     * _session_id_expired
     * 
     * Check to see if the session has expired based upon the 
     * session's regeneration timestamp.
     *
     * @return bool
     */
    private function _session_id_expired() {

        if (!$regenerated_time = $this->get($this->sess_regeneration_key)) {
            $this->set($this->sess_regeneration_key, $regenerated_time = time());
        }

        // Work out the expire time for this session
        $expiry_time = time() - $this->sess_time_to_update;

        return $regenerated_time <= $expiry_time;
    }


    /**
     * set_flashdata
     *
     * Add or change flashdata.  Flashdata is data which can be requested
     * only once before it is automatically destroyed (useful for displaying
     * notices or passing variables invisibly between pages).
     *
     * @param $paths array
     * @param $value mixed
     *
     * @return void
     */
    public function set_flashdata($paths = array(), $value = '') {

        if (!is_array($paths)) {
            return $this->set_flashdata(array($paths => $value));
        }

        foreach ($paths as $path => $data) {
            $this->set($this->flashdata_key . '/' . $path, $data);
        }
    }


    /**
     * flashdata
     * 
     * Fetch a specific flashdata item from the session array, or false
     * if the specific item does not exist in the array.
     *
     * @param string $path
     * @param bool $keep
     *
     * @return mixed
     */
    public function flashdata($path, $keep = false) {
        return $this->get($this->flashdata_key . '/' . $path, !$keep);
    }
 
 
    /**
     * destroy
     *
     * Destroys the current session, and unsets and cookie
     * relating to that session (if it exists).
     *
     * @return bool
     */
    public function destroy() {

        // are we storing the session id inside a cookie?
        if (ini_get('session.use_cookies') && isset($_COOKIE[session_name()])) {

            // pull parameters from the session
            $parameters = session_get_cookie_params();

            // destroy the cookie
            setcookie(session_name(), false, 1, $parameters['path'], $parameters['domain'], $parameters['secure'], $parameters['httponly']);
        }

        // unset session
        unset($_SESSION);

        return session_destroy();
    }


}