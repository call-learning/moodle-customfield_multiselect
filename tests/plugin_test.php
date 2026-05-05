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
 * Customfield Multiselect Type  - derived from customfield_select
 *
 * @package    customfield_multiselect
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_multiselect;

use core_customfield_test_instance_form;

/**
 * Functional test for customfield_multiselect
 *
 * @package   customfield_multiselect
 * @copyright  2020 CALL Learning 2020 - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plugin_test extends \advanced_testcase {
    /** @var stdClass[] */
    private $courses = [];
    /** @var \core_customfield\category_controller */
    private $cfcat;
    /** @var \core_customfield\field_controller[] */
    private $cfields;
    /** @var \core_customfield\data_controller[] */
    private $cfdata;

    /**
     * Tests set up.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $this->cfcat = $generator->create_category();

        $this->cfields[1] = $generator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'multiselect',
            'configdata' => ['options' => "a\nb\nc"]]
        );
        $this->cfields[2] = $generator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield2', 'type' => 'multiselect',
            'configdata' => ['required' => 1,
            'options' => "a\nb\nc"]]
        );
        $this->cfields[3] = $generator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield3', 'type' => 'multiselect',
            'configdata' => ['defaultvalue' => 'b',
            'options' => "a\nb\nc"]]
        );
        $this->cfields[4] = $generator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield3', 'type' => 'multiselect',
            'configdata' => ['defaultvalue' => "b,c",
            'options' => "a\nb\nc"]]
        );

        $this->courses[1] = $this->getDataGenerator()->create_course();
        $this->courses[2] = $this->getDataGenerator()->create_course();
        $this->courses[3] = $this->getDataGenerator()->create_course();

        $this->cfdata[1] = $generator->add_instance_data($this->cfields[1], $this->courses[1]->id, [0]);
        $this->cfdata[2] = $generator->add_instance_data($this->cfields[1], $this->courses[2]->id, [1]);

        $this->setUser($this->getDataGenerator()->create_user());
    }

    /**
     * Test for initialising field and data controllers
     *
     * @covers \customfield_multiselect\field_controller
     * @covers \customfield_multiselect\data_controller
     */
    public function test_initialise(): void {
        $f = \core_customfield\field_controller::create($this->cfields[1]->get('id'));
        $this->assertTrue($f instanceof field_controller);

        $f = \core_customfield\field_controller::create(0, (object) ['type' => 'multiselect'], $this->cfcat);
        $this->assertTrue($f instanceof field_controller);

        $d = \core_customfield\data_controller::create($this->cfdata[1]->get('id'));
        $this->assertTrue($d instanceof data_controller);

        $d = \core_customfield\data_controller::create(0, null, $this->cfields[1]);
        $this->assertTrue($d instanceof data_controller);
    }

    /**
     * Test for configuration form functions
     *
     * Create a configuration form and submit it with the same values as in the field
     *
     * @covers \customfield_multiselect\field_controller
     */
    public function test_config_form(): void {
        $this->setAdminUser();
        $submitdata = (array) $this->cfields[1]->to_record();
        $submitdata['configdata'] = $this->cfields[1]->get('configdata');

        $submitdata = \core_customfield\field_config_form::mock_ajax_submit($submitdata);
        $form = new \core_customfield\field_config_form(null, null, 'post', '', null, true, $submitdata, true);
        $form->set_data_for_dynamic_submission();
        $this->assertTrue($form->is_validated());
        $form->process_dynamic_submission();
    }

    /**
     * Test for instance form functions
     *
     * @covers \customfield_multiselect\field_controller
     */
    public function test_instance_form(): void {
        global $CFG;
        require_once($CFG->dirroot . '/customfield/tests/fixtures/test_instance_form.php');
        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        // First try to submit without required field.
        $submitdata = (array) $this->courses[1];
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $this->assertFalse($form->is_validated());

        // Now with required field.
        $submitdata['customfield_myfield2'] = "1";
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $this->assertTrue($form->is_validated());

        $data = $form->get_data();
        $this->assertNotEmpty($data->customfield_myfield1);
        $this->assertNotEmpty($data->customfield_myfield2);
        $handler->instance_form_save($data);
    }

    /**
     * Test for instance form functions and check submitted values
     *
     * @covers \customfield_multiselect\field_controller
     */
    public function test_instance_form_values(): void {
        global $CFG;
        require_once($CFG->dirroot . '/customfield/tests/fixtures/test_instance_form.php');
        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        // Now with required field.
        $submitdata['customfield_myfield2'] = [1, 2];
        $submitdata['customfield_myfield2'] = [1, 2];
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $this->assertTrue($form->is_validated());

        $data = $form->get_data();
        $this->assertNotEmpty($data->customfield_myfield1);
        $this->assertNotEmpty($data->customfield_myfield2);
        $handler->instance_form_save($data);
    }

    /**
     * Test for data_controller::get_value and export_value
     *
     * @covers \customfield_multiselect\data_controller
     */
    public function test_get_export_value(): void {
        $this->assertSame("0", $this->cfdata[1]->get_value());
        $this->assertIsString($this->cfdata[1]->get_value());
        $this->assertEquals('a', $this->cfdata[1]->export_value());

        // Field without data but with a default value.
        $d = \core_customfield\data_controller::create(0, null, $this->cfields[3]);
        $this->assertSame("1", $d->get_value());
        $this->assertIsString($d->get_value());
        $this->assertEquals('b', $d->export_value());

        // Field without data but with a default value.
        $d = \core_customfield\data_controller::create(0, null, $this->cfields[4]);
        $this->assertSame("1,2", $d->get_value());
        $this->assertIsString($d->get_value());
        $this->assertEquals('b, c', $d->export_value());
    }

    /**
     * Test for data_controller::set_value.
     *
     * @covers \customfield_multiselect\data_controller
     */
    public function test_set_value_accepts_array_and_string(): void {
        $data = \core_customfield\data_controller::create(0, null, $this->cfields[1]);

        $data->set_value([1, 2]);
        $this->assertSame('1,2', $data->get('value'));

        $data->set_value('0,2');
        $this->assertSame('0,2', $data->get('value'));
    }

    /**
     * Test backup receives a scalar value, including empty multiselect data.
     *
     * @covers \customfield_multiselect\field_controller
     */
    public function test_backup_value_is_scalar_csv_string(): void {
        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        $fields = $handler->get_instance_data_for_backup($this->courses[1]->id);
        $field = $this->get_backup_field_by_shortname($fields, 'myfield1');
        $this->assertSame('0', $field['value']);
        $this->assertIsString($field['value']);

        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $generator->add_instance_data($this->cfields[1], $this->courses[3]->id, []);

        $fields = $handler->get_instance_data_for_backup($this->courses[3]->id);
        $field = $this->get_backup_field_by_shortname($fields, 'myfield1');
        $this->assertSame('', $field['value']);
        $this->assertIsString($field['value']);
    }

    /**
     * Test course backup and restore of multiselect custom field values.
     *
     * @covers \customfield_multiselect\data_controller::backup_define_structure
     * @covers \customfield_multiselect\data_controller::restore_define_structure
     */
    public function test_backup_and_restore(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/customfield/tests/fixtures/test_instance_form.php');

        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        $submitdata = (array) $this->courses[1];
        $submitdata['customfield_myfield1'] = [1, 2];
        $submitdata['customfield_myfield2'] = [0];
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $this->assertTrue($form->is_validated());

        $data = $form->get_data();
        $this->assertNotEmpty($data->customfield_myfield1);
        $this->assertNotEmpty($data->customfield_myfield2);
        $handler->instance_form_save($data);

        $cf1data = $DB->get_record(
            'customfield_data',
            ['instanceid' => $this->courses[1]->id, 'fieldid' => $this->cfields[1]->get('id')],
            '*',
            MUST_EXIST
        );
        $this->assertSame('1,2', $cf1data->value);

        $backupid = $this->backup($this->courses[1]);
        $newcourseid = $this->restore($backupid, $this->courses[1], '_copy');

        $newcf1data = $DB->get_record(
            'customfield_data',
            ['instanceid' => $newcourseid, 'fieldid' => $this->cfields[1]->get('id')],
            '*',
            MUST_EXIST
        );
        $this->assertSame('1,2', $newcf1data->value);
        $newcf1controller = \core_customfield\data_controller::create($newcf1data->id);
        $this->assertSame('1,2', $newcf1controller->get_value());
        $this->assertSame('b, c', $newcf1controller->export_value());

        $newcf2data = $DB->get_record(
            'customfield_data',
            ['instanceid' => $newcourseid, 'fieldid' => $this->cfields[2]->get('id')],
            '*',
            MUST_EXIST
        );
        $this->assertSame('0', $newcf2data->value);
        $newcf2controller = \core_customfield\data_controller::create($newcf2data->id);
        $this->assertSame('0', $newcf2controller->get_value());
        $this->assertSame('a', $newcf2controller->export_value());
    }

    /**
     * Returns a backed up field by shortname.
     *
     * @param array $fields
     * @param string $shortname
     * @return array
     */
    private function get_backup_field_by_shortname(array $fields, string $shortname): array {
        foreach ($fields as $field) {
            if ($field['shortname'] === $shortname) {
                return $field;
            }
        }

        $this->fail("Backup field '{$shortname}' was not found.");
    }


    /**
     * Data provider for {@see test_parse_value}
     *
     * @return array
     */
    public static function parse_value_provider(): array {
        return [
            ['Red', "0"],
            ['Blue|Green', "1,2"],
            ['Green| red', "0,2"],
            ['Mauve', ""],
        ];
    }

    /**
     * Test field parse_value method
     *
     * @param string $value
     * @param string $expected
     * @return void
     *
     * @dataProvider parse_value_provider
     * @covers \customfield_multiselect\field_controller
     */
    public function test_parse_value(string $value, string $expected): void {
        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $field = $generator->create_field([
            'categoryid' => $this->cfcat->get('id'),
            'type' => 'multiselect',
            'shortname' => 'mymultiselect',
            'configdata' => [
                'options' => "Red\nBlue\nGreen",
            ],
        ]);

        $this->assertSame($expected, $field->parse_value($value));
    }

    /**
     * Deleting fields and data
     *
     * @covers \customfield_multiselect\field_controller
     */
    public function test_delete(): void {
        $this->cfcat->get_handler()->delete_all();
    }

    /**
     * Backs a course up to temp directory.
     *
     * @param \stdClass $course Course object to backup
     * @return string ID of backup
     */
    protected function backup($course): string {
        global $USER, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

        // Turn off file logging, otherwise it can't delete the file (Windows).
        $CFG->backup_file_logger_level = \backup::LOG_NONE;

        // Do backup with default settings. MODE_IMPORT means it will just
        // create the directory and not zip it.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->get_plan()->get_setting('logs')->set_value(true);
        $backupid = $bc->get_backupid();

        $bc->execute_plan();
        $bc->destroy();

        return $backupid;
    }

    /**
     * Restores a course from temp directory.
     *
     * @param string $backupid Backup id
     * @param \stdClass $course Original course object
     * @param string $suffix Suffix to add after original course shortname and fullname
     * @return int New course id
     * @throws \restore_controller_exception
     */
    protected function restore(string $backupid, $course, string $suffix): int {
        global $USER, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Do restore to new course with default settings.
        $newcourseid = \restore_dbops::create_new_course(
            $course->fullname . $suffix,
            $course->shortname . $suffix,
            $course->category
        );
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('logs')->set_value(true);
        $rc->get_plan()->get_setting('users')->set_value(true);

        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }
}
