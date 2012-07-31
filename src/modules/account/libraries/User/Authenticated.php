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
 * User_Authenticated
 *
 * @category  Account
 */
class User_Authenticated extends User {

    protected static $key = 'user';


    /**
     * authenticated
     * 
     * @return boolean
     */
    public function authenticated() {
        return true;
    }


}
