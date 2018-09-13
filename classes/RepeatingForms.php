<?php
namespace Stanford\Utilities;

use \REDCap;
use \Project;

/*
[
    [record_id 1]
        [event_id]
            [non_repeat_field1]     => val1
            [non_repeat_field2]     => val2
            [repeat_field1]          => ''
            [repeat_field2]          => ''

        [repeating_instances]
            [event_id]
                [form_name]
                    [instance_id]
                        [non_repeat_field1]     => ''
                        [non_repeat_field2]     => ''
                        [repeat_field1]          => val1
                        [repeat_field2]          => val2
]

*/


/**
 * Class RepeatingForms
 * @package Stanford\Utilities
 *
 *
 *
 */
class RepeatingForms
{

    // Metadata
    private $Proj;
    private $pid;
    private $instrument;
    private $is_longitudinal;
    private $data_dictionary;
    private $fields;
    private $events_enabled = array();    // Array of event_ids where the instrument is enabled


    // Instance
    private $record_id;
    private $event_id;
    public $event_name;
    public $instance_id;
    private $instances;     // Array of valid instances
    public $data;
    public $data_loaded = false;

    private $last_error_message = null;    // Last error message


    public function __construct($pid, $instrument_name)
    {
        global $Proj;
        if ($Proj->project_id == $pid) {
            $this->Proj = $Proj;
        } else {
            $this->Proj = new Project($pid);
        }

        if (empty($Proj)) {
            $last_error_message = "Cannot determine project ID in RepeatingForms";
        }
        $this->pid = $pid;

        // Find the fields on this repeating instrument
        $this->instrument = $instrument_name;
        $this->data_dictionary = REDCap::getDataDictionary($pid, 'array', false, null, array($instrument_name));
        $this->fields = array_keys($this->data_dictionary);

        // Is this project longitudinal?
        $this->is_longitudinal = $this->Proj->longitudinal;

        // Retrieved events
        $all_events = $this->Proj->getRepeatingFormsEvents();

        // See which events have this form enabled
        foreach (array_keys($all_events) as $event) {
            $fields_in_event = REDCap::getValidFieldsByEvents($this->pid, $event, false);
            $field_intersect = array_intersect($fields_in_event, $this->fields);
            if (isset($field_intersect) && sizeof($field_intersect) > 0) {
                array_push($this->events_enabled, $event);
            }
        }
    }

    /**
     * This function will load data internally from the database using the record, event and optional
     * filter in the calling arguments here as well as pid and instrument name from the constructor
     *
     * @param $record_id
     * @param null $event_id
     * @param null $filter
     * @return None
     */
    public function loadData($record_id, $event_id=null, $filter=null)
    {
        $this->record_id = $record_id;
        $this->event_id = $event_id;

        // Filter logic will only return matching instances
        $return_format = 'array';
        $repeating_forms = REDCap::getData($this->pid, $return_format, array($record_id), $this->fields, $this->event_id, NULL, false, false, false, $filter, true);

        // If this is a classical project, we are not adding event_id.
        foreach (array_keys($repeating_forms) as $record) {
            foreach ($this->events_enabled as $event) {
                if (!is_null($repeating_forms[$record]["repeat_instances"][$event]) and !empty($repeating_forms[$record_id]["repeat_instances"][$event])) {
                    if ($this->is_longitudinal) {
                        $this->data[$record_id][$event] = $repeating_forms[$record_id]["repeat_instances"][$event][$this->instrument];
                    } else {
                        $this->data[$record_id] = $repeating_forms[$record_id]["repeat_instances"][$event][$this->instrument];
                    }
                }
            }
        }

        $this->data_loaded = true;
    }

    /**
     * This function will return the data retrieved based on a previous loadData call. All instances of an
     * instrument fitting the criteria specified in loadData will be returned.  For longitudinal projects, the
     * format of returned data is [record1][event][instance1][instance2]...
     *                            [record2][event][instance1]...
     *
     * For classic projects, the format of the returned data is
     *                            [record1][instance1][instance2] ...
     *                            [record2][instance1]....
     *
     * @param $record_id
     * @param null $event_id
     * @return array (of data loaded from loadData) or null if an error occurred
     */
    public function getAllInstances($record_id, $event_id=null) {

        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error_message = "You must supply an event_id for longitudinal projects in " . __FUNCTION__;
            return null;
        }

