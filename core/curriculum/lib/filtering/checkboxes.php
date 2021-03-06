<?php //$Id$
/**
 * checkboxes.php - PHP Report filter
 *
 * Group of checkboxes (TBD: add layout options, columns/row designation ...)
 * for selecting choices for DB field using SQL 'IN' statement (= if only one)
 * options include:
 *   ['choices'] = array(key = 'DB field value', value = 'display string' ...)
 *   ['defaults'] = array('DB field value', ... ) - used when no options selected (optional)
 *   ['checked'] = array('DB field value', ...) - initially checked on form (optional)
 *   ['advanced'] = array('DB field value', ...) - which elements are advanced
 *   ['heading'] = string - the checkbox group heading (optional, raw html)
 *   ['footer'] = string - the checkbox group footer (optional, raw html)
 *   ['numeric'] = boolean - true if DB field is numeric,
 *                           false (the default) -> string
 *   ['set_value_fcn'] = callback array for call_user_fcn()::arg1,
 *                       arg2 is set to filter value.
 *                       Eg. 'set_value_fcn' => array(&$this, 'set_method')
 *
 * @author Brent Boghosian <brent.boghosian@remote-learner.net>
 * @version $Id$
 * @package curriculum/lib/filtering
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright (C) 2008-2012 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once($CFG->dirroot.'/user/filters/lib.php');

/**
 * Generic checkbox filter based on a list of values ...
 */
class generalized_filter_checkboxes extends generalized_filter_type {
    /**
     * Array - options for the checkboxes:
     * 'choices' => array
     *     keys are checkbox 'values' numbers or strings
     *     values are checkbox label strings
     * 'checked' => array of 'keys' only (from 'choices') to be checked
     * 'defaults' => array
     *     'keys' only (from 'choices') to be used if NO choices are checked!
     * 'heading' => string [optional] - checkbox group heading
     * 'footer' => string [optional] - checkbox group footer
     * 'numeric' => boolean - true if 'keys' should be numerically compared
     *                        optional, defaults to false (not numeric)
     */
    var $_options;

    var $_field;

    var $_formdelim = '_'; // required in: blocks/php_report/lib/filtering.php

    var $_isrequired;

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     * @param string $field user table field name
     * @param array $options mixed array of checkbox options - see above
     */
    function generalized_filter_checkboxes($uniqueid, $alias, $name, $label, $advanced, $field, $options = array()) {
        parent::generalized_filter_type($uniqueid, $alias, $name, $label, $advanced,
                    !empty($options['help']) ? $options['help'] : null);
        $this->_field   = $field;
        $this->_options = $options;
        $this->_isrequired = !empty($options['isrequired']) ? $options['isrequired'] : false;
    }

