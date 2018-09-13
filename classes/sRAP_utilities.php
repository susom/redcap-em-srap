<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

function filterProjects($pid, $requestor=null) {

    global $message, $user, $module;
    $id_fields = array("id");
    $module->emLog("requestor: "  . $requestor);

    if (is_null($requestor) or empty($requestor)) {
        $filterBy = $user;
    } else {
        $filterBy = $requestor;
    }

    // Also see if there projects where this person is a user
    $filter = '[u_sunet] = "' . $filterBy . '"';
    $project_ids = REDCap::getData($pid, "json", null, $id_fields, null, null, false, false, false, $filter);
    $results1 = json_decode($project_ids);

    // Also see if there projects where this person is the PI
    $filter = '[rp_pi_sunetid] = "' . $filterBy . '"';
    $project_ids = REDCap::getData($pid, "json", null, $id_fields, null, null, false, false, false, $filter);
    $results2 = json_decode($project_ids);
    $results = array_merge($results1, $results2);
    $module->emLog("list of projects: " . json_encode($results));

    // Make an array of pids
    $proj_ids = array();
    foreach($results as $pid) {
        $proj_ids[] = $pid->id;
    }

    return $proj_ids;
}

function getDisplayHeaders($pid, $instrument, $display_fields=null) {

    $instruments = array("id", $instrument);

    $ddictionary = REDCap::getDataDictionary($pid, "array", false, null, $instruments, false);

    $header = array();
    foreach ($display_fields as $field) {
        $header[] = $ddictionary[$field]["field_label"];
    }

    return $header;
}

function getDisplayData($pid, $record_id, $display_fields) {

    // Retrieve the data on this research project. Retrieving in
    $data = REDCap::getData($pid, "json", $record_id, $display_fields, null, null, false, false, false, null, true);

    return $data;
}

function getBadge($display_label, $size, $instrument, $record_id) {
    // Create a badge with the size of the table
    $html = '<button type="button" class="btn btn-lg btn-outline-dark" id="custom-badge" data-toggle="collapse" data-target="#' . $instrument .
        '" aria-expanded="false" aria-controls="collapse' . $instrument . '">' .
        $display_label . 's&nbsp;<span class="badge badge-light">' . $size . '</span></button>';
    return $html;

}

function get_Funding($pid, $record_id) {

    $instrument = "funding_information";
    $display_fields = array("id", "billing_ilab_service_id", "billing_first_name", "billing_last_name", "billing_email", "billing_pta", "billing_pta_date");

    // Retrieve the research project data
    $data = REDCap::getData($pid, "json", $record_id, $display_fields, null, null, false, false, false, null, true);
    if ($data <> false) {
        $funding = json_decode($data, true);

        $funding_html = '<div><h6><b>Funding</b><a data-record="' . $record_id . '" id="edit-style" data-value="grants" data-toggle="modal" data-target="#editFunding"><img src="' . APP_PATH_IMAGES . 'pencil_small3.png"/></a></h6></div>'
            . '<div>PTA Number: ' . $funding[0]["billing_pta"] . '</div>'
            . '<div>PTA Expiration Date: ' . $funding[0]["billing_pta_date"] . '</div>'
            . '<div>PTA Contact: ' . $funding[0]["billing_first_name"] . ' ' . $funding[0]["billing_last_name"] . '</div>'
            . '<div>Email: ' . $funding[0]["billing_email"] . '</div>'
            . '<div>iLab Acct No: ' . $funding[0]["billing_ilab_service_id"] . '</div>';
        return $funding_html;
    } else {
        return null;
    }
}


function get_Projects($pid, $record_id) {

    $instrument = "research_project";
    $display_fields = array("id", "rp_name_short", "rp_type", "rp_irb_number", "rp_irb_status", "rp_funding_status",
        "rp_start_date", "rp_end_date", "rp_pi_first_name", "rp_pi_last_name", "rp_pi_department");

    // Retrieve the research project data
    $data = REDCap::getData($pid, "json", $record_id, $display_fields, null, null, false, false, false, null, true);
    if ($data == false) {
        return data;
    } else {
        return json_decode($data, true);
    }
}

