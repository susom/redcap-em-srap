<?php
/**
 * Created by PhpStorm.
 * User: LeeAnnY
 * Date: 8/7/2018
 * Time: 12:48 PM
 */
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;
use \Stanford\Utilities;

require_once ($module->getModulePath() . "classes/RepeatingForms.php");

class sRAP_instances extends \Stanford\Utilities\RepeatingForms {

    private $pid;
    private $instrument;

    function __construct($pid, $instrument_name)
    {
        $this->pid = $pid;
        $this->instrument = $instrument_name;
        parent::__construct($pid, $instrument_name);
    }

    function getAllInstancesFlat($record_id, $display_fields, $event_id=null) {

        global $module;
        $instances = $this->getAllInstances($record_id, $event_id);

        $flat_results = array();
        $display_results = array();
        $id = array();
        foreach($instances[$record_id] as $key => $value) {
            $id["id"] = $key;
            $display_results = array_intersect_key($value, array_flip($display_fields));
            $flat_results[] = array_merge($id, $display_results);
        }

        return array("size" => sizeof($instances[$record_id]), "data" =>$flat_results);
    }
}
