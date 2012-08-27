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
 * RPG_Loader
 *
 * @category  Core
 */
class RPG_Loader extends HMVC_Loader {

    public function __construct() {

        parent::__construct();

        // Allow module views to exist in VIEWPATH
        $r_view = realpath(FCPATH . VIEWPATH);
        $module = $this->_ci_get_component('router')->fetch_module();
        $r_path = sprintf('%s/%s/', $r_view, $module);

        // Append this path to allowed view paths
        $this->_ci_view_paths[$r_path] = true;
    }


}
