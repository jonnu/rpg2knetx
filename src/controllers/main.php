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
 * Main
 *
 * @category  Core
 */
class Main extends CI_Controller {

    /**
     * index
     *
     */
    public function index() {
        $this->load->view('home/index.view.php');
    }


}