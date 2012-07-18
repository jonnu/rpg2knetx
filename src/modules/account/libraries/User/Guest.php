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
 * User_Guest
 *
 * @category  Account
 */
class User_Guest extends User {

    protected $user_id   = 0;
    protected $user_name = 'Guest';


    /**
     * authenticated
     * 
     * @return boolean
     */
    public function authenticated() {
        return false;
    }


    /**
     * login
     *
     */
    final static public function login($identifier, $password, $persistent = false) {
        return false;
    }


}