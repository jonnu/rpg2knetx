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
 * RPG_Autoloader
 * 
 * @category  Core
 */
class RPG_Autoloader {

    /**
     * @var array
     */
    private static $config  = array();

    /**
     * @var array
     */
    private static $modules = array();


    /**
     * init
     *
     */
    static public function init($config = array()) {

        self::$config = $config;

        foreach (self::$config['modules_locations'] as $location) {

            foreach (new DirectoryIterator($location) as $folder) {

                if (!$folder->isDir() || $folder->isDot()) {
                    continue;
                }

                // Remember module.
                self::$modules[] = $folder->getBasename();

                $paths[] = realpath($folder->getPathname()) . DIRECTORY_SEPARATOR . 'libraries';
            }
        }

        $paths = array_merge(array('.', realpath(APPPATH) . DIRECTORY_SEPARATOR . 'libraries'), $paths);

        // Build the new include path & register autoloader
        set_include_path(join($paths, PATH_SEPARATOR));
        spl_autoload_register(array(__CLASS__, 'load'));
    }


    /**
     * load
     * 
     */
    static public function load($class) {

        $stub = strtoupper(substr($class, 0, strpos($class, '_')));
        $psr0 = str_replace(array('\\', '_'), '/', $class) . EXT;

        switch ($stub) {

            case 'CI':
            case rtrim(self::$config['subclass_prefix'], '_'):
                return;

            case 'MODEL':
                return self::model($class);

            default:

                if (false !== strpos($psr0, '/')) {
                    $peep = strtolower(substr($psr0, 0, strpos($psr0, '/')));
                    if (in_array($peep, self::$modules)) {
                        $psr0 = substr($psr0, strpos($psr0, '/') + 1);
                    }
                    
                    //
                }


                if (false === ($filename = stream_resolve_include_path($psr0))) {
                    echo $peep . '<br />';
                    echo $psr0 . '<br />';
                    echo get_include_path() . '<br />';
                    throw new Exception('Unable to load ' . $psr0);
                }

                require_once $filename;
        }

        return false;
    }


    /**
     * model
     *
     * @todo the models do not obey PSR-0.
     * @param string $class
     */
    static public function model($class) {

        foreach(array(APPPATH . 'models') as $autoload_path_folder) {

            if (!file_exists($autoload_path_class = $autoload_path_folder . DIRECTORY_SEPARATOR . $class . EXT)) {
                continue;
            }

            require $autoload_path_class;
        }
    }


}