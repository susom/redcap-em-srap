<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;
use \ExternalModules;

require_once ($module->getModulePath() . "pages/sRAP_setup.php");

function filterProjects($pid, $requestor=null) {

    global $user, $module;
    $id_fields = array("id");

    if (is_null($requestor) or empty($requestor)) {
        $filterBy = $user;
    } else {
        $filterBy = $requestor;
    }

    // Also see if there projects where this person is a user
    $filter = '[u_sunet] = "' . $filterBy . '"';
    $module->emDebug("This is the filter for projects: " . $filter);
    $project_ids = REDCap::getData($pid, "json", null, $id_fields, null, null, false, false, false, $filter);
    $module->emDebug("List of projects: " . $project_ids);
    $results1 = json_decode($project_ids);

    // Also see if there projects where this person is the PI
    $filter = '[rp_pi_sunetid] = "' . $filterBy . '"';
    $module->emDebug("This is the filter for PIs: " . $filter);
    $project_ids = REDCap::getData($pid, "json", null, $id_fields, null, null, false, false, false, $filter);
    $module->emDebug("List of projects: " . $project_ids);
    $results2 = json_decode($project_ids);
    $results = array_merge($results1, $results2);

    // Make an array of pids
    $proj_ids = array();
    foreach($results as $pid) {
        $proj_ids[] = $pid->id;
    }

    return $proj_ids;
}

function getBadge($size) {
    // Create a badge with the size of the table
    $html = '<span class="badge badge-secondary">' . $size . '</span>';
    return $html;
}

function get_Projects($pid, $record_id, $project_display_fields) {

    // Retrieve the research project data
    $data = REDCap::getData($pid, "json", $record_id, $project_display_fields, null, null, false, false, false, null, true);
    if ($data == false) {
        return $data;
    } else {
        return json_decode($data, true);
    }
}

function get_ProjectHeader($pid, $record_id) {

    global $project_display_fields, $module;

    $html = null;
    $irb_column1 = null;
    $irb_column2 = null;

    // Get Project data to display
    $project_data = get_Projects($pid, $record_id, $project_display_fields);

    $html .= '<br><div class="container">';
    $html .= '<table class="table table-sm" cellspacing="2" width="100%">';
    $html .= '<tr colspan="4"><h5>'. $project_data[0]["rp_name_short"] . '';
    $html .= '<a class="btn-sm" data-toggle="modal" data-target="#editProject" data-record="'. $record_id . '">';
    $html .= '<i class="far fa-edit"></i>';
    $html .= '</a></h5></i>';
    $html .= '</tr>';

    if (isset($project_data[0]["rp_description"])) {
        $html .= '<tr><td colspan="4"><b>Project Description: </b>' . $project_data[0]["rp_description"] . '</td></tr>';
    }

    // Setup each row
    $html .= '<tr>';
    $html .= '<td class="col-md-auto"><b>Principal Investigator</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_pi_firstname"]) ? '&nbsp;' : $project_data[0]["rp_pi_firstname"]) .
             ' ' . (empty($project_data[0]["rp_pi_lastname"]) ? '&nbsp;' : $project_data[0]["rp_pi_lastname"]) . '</td>';
    $html .= '<td class="col-md-auto"><b>Funding Status</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_funding_status"]) ? '&nbsp;' : $project_data[0]["rp_funding_status"]) . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td class="col-md-auto"><b>Email</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_pi_email"]) ? '&nbsp;' :  $project_data[0]["rp_pi_email"]) . '</td>';
    $html .= '<td class="col-md-auto"><b>Estimated Start Date</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_start_date"]) ? '&nbsp;' : $project_data[0]["rp_start_date"]) . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td class="col-md-auto"><b>Phone</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_pi_phone"]) ? '&nbsp;' : $project_data[0]["rp_pi_phone"]) . '</td>';
    $html .= '<td class="col-md-auto"><b>Estimated End Date</td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_end_date"]) ? '&nbsp;' : $project_data[0]["rp_end_date"]) . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td class="col-md-auto"><b>Department</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_pi_department"]) ? '&nbsp;' : $project_data[0]["rp_pi_department"]) . '</td>';
    if ($project_data[0]["rp_type"] == 'IRB required') {
        $html .= '<td class="col-md-auto"><b>IRB Number</b></td>';
        $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_irb_number"]) ? '&nbsp;' : $project_data[0]["rp_irb_number"]) . '</td>';
    } else if ($project_data[0]["rp_type"] == 'IRB NOT required') {
        $html .= '<td class="col-md-auto"><b>IRB Not Required</b></td>';
    }
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td class="col-md-auto"><b>Sunet ID</b></td>';
    $html .= '<td class="col-md-auto">'. (empty($project_data[0]["rp_pi_sunetid"]) ? '&nbsp;' : $project_data[0]["rp_pi_sunetid"]) . '</td>';
    if ($project_data[0]["rp_type"] == 'IRB required') {
        $html .= '<td class="col-md-auto"><b>IRB Status</b></td>';
        $html .= '<td class="col-md-auto">' . (empty($project_data[0]["rp_irb_status"]) ? '&nbsp;' : $project_data[0]["rp_irb_status"]) . '</td>';
    }
    $html .= '</tr>';

    $html .= '</table>';
    $html .= '</div>';

    return $html;
}

