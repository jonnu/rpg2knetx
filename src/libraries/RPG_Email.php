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
 * RPG_Email
 * 
 * @category  Core
 */
class RPG_Email extends CI_Email {

    public $useragent = 'RPG2KNET';
    public $protocol  = 'sendmail';

    protected $_replyto_flag = false;


}