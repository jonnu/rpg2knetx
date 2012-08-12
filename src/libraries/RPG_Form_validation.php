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
 * RPG_Form_Validation
 * 
 * @category  Core
 */
class RPG_Form_Validation extends CI_Form_Validation {

    /**
     * HTML fragment (opening tag) that signifies the start
     * of a list of errors.
     *
     * @var string
     */
    public $_error_open = '<ul class="validation-errors">';

    /**
     * HTML framement (closing tag) that signifies the end
     * of a list of errors.
     *
     * @var string
     * @see $_error_open
     */
    public $_error_close = '</ul>';

    /**
     * HTML fragment (opening tag) that wraps each error
     * message before it is output.
     *
     * @var string
     */
    public $_error_prefix = '<li>';

    /**
     * HTML fragment (closing tag) that wraps each error
     * message before it is output.
     *
     * @var string
     */
    public $_error_suffix = '</li>';


    /**
     * value
     *
     * @param string $field 
     * @param string $default 
     * @param string $auto_encode 
     * @param string $nibble_arrays
     *
     * @return mixed
     */
    public function value($field, $default = '', $auto_encode = true, $nibble_arrays = true) {

        $set_value = $this->set_value($field, $default);

        if (is_array($set_value)) {

            $return_values = $auto_encode ? array_map('htmlspecialchars', $set_value, array_fill(0, count($set_value), ENT_COMPAT), array_fill(0, count($set_value), 'UTF-8'), array_fill(0, count($set_value), false)) : $set_value;

            // If we just want the array in question, stop it
            // from being 'nibbled'.
            if (!$nibble_arrays) {
                return $return_values;
            }

            if (!isset($this->_field_points[$field])) {
                $this->_field_points[$field] = 0;
            }

            // Get the applicable item, and then index.
            $selected_index = array_slice($return_values, $this->_field_points[$field], 1);
            $this->_field_points[$field]++;

            // Use current() since it will return an array with 1 item.
            return current($selected_index);
        }

        if (is_null($set_value) || $auto_encode === false) {
            return $set_value;
        }

        return htmlspecialchars($set_value);
    }


    /**
     * display
     *
     * Output security and validation data as a block of HTML.
     *
     * @return string
     */
    public function display() {

        // Create a security nonce
        $nonce  = form_hidden('nonce', $this->nonce_create(), true);
        $output = array($nonce);

        // If there are no errors, display none
        if (count($this->error_array()) === 0) {
            return implode(PHP_EOL, $output);
        }

        array_push($output, $this->_error_open);

        foreach ($this->error_array() as $error_string) {

            if (!is_string($error_string)) {
                continue;
            }

            array_push($output, vsprintf('%s%s%s', array(
                $this->_error_prefix,
                trim($error_string),
                $this->_error_suffix
            )));
        }

        array_push($output, $this->_error_close);

        return implode(PHP_EOL, $output);
    }


    /**
     * checked
     * 
     * @return mixed
     */
    public function checked($field, $value = 'on', $default = false) {

        if (!isset($this->_field_data[$field])) {
            return $default ? ' checked="checked"' : null;
        }

        if ($this->_field_data[$field]['postdata'] == $value) {
            return ' checked="checked"';
        }

        return null;
    }


    public function is_checked($field) {

        if (!isset($this->_field_data[$field])) {
            return false;
        }

        if (is_null($this->_field_data[$field]['postdata'])) {
            return false;
        }

        return true;
    }


    public function add_error($message, $field = null, $highlight_only = false) {

        if (is_array($field)) {

            $highlight_flag = false;
            foreach ($field as $field_element) {
                $this->add_error($message, $field_element, $highlight_flag);
                $highlight_flag = $highlight_only;
            }

            return;
        }

        $message = $highlight_only ? true : trim($message);

        if (!is_null($field)) {

            $this->{$field . '_error'}  = $message;
            $this->_error_array[$field] = $message;

            return;
        }

        $this->_error_array[] = $message;

        return;
    }


    /**
     * module
     *
     * Pass field data through to a module's model for validation.
     * Data is passed by reference in order to allow functions to
     * alter the content of the field.
     *
     * @param string $data
     * @param string $arguments
     *
     * @return boolean
     */
    public function module(&$data, $arguments) {

        if (!preg_match('/^((?<model>[a-z_]+),)?(?<func>[a-z_]+)(\[(?<param>[a-z,]+)\])?$/', $arguments, $elements)) {
            $this->set_message(__function__, 'Invalid module validation call: "%2$s"', $arguments);
            return false;
        }

        $module     = $this->CI->router->fetch_module();
        $function   = $elements['func'];
        $parameters = array(&$data);

        if (isset($elements['model'])) {
            $model = $elements['model'];
        }

        if (isset($elements['param'])) {
            $parameters = array_merge($parameters, explode(',', $elements['param']));
        }

        if (!property_exists($this->CI, $model)) {
            $this->CI->load->model($module . '/' . $model, $model);
        }

        if (!method_exists($this->CI->$model, $function)) {

            $this->set_message(__function__, sprintf(
                'Cannot locate validate method "%s->%s(%s)"',
                $model,
                $function,
                implode(', ', array_map(
                    function($i) {
                        return '$' . (++$i);
                    },
                    array_slice(array_keys($parameters), 1)
                ))
            ));

            return false;
        }

        // Call the validation function on the foreign model
        $return = call_user_func_array(array($this->CI->$model, $function), $parameters);

        if (!is_bool($return)) {
            $this->set_message(__function__, sprintf('Validation function %s must return boolean', $function));
            return false;
        }

        // If validation failed, take the error from the stack to make it 'belong' to this function
        if (!$return) {
            $this->set_message(__function__, array_pop($this->_error_messages));
        }

        return $return;
    }


    /**
     * nonce_create
     *
     *
     * @return string
     */
     public function nonce_create() {

        if (false !== ($nonce = $this->CI->session->get('core/nonce'))) {
            return $nonce;
        }

        $ip    = $this->CI->input->ip_address();
        $nonce = md5(mt_rand() . $ip . microtime());

        // Store the nonce in the session
        $this->CI->session->set('core/nonce', $nonce);

        return $nonce;
    }


    /**
     * nonce_valid
     *
     * 
     * @param string $nonce
     * @return bool
     */
    public function nonce_valid($nonce) {

        // Read session nonce
        $sess_nonce = $this->CI->session->get('core/nonce', true);

        // Does the nonce match the session?
        if (0 !== strcmp($sess_nonce, $nonce)) {
            $this->set_message(__function__, 'NONCE FAILURE: Invalid Token. Check form, try again.');
            return false;
        }

        return true;
    }


    /**
     * run
     *
     * Run the Validator
     *
     * @param string $group
     * @return bool
     */
    public function run($group = '') {

        if ($group !== '' && isset($this->_config_rules[$group])) {

            $this->_config_rules[$group][] = array(
                'field' => 'nonce',
                'label' => 'Nonce',
                'rules' => 'required|nonce_valid'
            );
        }

        return parent::run($group);
    }


    /**
     * _execute
     * 
     * Executes the Validation routines
     *
     * @see https://github.com/EllisLab/CodeIgniter/issues/1558
     *
     * @param array
     * @param array
     * @param mixed
     * @param int
     *
     * @return mixed
     */
    protected function _execute($row, $rules, $postdata = null, $cycles = 0) {

        // Remove any empty rules
        $rules = array_filter($rules, function($item) {
            return !empty($item);
        });

        return parent::_execute($row, $rules, $postdata, $cycles);
    }


}