function get_Funding($pid, $record_id) {

    global $funding_instrument, $funding_display_fields;
    $html = null;

    // Create instrument class and retrieve the table headers on the research project
    $proj_funding = new sRAP_instances($pid, $funding_instrument);
    $header = $proj_funding->getDisplayHeaders($funding_display_fields);
    $proj_funding->loadData($record_id);
    $found_funding = $proj_funding->getAllInstancesFlat($record_id, $funding_display_fields);

    // Create a badge that displays the number of funding instances
    $badge = getBadge($found_funding["size"]);

    // Get the data table of Users
    $html .= $proj_funding->renderTable($header, $found_funding["data"], 'Funding Source');

    return array("size" => $badge, "html" => $html);
}

function get_Users($pid, $record_id) {

    global $user_instrument, $user_display_fields;
    $html = null;

    // Retrieve the table headers on the research project
    $proj_user = new sRAP_instances($pid, $user_instrument);
    $header = $proj_user->getDisplayHeaders($user_display_fields);
    $proj_user->loadData($record_id);
    $found_users = $proj_user->getAllInstancesFlat($record_id, $user_display_fields);

    // Convert the u_role checkboxes and selections to readable labels
    $data_with_labels = array();
    foreach($found_users["data"] as $user) {
        $user["u_role"] = $proj_user->getCheckboxesLabels("u_role", $user["u_role"]);
        $user["u_status"] = $proj_user->getSelectionLabels("u_status", $user["u_status"]);
        $data_with_labels[] = $user;
    }

    // Create a badge displaying the number of user records
    $badge = getBadge($found_users["size"]);
    $html .= $proj_user->renderTable($header, $data_with_labels, 'User');

    return array("size" => $badge, "html" => $html);
}

function get_REDCap($pid, $record_id) {

    global $redcap_instrument, $redcap_display_fields;

    $html = null;

    // Retrieve the table headers on the research project

    $projects = new sRAP_instances($pid, $redcap_instrument);
    $header = $projects->getDisplayHeaders($redcap_display_fields);
    $projects->loadData($record_id);
    $found_projects = $projects->getAllInstancesFlat($record_id, $redcap_display_fields);

    // Redcap Projects are a little different.  We are looking up the project title and status
    // from the entered pid.
    $data = array();
    foreach ($found_projects["data"] as $project) {
        $record = array();
        $sql = "SELECT app_title, status FROM redcap_projects where project_id = " . $project["redcap_pid"];
        $q = db_query($sql);
        $row = db_fetch_assoc($q);
        if (!is_null($row)) {
            $record['redcap_pid'] = $project["redcap_pid"];
            $record['app_title'] = $row['app_title'];
            $record['status'] = ($row['status'] == 0 ? 'Development' : 'Production');
        } else {
            $record['redcap_pid'] = $project["redcap_pid"] . ' - Unknown PID';
            $record['app_title'] = null;
            $record['status'] = null;
        }
        $record['id'] = $project['id'];
        $data[] = $record;
    }

    $header = array_merge($header, array("app_title" => "Title", "status" => "Production Status"));

    // Create a badge which displays the number of instances of redcap projects
    $badge = getBadge($found_projects["size"]);
    $html .= $projects->renderTable($header, $data, 'Redcap Project');

    return array("size" => $badge, "html" => $html);
}

function get_Requests($pid, $record_id) {

    global $request_instrument, $request_display_fields;
    $html = null;

    // Retrieve the table headers on the research project
    $request = new sRAP_instances($pid, $request_instrument);
    $header = $request->getDisplayHeaders($request_display_fields);
    $request->loadData($record_id);
    $found_requests = $request->getAllInstancesFlat($record_id, $request_display_fields);

    // Create a badge that displays the number of instances of requests
    $badge = getBadge($found_requests["size"]);
    $html .= $request->renderTable($header, $found_requests["data"], 'Request');

    return array("size" => $badge, "html" => $html);
}

function get_IRBBySunetID($sunetid) {
    $tokenMgnt = \ExternalModules\ExternalModules::getModuleInstance('irb_lookup');
    return $tokenMgnt->getIRBAllBySunetID($sunetid);
}

function get_IRBByIRBNum($irb_num) {
    $tokenMgnt = \ExternalModules\ExternalModules::getModuleInstance('irb_lookup');
    return $tokenMgnt->getAllIRBData($irb_num);
}

