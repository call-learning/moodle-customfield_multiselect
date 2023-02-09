<?php

namespace customfield_multiselect\task;


class multiselect_sync extends \core\task\scheduled_task {

	 public function get_name() {
        return get_string('task', 'customfield_multiselect');
    }

    function execute() {
        global $CFG,$DB;
        require_once $CFG->dirroot . '/user/lib.php';

		$five_mins_ago = time() - 300;
        $sql="SELECT * FROM {course} WHERE timemodified > ".$five_mins_ago;
        $courses = $DB->get_records_sql($sql);
        print_r($courses);
        if(!empty($courses)){
			foreach($courses as $course){
				$DB->delete_records('customfield_multiselect', array('courseid' => $course->id));
			}       	
        }



        
        //Firstly get all cusotmfields with multiselect
        $all_multiselect_fields = $DB->get_records('customfield_field',array('type'=>'multiselect'));

        foreach($all_multiselect_fields as $field){
        	//Get all courses with data for this field
        	$field_with_data = $DB->get_records('customfield_data',array('fieldid'=>$field->id));
        	foreach($field_with_data as $data){
        		//Check if this has already been added
        		if(!$DB->get_record('customfield_multiselect',array('fieldid'=>$field->id, 'courseid'=>$data->instanceid))){
	        		$courseid = $data->instanceid;
	        		$value = $data->value;
	        		$values = explode(",", $value);
	        		$configdata = $field->configdata;
	        		$config_op = json_decode($configdata);
	        		$field_options = $config_op->options;
	        		$options = explode("\r\n", $field_options);

	        		$valuesaarr = array();

	        		foreach($values as $multioption){
	        			array_push($valuesaarr, $options[$multioption]);
	        		}
	        		$data = new \stdclass();
	        		$data->courseid = $courseid;
	        		$data->fieldid = $field->id;
	        		$data->data = implode(",",$valuesaarr);
	        		$DB->insert_record('customfield_multiselect', $data);
        		}
        	}
        }
	}
}