    /**
     * Returns an array of checkbox defaults
     * @param array
     * @return array
     */
    function get_default_values($filter_data) {
        $checkbox_filter = array();
        foreach ($filter_data as $key => $value) {
            // Remove the prefix to match the id's of the arrays in order to determine the checkbox default
            $checkbox_filter[$key] = in_array(substr($key, strlen($this->_uniqueid) + 1), $this->_options['checked']);
        }
       return $checkbox_filter;
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $objs = array();

        if (!empty($this->_options['choices'])) {
            $usedefault = true;
            foreach ($this->_options['choices'] as $key => $value) {
                $elem = $this->_uniqueid . $this->_formdelim . $key;
                $objs[] = &$mform->createElement('advcheckbox', $elem, '', $value);
                if (!empty($mform->_defaultValues[$elem])) {
                    $usedefault = false;
                }
            }
        }

        if (!empty($this->_options['footer'])) {
            $objs[] = &$mform->createElement('static',
                          $this->_uniqueid . $this->_formdelim . 'footer', '',
                          $this->_options['footer']);
        }

        $mform->addElement('group', $this->_uniqueid.'_grp',
                    !empty($this->_options['heading'])
                    ? $this->_options['heading']: '' , $objs, '<br/>', false);

        if (!empty($this->_filterhelp)) {
            $mform->setHelpButton($this->_uniqueid.'_grp', $this->_filterhelp);
        }

        if (!empty($this->_options['choices'])) {
            if ($usedefault) {
                if (!empty($this->_options['checked'])) {
                    foreach ($this->_options['checked'] as $checked) {
                        $mform->setDefault($this->_uniqueid . $this->_formdelim
                                           . $checked, 1);
                    }
                }
            } else {
                foreach ($this->_options['choices'] as $key => $value) {
                    $elem = $this->_uniqueid . $this->_formdelim . $key;
                    if (!empty($mform->_defaultValues[$elem])) {
                        $mform->setDefault($elem, $mform->_defaultValues[$elem]);
                    }
                }
            }
        }

        if (!empty($this->_options['advanced'])) {
            foreach ($this->_options['advanced'] as $advanced) {
                $mform->setAdvanced(
                        $this->_uniqueid . $this->_formdelim . $advanced);
            }
        }

    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $field = $this->_uniqueid;

        $retval = '';
        // check for checkboxes checked ...
        foreach ($this->_options['choices'] as $key => $value) {
            $formfield = $field . $this->_formdelim . $key;
            if (!empty($formdata->{$formfield})) {
                if ($retval !== '') {
                    $retval .= ',';
                }
                $retval .= empty($this->_options['numeric'])
                           ? "'". $key ."'" : $key;
                //error_log("checkboxes.php::check_data(): formdata->{$formfield} ($value) is CHECKED");
            }
        }

        if (empty($retval) && !empty($this->_options['defaults'])) {
            // if none checked use default
            foreach ($this->_options['defaults'] as $default) {
                if ($retval !== '') {
                    $retval .= ', ';
                }
                $retval .= empty($this->_options['numeric'])
                           ? "'". $default ."'" : $default;
            }
        }

        if (!empty($this->_options['set_value_fcn'])) {
            // if user defines callback array then call it
            call_user_func($this->_options['set_value_fcn'], $retval);
        }
        //error_log("checkboxes::check_data() 'value' => $retval");
        //$this->err_dump($formdata, '$formdata');

        if (isset($this->_options['allowempty']) && $this->_options['allowempty'] === true) {
            //allowing empty values to be considered, so directly return whatever's set
            return array('value' => (string)$retval);
        }

        return $retval === '' ? FALSE : array('value' => (string)$retval);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        $filter = $this->_label;
        if ((!isset($data['value']) ||
             ($data_array = explode(',', $data['value'])) === false ||
             empty($data_array)) && !empty($this->_options['defaults']))
        {
            $data_array = $this->_options['defaults'];
        }
        if (!empty($data_array)) {
            array_walk($data_array, 'clean_data_value');
            $filter .= '<ul>';
            foreach ($this->_options['choices'] as $key => $value) {
                if (in_array($key, $data_array)) {
                    $filter .= "<li>{$value}</li>";
                }
            }
            $filter .= '</ul>';
        }
        return $filter;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return string the filtering condition or null if the filter is disabled
     */
    function get_sql_filter($data) {
        $full_fieldname = $this->get_full_fieldname();
        if (!isset($data['value']) || empty($full_fieldname)) {
            return null;
        }
        if (strpos($data['value'], ',') === false) {
            // Special case if only one value
            if (isset($this->_options['nullvalue']) && $data['value'] === $this->_options['nullvalue']) {
                return "{$full_fieldname} IS NULL";
            }
            $val = $data['value'];
            if (empty($this->_options['numeric'])) {
                $val = "'{$val}'";
            }
            return "{$full_fieldname} = {$val}";
        }

        //split out the different values and create the necessary condition
        $parts = explode(',', $data['value']);

        $condition = '';

        if (!empty($parts)) {
            foreach ($parts as $part) {
                if (isset($this->_options['nullvalue']) && $part === $this->_options['nullvalue']) {
                    //NULL case
                    $condition = $condition === '' ? "{$full_fieldname} IS NULL" : $condition . " OR {$full_fieldname} IS NULL";
                } else {
                    if (empty($this->_options['numeric'])) {
                        //wrap as a string if necessary
                        $part = "'{$part}'";
                    }
                    //append equality condition
                    $condition = $condition === '' ? "{$full_fieldname} = {$part}" : $condition . " OR {$full_fieldname} = {$part}";
                }
            }
        }

        //no conditions applied, so perform no filtering
        if ($condition === '') {
            $condition = 'TRUE';
        }

        return "($condition)";
    }

    function get_report_parameters($data) {
        return array('value' => $data['value']);
    }

    // Debug helper function
    function err_dump($obj, $name = '') {
        ob_start();
        var_dump($obj);
        $tmp = ob_get_contents();
        ob_end_clean();
        error_log('err_dump:: '.$name." = {$tmp}");
    }
}

/**
 * Support function for get_label() above
 */
function clean_data_value(&$val, $idx) {
    $val = trim($val, "' ");
}

?>
