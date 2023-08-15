<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_customfield_multiselect_upgrade($oldversion) {
    global $CFG, $DB;

    $result = true;
    $dbman = $DB->get_manager();

    if ($oldversion < 2023020803) {

        // Define table customfield_multiselect to be created.
        $table = new xmldb_table('customfield_multiselect');

        // Adding fields to table customfield_multiselect.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '5', null, null, null, null);
        $table->add_field('fieldid', XMLDB_TYPE_INTEGER, '5', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table customfield_multiselect.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for customfield_multiselect.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2023020803, 'customfield', 'multiselect');
    }



    return $result;
}

