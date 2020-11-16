<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Customfield multiselect Type
 *
 * @package   customfield_multiselect
 * @copyright  2020 CALL Learning 2020 - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_multiselect;

use core_customfield\data;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package   customfield_multiselect
 * @copyright 2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {

    /**
     * Datafield value (here 'value')
     *
     * @return string
     */
    public function datafield(): string {
        return 'value'; // There could be a discussion here if it could not be a char value, but for long list that could have
        // been a limitation.
    }

    /**
     * Get the default value for this field.  The default value is a list of valid options.
     * We just verify they exist before sending their index back.
     *
     * @return string a list of comma separated index of matching options
     */
    public function get_default_value() {
        $defaultvalue = $this->get_field()->get_configdata_property('defaultvalue');
        $options = field_controller::get_options_array($this->get_field());
        $defaultvaluesarray = [];
        $values = explode(",", $defaultvalue);

        foreach ($values as $val) {
            $index = $this->get_option_index($val, $options);
            if ($index !== false) {
                $defaultvaluesarray[] = intval($index);
            }
        }
        return implode(',', $defaultvaluesarray);
    }

    /**
     * Get the option index in the array of options from the raw text value
     *
     * @param mixed $rawvalue
     * @param array $options
     * @return false|int|string
     */
    protected function get_option_index($rawvalue, $options) {
        return array_search($rawvalue, $options);
    }

    /**
     * Define the form
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        global $PAGE;
        $field = $this->get_field();
        $config = $field->get('configdata');
        $options = field_controller::get_options_array($field);
        $formattedoptions = [];
        $context = $this->get_field()->get_handler()->get_configuration_context();
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $formattedoptions[$key] = format_string($option, true, ['context' => $context]);
        }

        $elementname = $this->get_form_element_name();
        $attributes = array('multiple' => true);
        $mform->addElement('autocomplete', $elementname,
            $this->get_field()->get_formatted_name(),
            $formattedoptions,
            $attributes);

        if (($defaultkey = array_search($config['defaultvalue'], $options)) !== false) {
            $mform->setDefault($elementname, $defaultkey);
        }
        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

    /**
     * Prepares the custom field data related to the object to pass to mform->set_data() and adds them to it
     *
     * This function must be called before calling $form->set_data($object);
     *
     * @param \stdClass $instance the instance that has custom fields, if 'id' attribute is present the custom
     *    fields for this instance will be added, otherwise the default values will be added.
     */
    public function instance_form_before_set_data(\stdClass $instance) {
        $instance->{$this->get_form_element_name()} = $this->get_value();
    }

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     * @throws \coding_exception
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }
        $value = implode(',', $datanew->$elementname);
        $this->data->set($this->datafield(), $value);
        $this->data->set('value', $value);
        $this->save();
    }

    /**
     * Returns the value as it is stored in the database or default value if data record is not present
     *
     * @return string comma separated list of items
     */
    public function get_value() {
        if (!$this->get('id')) {
            return $this->get_default_value();
        }
        return $this->get($this->datafield());
    }

    /**
     * Set the value as it should be stored in the database
     *
     * @param array $value to be set and transformed into a comma separated string
     * @return data
     */
    public function set_value($value) {
        return $this->set($this->datafield(), implode(',', $value));
    }

    /**
     * Checks if the value is empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function is_empty($value): bool {
        return trim($value) === "";
    }

    /**
     * Returns value in a human-readable format or default value if data record is not present
     *
     * This is the default implementation that most likely needs to be overridden
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $values = $this->get_value(); // This is a string of comma separated list of indexes.

        if ($this->is_empty($values)) {
            return null;
        }
        // Change into an array for parsing.
        $valuesarray = explode(',', $values);
        if (!$valuesarray) {
            $valuesarray = [];
        }
        $commasepoptionvalues = "";
        $options = field_controller::get_options_array($this->get_field());
        foreach ($valuesarray as $val) {
            if (!empty($options[$val])) {
                $commasepoptionvalues .= (empty($commasepoptionvalues) ? '' : ', ') .
                    format_string($options[$val], true,
                        ['context' => $this->get_field()->get_handler()->get_configuration_context()]);
            }
        }
        return $commasepoptionvalues;
    }

}