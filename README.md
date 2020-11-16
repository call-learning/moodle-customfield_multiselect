Multiselect Custom Field
========================

[![Build Status](https://travis-ci.org/call-learning/moodle-customfield_multiselect.svg?branch=master)](https://travis-ci.org/call-learning/moodle-customfield_multiselect)


This plugin is a new multiselect profile inspired from the existing select customfield (customfield/field/select)

It allows for several choices to be selected.
The data is stored in the database as comma separated values of option indexes.


NOTE
===
There is a very similar development made by https://github.com/devlionco a couple
of years ago. 
After a discussion (see https://tracker.moodle.org/browse/MDL-66321), the
form filter was changed to autocomplete as it made more sense. The "clear" button has been
removed.
The field should behave as the one developed on: https://github.com/devlionco/moodle-customfield_multiselect

There are a couple of differences though:

    * In this implementation, the base class is not the customfield_select data or field classes. The reason was that there were so many little differences in the 
    method implementations that we ended up overriding all methods from customfield_select\data_controller or field_controller. The disadvantage being that for example
    the new course grouping feature is not automatically supported.
    * We use 'value' instead of 'charvalue' for datastorage. This is down to the fact that we wanted to make sure there was no limit on the number of selected option.
    This can be reviewed if needed.


TODO
====
 * Allow to change the select into a series of cheboxes for small amounts of choices.
 * When values are removed from the list, should we re-index ?