function get_ProjectHeader($pid, $record_id) {

    $html = null;
    $funding_html = null;

    // Get Project data to display
    $project_data = get_Projects($pid, $record_id);
    if ($project_data <> false) {
        // Decide what to display about the IRB depending on status
        if ($project_data[0]["rp_type"] == 'IRB required') {
            $irb_display = '<div>IRB Number: ' . $project_data[0]["rp_irb_number"] . '</div>
                            <div>IRB Status: ' . $project_data[0]["rp_irb_status"] . '</div>';
        } else {
            $irb_display = "<div>IRB Not Required</div>";
        }
    }

    // Get project general information
    $html .= '<br><div></div><h5>' . $project_data[0]["rp_name_short"] . '</h5></div><br>'
            . '<div class="row"><div class="col-sm-6">'
            . '<h6><b>Project Information</b><a data-record="' . $record_id . '" id="edit-style" data-value="grants" data-toggle="modal" data-target="#editProject"><img src="' . APP_PATH_IMAGES . 'pencil_small3.png"/></a></h6>'
            . '<div>Principal Investigator: ' . $project_data[0]["rp_pi_first_name"] . ' ' . $project_data[0]["rp_pi_last_name"] . '</div>'
            . '<div>Department: ' . $project_data[0]["rp_pi_department"] . '</div>'
            . $irb_display
            . '<div>Start Date: ' . $project_data[0]["rp_start_date"] . '- End Date: ' . $project_data[0]["rp_end_date"] . '</div>'
            . '</div><div class="col-sm-6">'
            . $funding_html
            . '</div></div>'
        ;

    return $html;
}

function get_Users($pid, $record_id) {

    $html = null;
    $instrument = "users";
    $display_fields = array("id", "u_firstname", "u_lastname", "u_role", "u_status");

    // Retrieve the table headers on the research project
    $header = getDisplayHeaders($pid, $instrument, $display_fields);

    $user = new sRAP_Instances($pid, $instrument);
    $user->loadData($record_id);
    $return = $user->getAllInstancesFlat($record_id, $display_fields);

    // Get the collapsed data table of Users
    //if ($return["size"] <> 0) {
        $dt = new displayTable();
        $html .= $dt->renderTable($instrument, $header, $return["data"], $record_id);
    //}

    return $html;
}

function get_REDCap($pid, $record_id) {

    $html = null;
    $instrument = "redcap_projects";
    $display_fields = array("id", "redcap_pid", "redcap_name", "redcap_proj_status", "redcap_proj_create_date");

    // Retrieve the table headers on the research project
    $header = getDisplayHeaders($pid, $instrument, $display_fields);

    $projects = new sRAP_Instances($pid, $instrument);
    $projects->loadData($record_id);
    $return = $projects->getAllInstancesFlat($record_id, $display_fields);

    if ($return["size"] <> 0) {
        $dt = new displayTable();
        $html .= $dt->renderTable($instrument, $header, $return["data"], $record_id);
    }

    return $html;
}

function get_Requests($pid, $record_id) {

    $html = null;
    $instrument = "requests";
    $display_fields = array("id", "r_date", "r_requestor", "r_ticket_id", "r_description");

    // Retrieve the table headers on the research project
    $header = getDisplayHeaders($pid, $instrument, $display_fields);

    $request = new sRAP_Instances($pid, $instrument);
    $request->loadData($record_id);
    $return = $request->getAllInstancesFlat($record_id, $display_fields);

    if ($return["size"] <> 0) {
        $dt = new displayTable();
        $html .= $dt->renderTable($instrument, $header, $return["data"], $record_id);
    }

    return $html;
}

function get_IRBBySunetID($sunetid) {
    $tokenMgnt = \ExternalModules\ExternalModules::getModuleInstance('irb');
    return $tokenMgnt->getIRBAllBySunetID($sunetid);
}

function get_IRBByIRBNum($irb_num) {
    $tokenMgnt = \ExternalModules\ExternalModules::getModuleInstance('irb');
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
