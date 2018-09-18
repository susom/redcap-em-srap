<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

// This is storing data into pid=13941. (For testing it is pid 30 on Lee Ann's localhost).
$module->emLog("Incoming request", json_encode($_POST));
$pid = $module->getSystemSetting("redcap_pid");
$saved_secret = $module->getSystemSetting("shared_secret");
$sent_secret = isset($_POST['ss']) && !empty($_POST['ss']) ? $_POST['ss'] : null;
if ($saved_secret != $sent_secret) {
    $module->emError("Shared secret is incorrect.", json_encode($_POST));
    header("HTTP/1.0 401 Unauthorized");
    exit;
}

// Currently, the request_id is the record_id but in version 2.0, it will be a unique id since there
// may be more requests for each record.
$request_id = isset($_POST['record_id']) && !empty($_POST['record_id']) ? $_POST['record_id'] : null;
$case_create_date = isset($_POST['created_on']) && !empty($_POST['created_on']) ? $_POST['created_on'] : null;
$case_num = isset($_POST['case_number']) && !empty($_POST['case_number']) ? $_POST['case_number'] : null;
$case_status = isset($_POST['status']) && !empty($_POST['status']) ? $_POST['status'] : null;
$case_owner = isset($_POST['owner']) && !empty($_POST['owner']) ? $_POST['owner'] : null;
$case_last_update = isset($_POST['last_updated_on']) && !empty($_POST['last_updated_on']) ? $_POST['last_updated_on'] : null;

$data = array(
    'record_id'                 => intval($request_id),
    'sf_case_created_on'        => $case_create_date,
    'sf_case_number'            => $case_num,
    'sf_case_status'            => $case_status,
    'sf_case_owner'             => $case_owner,
    'sf_case_last_updated_on'   => $case_last_update,
    'salesforce_case_complete'  => 2
);
$module->emLog("Saving data for record $request_id ", json_encode($data));

$return = REDCap::saveData($pid, 'json', json_encode(array($data)));

header("Context-type: application/json");
if (empty($return["errors"])) {
    header("HTTP/1.0 200 OK");
} else {
    $module->emError("Return: " . implode(',', $return));
    $module->emError("Return errors: " . implode(',', $return["errors"]));
    $module->emError("Return warnings: " . implode(',', $return["warnings"]));
    $module->emError("Return ids: " . implode(',', $return["ids"]));
    $module->emError("Return item_count: " . $return["item_count"]);
    header("HTTP/1.0 500 Internal Server Error");
}

?>


