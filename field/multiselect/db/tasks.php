<?php


defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'customfield_multiselect\task\multiselect_sync',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),
    array(
        'classname' => 'customfield_multiselect\task\multiselect_field_change',
        'blocking' => 0,
        'minute' => '/5',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),
);