        // Check to see if we have the correct data loaded. If not, load it.
        if ($this->data_loaded == false || $this->record_id != $record_id || $this->event_id != $event_id) {
            $this->loadData($record_id, $event_id, null);
        }

        return $this->data;
    }

    /**
     * This function will return one instance of data retrieved in dataLoad.
     *
     * @param $record_id
     * @param $instance_id
     * @param null $event_id
     * @return array (of instance data) or null if an error occurs
     */
    public function getInstanceById($record_id, $instance_id, $event_id=null)
    {
        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error_message = "You must supply an event_id for longitudinal projects in " . __FUNCTION__;
            return null;
        }

        // Check to see if we have the correct data loaded.
        if ($this->data_loaded == false || $this->record_id != $record_id || $this->event_id != $event_id) {
            $this->loadData($record_id, $event_id, null);
        }

        // If the record and optionally event match, return the data.
        if ($this->is_longitudinal) {
            if (!empty($this->data[$record_id][$event_id][$instance_id]) &&
                !is_null($this->data[$record_id][$event_id][$instance_id])) {
                return $this->data[$record_id][$event_id][$instance_id];
            } else {
                $this->last_error_message = "Instance number is invalid";
                return null;
            }
        } else {
            if (!empty($this->data[$record_id][$instance_id]) && !is_null($this->data[$record_id][$instance_id])) {
                return $this->data[$record_id][$instance_id];
            } else {
                $this->last_error_message = "Instance number is invalid";
                return null;
            }
        }
    }

    /**
     * This function will return the first instance_id for this record and optionally event. This function
     * does not return data.
     *
     * @param $record_id
     * @param null $event_id
     * @return int (instance number) or null (if an error occurs)
     */
     public function getFirstInstanceId($record_id, $event_id=null) {
        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error_message = "You must supply an event_id for longitudinal projects in " . __FUNCTION__;
            return null;
        }

        // Check to see if we have the correct data loaded.
        if ($this->data_loaded == false || $this->record_id != $record_id || $this->event_id != $event_id) {
            $this->loadData($record_id, $event_id, null);
        }

        // If the record and optionally event match, return the data.
        if ($this->is_longitudinal) {
            if (!empty(array_keys($this->data[$record_id][$event_id])[0]) &&
                !is_null(array_keys($this->data[$record_id][$event_id])[0])) {
                return array_keys($this->data[$record_id][$event_id])[0];
            } else {
                $this->last_error_message = "There are no instances in event $this->event_id for record $record_id " . __FUNCTION__;
                return null;
            }
        } else {
            if (!empty(array_keys($this->data[$record_id])[0]) && !is_null(array_keys($this->data[$record_id])[0])) {
                return array_keys($this->data[$record_id])[0];
            } else {
                $this->last_error_message = "There are no instances for record $record_id " . __FUNCTION__;
                return null;
            }
        }
    }

    /**
     * This function will return the last instance_id for this record and optionally event. This function
     * does not return data.
     *
     * @param $record_id
     * @param null $event_id
     * @return int | null (If an error occurs)
     */
    public function getLastInstanceId($record_id, $event_id=null) {

        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error_message = "You must supply an event_id for longitudinal projects in " . __FUNCTION__;
            return null;
        }

        // Check to see if we have the correct data loaded.
        if ($this->data_loaded == false || $this->record_id != $record_id || $this->event_id != $event_id) {
            $this->loadData($record_id, $event_id, null);
        }

        // If the record_ids (and optionally event_ids) match, return the data.
        if ($this->is_longitudinal) {
            $size = sizeof($this->data[$record_id][$event_id]);
            if ($size < 1) {
                $this->last_error_message = "There are no instances in event $event_id for record $record_id " . __FUNCTION__;
                return null;
            } else {
                return array_keys($this->data[$record_id][$event_id])[$size - 1];
            }
        } else {
            $size = sizeof($this->data[$record_id][$event_id]);
            if ($size < 1) {
                $this->last_error_message = "There are no instances for record $record_id " . __FUNCTION__;
                return null;
            } else {
                return array_keys($this->data[$record_id])[$size - 1];
            }
        }
    }


    /**
     * This function will return the next instance_id.  If there are no current instances, it will return 1.
     *
     * @param $record_id
     * @param null $event_id
     * @return int | null (if an error occurs)
     */
    public function getNextInstanceId($record_id, $event_id=null)
    {
        // If this is a longitudinal project, the event_id must be supplied.
        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error_message = "You must supply an event_id for longitudinal projects in " . __FUNCTION__;
            return null;
        }

        // Find the last instance and add 1 to it. If there are no current instances, return 1.
        $last_index = $this->getLastInstanceId($record_id, $event_id);
        if ($last_index == null) {
            return 1;
        } else {
            return ++$last_index;
        }
    }

    /**
     * This function will save an instance of data.  If the instance_id is supplied, it will overwrite
     * the current data for that instance with the suupplied data. If a instance_id is not given,
     * it will create a new instance of the data using the next instance_id.
     *
     * @param $record_id
     * @param $data
     * @param null $instance_id
     * @param null $event_id
     * @return true | null (if an error occurs)
     */
    public function saveInstance($record_id, $data, $instance_id = null, $event_id = null)
    {
        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error = "Event ID Required for longitudinal project in " . __FUNCTION__;
            return false;
        }

        // If the instance ID is null, get the next one because we are saving a new instance
        if (is_null($instance_id)) {
            $next_instance_id = $this->getNextInstanceId($record_id, $event_id);
        } else {
            $next_instance_id = $instance_id;
        }

        $new_instance[$record_id]["repeat_instances"][$event_id][$this->instrument][$next_instance_id] = $data;

        $return = REDCap::saveData($this->pid, 'array', $new_instance);
        if (!is_null($return["errors"])) {
            $this->last_error = "Problem saving instance $next_instance_id for record $record_id in project $this->pid. Returned: " . json_encode($return);
            return false;
        }

        return true;
    }

    // TBD: Not sure how to delete an instance ????
    public function deleteInstance($record_id, $instance_id, $event_id = null) {

        // If longitudinal and event_id = null, send back an error
        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error = "Event ID Required for longitudinal project in " . __FUNCTION__;
            return false;
        }

    }

    /**
     * This function will look for the data supplied in the given record/event and send back the instance
     * number if found.  The data supplied does not need to be all the data in the instance, just the data that
     * you want to search on.
     *
     * @param $needle
     * @param $record_id
     * @param null $event_id
     * @return int | null (if an error occurs)
     */
    public function exists($needle, $record_id, $event_id=null) {

        // Longitudinal projects need to supply an event_id
        if ($this->is_longitudinal && is_null($event_id)) {
            $this->last_error = "Event ID Required for longitudinal project in " . __FUNCTION__;
            return null;
        }

        // Check to see if we have the correct data loaded.
        if ($this->data_loaded == false || $this->record_id != $record_id || $this->event_id != $event_id) {
            $this->loadData($record_id, $event_id, null);
        }

        // Look for the supplied data in an already created instance
        $found_instance_id = null;
        $size_of_needle = sizeof($needle);
        if ($this->is_longitudinal) {
            foreach ($this->data[$record_id][$event_id] as $instance_id => $instance) {
                $intersected_fields = array_intersect_assoc($instance, $needle);
                if (sizeof($intersected_fields) == $size_of_needle) {
                    $found_instance_id = $instance_id;
                }
            }
        } else {
            foreach ($this->data[$this->record_id] as $instance_id => $instance) {
                $intersected_fields = array_intersect_assoc($instance, $needle);
                if (sizeof($intersected_fields) == $size_of_needle) {
                    $found_instance_id = $instance_id;
                }
            }
        }

        // Supplied data did not match any instance data
        if (is_null($found_instance_id)) {
            $this->last_error_message = "Instance was not found with the supplied data " . __FUNCTION__;
        }

        return $found_instance_id;
    }

}