function getNextId($pid, $id_field, $arm_event = NULL, $prefix = '') {
    $q = REDCap::getData($pid,'array',NULL,array($id_field), $arm_event);

    if ( !empty($prefix) ) {
        // A prefix is supplied - first check if it is used
        if ( !isset($q[$prefix]) ) {
            // Just use the plain prefix as the new record name
            return $prefix;
        } else {
            // Lets start numbering at 2 until we find an open record id:
            $i = 2;
            do {
                $next_id = $prefix . "-" . $i;
                $i++;
            } while (isset($q[$next_id]));
            return $next_id;
        }
    } else {
        // No prefix
        $new_id = 1;
        foreach ($q as $id=>$event_data) {
            if (is_numeric($id) && $id >= $new_id) $new_id = $id + 1;
        }
        return $new_id;
    }
}


function getCurrentValue($field) {
    global $pid, $record_id, $module;
    if (isset($record_id) and !is_null($record_id)) {
        $field_data = REDCap::getData($pid, 'json', $record_id, array($field));
        //$module->emLog("This is the field $field for record $record_id and returned value: ", $field_data);
    } else {
        //$module->emLog("This is the field $field for null record");
        return null;
    }
}


function getSelectOptions($field, $current_value=null) {
    global $pid, $popover_content;
    $html = "";

    // Retrieve the data dictionary for this field so we know the available options. This get retrieves the labels
    $data_dict = REDCap::getDataDictionary($pid, 'array', true, $field);
    $choices = array_map('trim', explode('|', $data_dict[$field]["select_choices_or_calculations"]));

    // Create the html with these options and optionally select the one that is already selected
    if (($data_dict[$field]['field_type'] == 'checkbox')  and ($field == "u_role")) {
        foreach ($choices as $choice) {
            $split_choice = array_map('trim', explode(',', $choice));
            $html .= '<div class="custom-control custom-checkbox">';
            $field_name = $field . '___' . $split_choice[0];
            if (isset($current_value) and ($split_choice[1] == $current_value)) {
               $html .= '<input type="checkbox" class="custom-control-input" name="' . $field_name . '" id="' . $field_name . ' checked>';
            } else {
                $html .= '<input type="checkbox" class="custom-control-input" name="' . $field_name . '" id="' . $field_name . '">';
            }
            $html .= '<label class="custom-control-label" for="' . $field_name . '"><a href="#" title="' . $split_choice[1] . '" data-toggle="popover" data-placement="top" data-content="' . $popover_content[$split_choice[0]] . '">' . $split_choice[1] . '</a></label><br>';
            $html .= '</div>';

        }
    } else if (($data_dict[$field]['field_type'] == 'radio') or ($data_dict[$field]['field_type'] == 'dropdown')) {
        if (is_null($current_value)) {
            $html .= '<option value="" selected disabled>-- Select one ---</option>';
        } else {
            $html .= '<option value="" disabled>   --- Select one ---  </option>';
        }
        foreach ($choices as $choice) {
            $split_choice = array_map('trim', explode(',', $choice));
            if (isset($current_value) and ($split_choice[0] == $current_value)) {
                $html .= '<option value="' . $split_choice[0] . '" selected>' . $split_choice[1] . '</option>';
            } else {
                $html .= '<option value="' . $split_choice[0] . '">' . $split_choice[1] . '</option>';
            }
        }
    }

    return $html;
}

function saveRepeatingForm($record_id, $instrument, $instance_id, $data)
{
    global $module, $pid;

    $data = array();
    $record_id = null;
    $instrument = null;
    $instance_id = null;

    $module->emLog("For pid $pid instrument $instrument, instance_id $instance_id and record_id $record_id: data", $data);
    $repeating_form = new sRAP_instances($pid, $instrument);
    if (is_null($instance_id)) {
        $instance_id = $repeating_form->getNextInstanceId($record_id);
        $module->emLog("New instance id $instance_id");
    }
    $return = $repeating_form->saveInstance($record_id, $data, $instance_id);
    $module->emLog("Return from saveInstance: " . $return);
    if ($return == false) {
        $module->emError("Error saving instrument $instrument, instance $instance_id for record $record_id", $repeating_form->last_error_message);
    } else {
        $module->emLog("Saved instrument $instrument in record $record_id and instance $instance_id", $data);
    }
}

function retrieveUserInfo($new_sunetid) {

    $user_data = array();
    if (!is_null($new_sunetid)) {
        // Call lookup for this user
        $spl = ExternalModules\ExternalModules::getModuleInstance('stanford_person_lookup');
        $spl_results = $spl->personLookup($new_sunetid);

        // If Lookup was successful, save the data retrieved
        if ($spl_results["success"] == true) {
            $user_data = array("u_firstname" => $spl_results["user"]["first_name"],
                "u_lastname" => $spl_results["user"]["last_name"],
                "u_email" => $spl_results["user"]["email"],
                //"rp_pi_phone"        => $spl_results["user"]["telephonenumber"],
                "u_sunet" => $spl_results["user"]["sunet"],
                "u_affiliation" => $spl_results["user"]["affiliation"],
                "u_department" => $spl_results["user"]["department"]
            );
        } else {
            // If the user was not found in lookup, just save the sunetID
            $user_data = array("u_sunet" => $new_sunetid);
        }
    }

    return $user_data;
}
