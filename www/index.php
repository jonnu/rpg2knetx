<?php

define('ENVIRONMENT', isset($_SERVER['RPG2KNET_ENVIRONMENT']) ? $_SERVER['RPG2KNET_ENVIRONMENT'] : '');
if (!in_array(ENVIRONMENT, array('development', 'production'))) {
    die('please set RPG2KNET_ENVIRONMENT [development|production]');
}

switch (ENVIRONMENT) {

    case 'development': {

        error_reporting(E_ALL | E_STRICT);
        break;
    }
    case 'production': {

        error_reporting(0);
        break;
    }

}



/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same  directory
 * as this file.
 */
    $system_path = '/var/www/common/codeigniter.git/system';

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server. If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
    $application_folder = '../src';

/*
 *---------------------------------------------------------------
 * VIEW FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view folder out of the application
 * folder set the path to the folder here. The folder can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application folder. If you
 * do move this, use the full server path to this folder.
 *
 * NO TRAILING SLASH!
 */
    $view_folder = '../www/html';


/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

    // Set the current directory correctly for CLI requests
    if (defined('STDIN'))
    {
        chdir(dirname(__FILE__));
    }

    if (realpath($system_path) !== FALSE)
    {
        $system_path = realpath($system_path) . '/';
    }

    // ensure there's a trailing slash
    $system_path = rtrim($system_path, '/') . '/';

    // Is the system path correct?
    if (!is_dir($system_path))
    {
        exit('Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME));
    }

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
    // The name of THIS file
    define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

    // The PHP file extension
    // this global constant is deprecated.
    define('EXT', '.php');

    // Path to the system folder
    define('BASEPATH', str_replace('\\', '/', $system_path));

    // Path to the front controller (this file)
    define('FCPATH', str_replace(SELF, '', __FILE__));

    // Name of the "system folder"
    define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

    // The path to the "application" folder
    if (is_dir($application_folder))
    {
        define('APPPATH', $application_folder . '/');
    }
    else
    {
        if (!is_dir(BASEPATH . $application_folder . '/'))
        {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, '503');
            exit('Your application folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF);
        }

        define('APPPATH', BASEPATH . $application_folder . '/');
    }

    // The path to the "views" folder
    if (is_dir($view_folder))
    {
        define('VIEWPATH', $view_folder . '/');
    }
    else
    {
        if (!is_dir(APPPATH . 'views/'))
        {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, '503');
            exit('Your view folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF);
        }

        define('VIEWPATH', APPPATH . 'views/');
    }

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once APPPATH  . 'core/RPG_Autoloader.php';
require_once BASEPATH . 'core/CodeIgniter.php';

/* End of file index.php */
/* Location: ./index.php */
