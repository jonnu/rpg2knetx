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
     * @var int
     */
    const CACHE_TTL = 3600;

    /**
     * @var array
     */
    private static $config = array();

    /**
     * @var array
     */
    private static $modules = array();


    /**
     * init
     *
     * Find all possible module locations and save the library paths in the
     * base include_path, allowing the use of stream_resolve_include_path.
     *
     * @param array $config
     *
     * @return void
     */
    static public function init(array $config = array()) {

        self::$config = $config;

        // Load cached data.
        if (extension_loaded('apc')) {
            $include_path  = apc_fetch('core_include_path');
            self::$modules = apc_fetch('core_modules_loaded') ?: array();
        }

        if (!isset($include_path) || false === $include_path) {
            $include_path = self::buildIncludePath();
        }

        set_include_path($include_path);

        // Register loading function
        spl_autoload_register(array(__CLASS__, 'load'));

        if (extension_loaded('apc')) {
            apc_store('core_modules_loaded', self::$modules, self::CACHE_TTL);
            apc_store('core_include_path', $include_path, self::CACHE_TTL);
        }
    }

    /**
     * buildIncludePath
     *
     * Create an include path by scanning the current module locations and
     * combining their paths to the common framework system paths.
     *
     * @return string
     */
    static private function buildIncludePath() {

        foreach (self::$config['modules_locations'] as $location) {

            foreach (new DirectoryIterator($location) as $folder) {

                if (!$folder->isDir() || $folder->isDot()) {
                    continue;
                }

                // Remember modules.
                self::$modules[] = $folder->getBasename();

                $paths[] = realpath($folder->getPathname()) . DIRECTORY_SEPARATOR . 'libraries';
            }
        }

        // Combine modules and core
        $paths = array_merge(array('.',
            realpath(APPPATH) . DIRECTORY_SEPARATOR . 'extensions',
            realpath(APPPATH) . DIRECTORY_SEPARATOR . 'libraries'
        ), $paths);

        // Build the new include path & return
        $include_path = join($paths, PATH_SEPARATOR);

        return $include_path;
    }


    /**
     * load
     *
     * Load a file, depending on the filename. The loader currently supports
     * several 'conventions', including PSR-0. Unfortuantly, this process is
     * messy due to the underlying framework.
     *
     * @param string $class
     *
     * @return mixed
     */
    static public function load($class) {

        // Get the stub, e.g. RPG_SomeClass -> RPG.
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

                    // Is this module a member of a module?
                    $peep = strtolower(substr($psr0, 0, strpos($psr0, '/')));
                    if (in_array($peep, self::$modules)) {
                        $psr0 = substr($psr0, strpos($psr0, '/') + 1);
                    }
                }

                if (false === ($filename = stream_resolve_include_path($psr0))) {
                    throw new Exception('Unable to load file: ' . $psr0);
                }

                require_once $filename;
        }

        return false;
    }


    /**
     * model
     *
     * Load a model directly. Base models are 'special snowflaskes' at the
     * moment and have their own 'model' directory in the application root.
     *
     * @todo Make modules obey PSR-0 (Model_Example = libraries/Model/Example.php)
     *
     * @param string $class
     *
     * @return void
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