<?php

namespace customfield_multiselect\task;


class multiselect_field_change extends \core\task\scheduled_task {

	 public function get_name() {
        return get_string('field_task', 'customfield_multiselect');
    }

    function execute() {
        global $CFG,$DB;
        require_once $CFG->dirroot . '/user/lib.php';

        //Firstly get all cusotmfields with multiselect
        $all_multiselect_fields = $DB->get_records('customfield_field',array('type'=>'multiselect'));

        foreach($all_multiselect_fields as $field){
        	//Get all courses with data for this field
        	$five_mins_ago = time() - 300;
        	if($field->timemodified >= $five_mins_ago){
        		$field_with_data = $DB->get_records('customfield_data',array('fieldid'=>$field->id));

	        	foreach($field_with_data as $data){
	        		//Check if this has already been added
	        		if($entry = $DB->get_record('customfield_multiselect',array('fieldid'=>$field->id, 'courseid'=>$data->instanceid))){
		        		$courseid = $data->instanceid;
		        		$value = $entry->data;
		        		$values = explode(",", $value);

		        		$configdata = $field->configdata;
		        		$config_op = json_decode($configdata);
		        		$field_options = $config_op->options;
		        		$options = explode("\r\n", $field_options);

		        		$valuesaarr = array();

		        		foreach($values as $multioption){
		        			$key = array_search($multioption, $options);
		        			array_push($valuesaarr, $key);
		        		}
		        		$data->value = implode(",",$valuesaarr);
		        		$DB->update_record('customfield_data', $data);
	        		}
	        	}

        	}
        }
	}
}