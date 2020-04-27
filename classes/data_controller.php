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

use core_customfield\api;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package   customfield_multiselect
 * @copyright 2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {
    public function datafield(): string {
        return 'value'; // There could be a discussion here if it could not be a char value, but for long list that could have
        // been a limitation.
    }

    /**
     * Get the default value for this field.  The default value is a list of valid options.
     * We just verify they exist before sending their index back.
     *
     * @return array|string[]|mixed
     */
    public function get_default_value() {
        $defaultvalue = $this->get_field()->get_configdata_property('defaultvalue');
        $options = \field_controller::get_options_array($this->get_field());
        $defaultvaluesint = [];
        $values = explode('\n', $defaultvalue);
        foreach ($values as $val) {
            $this->add_option_index($defaultvaluesint, $val, $options);
        }

        return $defaultvaluesint;
    }

    /**
     * Add the value to the indexed array if the value is found
     *
     * @param $intvalues
     * @param $rawvalue
     * @param $options
     */
    protected function add_option_index($intvalues, $rawvalue, $options) {
        $key = array_search($rawvalue, $options);
        if ($key !== false) {
            array_push($intvalues, $key);
        }
    }

    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $config = $field->get('configdata');
        $options = self::get_options_array($field);
        $formattedoptions = array('multiple' => true);
        $context = $this->get_field()->get_handler()->get_configuration_context();
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $formattedoptions[$key] = format_string($option, true, ['context' => $context]);
        }

        $elementname = $this->get_form_element_name();
        $mform->addElement('select', $elementname, $this->get_field()->get_formatted_name(), $formattedoptions);

        if (($defaultkey = array_search($config['defaultvalue'], $options)) !== false) {
            $mform->setDefault($elementname, $defaultkey);
        }
        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

}
