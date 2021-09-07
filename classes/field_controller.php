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

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package   customfield_multiselect
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Customfield type
     */
    const TYPE = 'multiselect';

    /**
     * Form defintion for multiselect
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_multiselect'));
        $mform->setExpanded('header_specificsettings', true);

        $mform->addElement('textarea', 'configdata[options]', get_string('menuoptions', 'customfield_multiselect'));
        $mform->setType('configdata[options]', PARAM_TEXT);

        $mform->addElement('text', 'configdata[defaultvalue]', get_string('defaultvalue', 'customfield_multiselect'));
        $mform->setType('configdata[defaultvalue]', PARAM_TEXT);
    }

    /**
     * Returns the options available as an array.
     *
     * @return array
     */
    public function get_options(): array {
        if ($this->get_configdata_property('options')) {
            $options = preg_split("/\s*\n\s*/", trim($this->get_configdata_property('options')));
        } else {
            $options = array();
        }
        return $options;
    }

    /**
     * Returns the options available as an array.
     * Method compatible with select type of customfield.
     *
     * @param \core_customfield\field_controller $field
     * @return array
     */
    public static function get_options_array(\core_customfield\field_controller $field): array {
        return $field->get_options();
    }

    /**
     * Validate the data from the config form.
     * Sub classes must reimplement it.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     * @throws \coding_exception
     */
    public function config_form_validation(array $data, $files = array()): array {
        $options = preg_split("/\s*\n\s*/", trim($data['configdata']['options']));
        $errors = [];
        if (!$options || count($options) < 2) {
            $errors['configdata[options]'] = get_string('errornotenoughoptions', 'customfield_multiselect');
        } else if (!empty($data['configdata']['defaultvalue'])) {
            $defaultvalue = $data['configdata']['defaultvalue'];
            foreach (explode(',', $defaultvalue) as $val) {
                $defaultkey = array_search($val, $options);
                if ($defaultkey === false) {
                    $errors['configdata[defaultvalue]'] = get_string('errordefaultvaluenotinlist',
                        'customfield_multiselect', $val);
                    break;
                }
            }
        }
        return $errors;
    }

    /**
     * Separator between different option when parsing.
     */
    const PARSE_SEPARATOR = '|';
    /**
     * Locate the values set in the list (comma separated list), and return the corresponding
     * indexed list.
     *
     * @param string $value
     * @return $value
     */
    public function parse_value(string $value) {
        $options = $this->get_options();
        $values = array_map(function($val) {
            return trim(strtolower($val));
        }, explode(self::PARSE_SEPARATOR, $value));
        $indexvalues = [];
        foreach ($options as $index => $value) {
            if (in_array(trim(strtolower($value)), $values)) {
                $indexvalues[] = $index;
            }
        }
        sort($indexvalues);
        return implode(',', $indexvalues);
    }
}
