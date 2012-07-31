<?php
(defined('BASEPATH')) or exit('No direct script access allowed');

/* load the HMVC_Loader class */
require APPPATH . 'extensions/HMVC/Loader.php';

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


    /*
    @removed

    Idea behind this function was to start pushing CI towards
    PSR-0 without breaking functionality.  The main issue with
    this is it isolates models completely from the rest of the
    system and makes them special snowflakes.

    Leaving this in the repo for now in case I want to revisit
    in the future.

    ...

    public function library($library = '', $params = null, $object_name = null) {

        if (is_array($library)) {

            foreach ($library as $class) {
                $this->library($class, $params);
            }

            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($library)) {

            // Module has already been loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::library($class, $params, $object_name);
            }

            // Add module
            $this->add_module($module);

            // Tag that we are using a method.
            $params['PSR0_CLASS'] = ucfirst($module) . '_' . ucfirst($class);

            // Let parent do the heavy work
            $void = parent::library($class, $params, $object_name);

            // Remove module
            $this->remove_module();

            return $void;
        }
        else {

            return parent::library($library, $params, $object_name);
        }
    }


    protected function _ci_init_class($class, $prefix = '', $config = false, $object_name = null) {

        // If this initiation has been tagged, get the PSR0
        // and use it to initiate this with the parent class
        if (isset($config['PSR0_CLASS'])) {

            $class = $config['PSR0_CLASS'];
            unset($config['PSR0_CLASS']);
        }

        return parent::_ci_init_class($class, $prefix, $config, $object_name);
    }

    */
}
