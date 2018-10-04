<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

$pid = $module->getSystemSetting("portal_pid");
DEFINE(PROJECT_PID, $pid);
$user = USERID;

global $pid, $user;
global $user_role_names;//
global $project_display_fields;

require_once ($module->getModulePath() . "pages/sRAP_setup.php");
require_once ($module->getModulePath() . "pages/sRAP_header_classes.php");
require_once ($module->getModulePath() . "classes/sRAP_instances.php");
require_once ($module->getModulePath() . "classes/sRAP_utilities.php");

$record_id = isset($_GET['record_id']) && !empty($_GET['record_id']) ? $_GET['record_id'] : null;
$action = isset($_POST['action']) && !empty($_POST['action']) ? $_POST['action'] : null;

$module->emLog("Action: $action for record $record_id", $_POST);

// These are all repeating instruments
if (($action == "users") or ($action == "redcap") or ($action == "request") or ($action == "finance")) {

    // Retrieve POST data
    $data_to_save = $_POST;
    unset($data_to_save["action"]);
    $instance_id = isset($_POST['instance']) && !empty($_POST['instance']) ? $_POST['instance'] : null;
    unset($data_to_save["instance"]);
    $instrument = isset($_POST['instrument']) && !empty($_POST['instrument']) ? $_POST['instrument'] : null;
    unset($data_to_save["instrument"]);

    // Convert checkbox data to format needed to save.  Save format is u_role => {"0", "1", "0", "0"}
    if ($action == 'users') {

        // Fix the user role checkboxes
        $user_roles = array();
        foreach ($user_role_names as $user_role => $role) {
            if ($data_to_save[$role] == 'on') {
                $user_roles[] = "1";
            } else {
                $user_roles[] = "0";
            }
            unset($data_to_save[$role]);
        }
        $data_to_save = array_merge($data_to_save, array("u_role" => $user_roles, $instrument . '_complete' => 1));

    } else {
        // Add instrument verifed status to data and save
        $data_to_save = array_merge($data_to_save, array($instrument . '_complete' => 2));
    }

    saveRepeatingForm($record_id, $instrument, $instance_id, $data_to_save);

    header("Refresh:0");
    return;

} else if ($action == "project") {

    // Retrieve POST data to save and add instrument complete status
    $data_to_save = $_POST;
    $instrument = $data_to_save["instrument"];
    $data_to_save = array_merge(array("id" => intval($record_id), $instrument . '_complete' => 2), $data_to_save);
    unset($data_to_save["action"]);
    unset($data_to_save["instrument"]);

    // This is an update to the project instrument - only non-repeating instrument
    // Values entered from the modal will overwrite values stored in the database - even if blanked out.
    $return = REDCap::saveData($pid, 'json', json_encode(array($data_to_save)));
    if (isset($results["errors"]) and !empty($results["errors"])) {
        $module->emError("Not able to save project data for record $record_id", $return);
    } else {
        $module->emLog("Successfully saved project data for record $record_id", $data_to_save);
    }

    header("Refresh:0");
    return;

} else if ($action == "initialize_request") {

    // Retrieve the instrument data for this instance so we can initialize the modal before displaying
    $instance_id = isset($_POST['instance']) && !empty($_POST['instance']) ? $_POST['instance'] : null;
    $instrument = isset($_POST['instrument']) && !empty($_POST['instrument']) ? $_POST['instrument'] : null;

    $repeating_form = new sRAP_instances($pid, $instrument);
    $repeating_form->loadData($record_id);
    $data = $repeating_form->getInstanceById($record_id, $instance_id);
    if ($data == false) {
        $module->emError("Unable to retrieve instance $instance_id for record $record_id and instrument $instrument", $repeating_form->last_error_message);
    } else {
        $module->emLog("Successfully retrieved instance $instance_id for record $record_id and instrument $instrument by $user", $data);
    }

    print json_encode($data);
    return;

} else if ($action == 'initialize_project') {

    $data = REDCap::getData($pid, "json", $record_id, $project_display_fields);

    print $data;
    return;

} else if ($action == "delete") {

    // Delete this instance
    $instance_id = isset($_POST['instance']) && !empty($_POST['instance']) ? $_POST['instance'] : null;
    $instrument = isset($_POST['instrument']) && !empty($_POST['instrument']) ? $_POST['instrument'] : null;
    $module->emLog("This is the pid in delete: $pid");

    $repeating_form = new sRAP_instances($pid, $instrument);
    $return = $repeating_form->deleteInstance($record_id, $instance_id);
    if ($return == false) {
        $module->emError("Unable to delete instance $instance_id for record $record_id and instrument $instrument", $repeating_form->last_error_message);
    } else {
        $module->emLog("Successfully deleted instance $instance_id for record $record_id and instrument $instrument by $user");
    }

    header("Refresh:0");
    return;
}

function displayResearchProject() {
    global $pid, $record_id;

    $html = null;
    if (is_null($record_id) or empty($record_id) or $record_id == 'null') {
        // if the record_id is null, we are creating a new record.
        //$html .= newResearchProject();
        $html .= "ERROR - newResearchProject";
    } else {
        $requests = get_Requests($pid, $record_id);
        $users = get_Users($pid, $record_id);
        $redcap = get_REDCap($pid, $record_id);
        $funding = get_Funding($pid, $record_id);

        $html .= get_ProjectHeader($pid, $record_id);
        $html .= '<br><br>';
        $html .= '<ul class="nav nav-tabs flex-column flex-lg-row" id="projectTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="request-tab" data-toggle="tab" href="#requests" role="tab" aria-controls="requests" aria-selected="true">
                        <b>Requests</b>  ' . $requests["size"] . '</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="redcap-tab" data-toggle="tab" href="#redcap" role="tab" aria-controls="redcap" aria-selected="false">
                        <b>REDCap Projects</b>  ' . $redcap["size"] . '</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="users-tab" data-toggle="tab" href="#users" role="tab" aria-controls="users" aria-selected="false">
                        <b>Users</b>  ' . $users["size"] . '</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="financial-tab" data-toggle="tab" href="#financial" role="tab" aria-controls="financial" aria-selected="false">
                        <b>Financial Information</b>  ' . $funding["size"] . '</a>
                    </li>
                </ul>
                <div class="tab-content" id="projectTabContent">
                    <div class="tab-pane fade show active" id="requests" role="tabpanel" aria-labelledby="request-tab"><br>';

        $html .= $requests["html"];
        $html .= '</div>
                  <div class="tab-pane fade" id="redcap" role="tabpanel" aria-labelledby="redcap-tab"><br>';
        $html .= $redcap["html"];
        $html .= '</div>
                  <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab"><br>';
        $html .= $users["html"];
        $html .= '</div>
                  <div class="tab-pane fade" id="financial" role="tabpanel" aria-labelledby="financial-tab"><br>';
        $html .= $funding["html"];
        $html .= '</div>
                  </div>';
    }
    return $html;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <title>Research Projects</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?php echo $module->getUrl("pages/sRAP.css") ?>" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</head>

<body>

<?php echo getPageHeader(); ?>

<div id="background" class="background">

    <div class="container">
        <div class="panel">
            <div class="panel-heading">
            </div>
            <div class="panel-body">
                <div id="research">
                    <?php echo displayResearchProject(); ?>
                </div>
            </div>
        </div>

        <!-- Edit Project Modal -->
        <div class="modal fade bd-example-modal-sm" id="editProject" tabindex="-1" role="dialog" aria-labelledby="editProject" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                            <h6 class="modal-title" align="left">Edit Project</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate method="post">
                            <input name="action" value="project" hidden>
                            <input name="instrument" value="research_project" hidden>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Principal Investigator:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_pi_firstname" id="rp_pi_firstname" placeholder="First Name" required>
                                <input type="text" class="form-control" name="rp_pi_lastname" id="rp_pi_lastname" placeholder="Last Name" required>
                            </div>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Email:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_pi_email" id="rp_pi_email" placeholder="ex: xyz@stanford.edu" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Phone:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_pi_phone" id="rp_pi_phone" placeholder="ex: (xxx)xxx-xxxx" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Department:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_pi_department" id="rp_pi_department" placeholder="Department Name" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Sunet ID:</div>
                                    <input type="text" class="form-control" name="rp_pi_sunetid" id="rp_pi_sunetid" placeholder="sunetID" required>
                                </div>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Short Description:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_name_short" id="rp_name_short" placeholder="Short Description" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Detailed Description:</div>
                                </div>
                                <textarea class="form-control" name="rp_description" id="rp_description" placeholder="Detailed Description" required></textarea>
                            </div>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Funding Status:</div>
                                </div>
                                <select name="rp_funding_status" id="rp_funding_status">
                                    <?php echo getSelectOptions('rp_funding_status', false); ?>
                                </select>
                            </div>

                            <div>
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Expected Start Date:</div>
                                    <input type="text" class="form-control" name="rp_start_date" id="rp_start_date" placeholder="ex: 2018-09-25" required>
                                </div>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Expected End Date:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_end_date" id="rp_end_date" placeholder="ex: 2020-09-25">
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">IRB Number:</div>
                                </div>
                                <input type="text" class="form-control" name="rp_irb_number" id="rp_irb_number" placeholder="ex: 12345" >
                            </div>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">IRB Status:</div>
                                </div>
                                <select name="rp_irb_status" id="rp_irb_status">
                                    <?php echo getSelectOptions('rp_irb_status', false); ?>
                                </select>
                            </div>
                            <br>
                            <button class="btn btn-secondary" type="submit">Submit</button>
                            <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- End Project Modal -->

        <!-- Edit Funding Modal -->
        <div class="modal fade bd-example-modal-sm" id="editfunding_information" tabindex="-1" role="dialog" aria-labelledby="editFunding" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">Funding Information</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate method="post">
                            <input name="action" value="finance" hidden>
                            <input name="instance" id="funding_instance_id" hidden>
                            <input name="instrument" id="funding_instrument" hidden>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Financial Contact:</div>
                                    <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" placeholder="First Name" value="<?php echo getCurrentValue('billing_first_name'); ?>" required>
                                </div>
                                <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" placeholder="Last Name" value="<?php echo getCurrentValue('billing_last_name'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Email Address:</div>
                                    <input type="email" class="form-control" name="billing_email" id="billing_email" placeholder="Email Address" value="<?php echo getCurrentValue('billing_email'); ?>" required>
                                </div>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Phone Number:</div>
                                </div>
                                <input type="text" class="form-control" name="billing_phone" id="billing_phone" placeholder="Phone Number" value="<?php echo getCurrentValue('billing_phone'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">PTA Charge Number:</div>
                                </div>
                                <input type="text" class="form-control" name="billing_pta" id="billing_pta" placeholder="Charge Number" value="<?php echo getCurrentValue('billing_pta'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">PTA Expiration Date:</div>
                                </div>
                                <input type="text" class="form-control" name="billing_pta_date" id="billing_pta_date" placeholder="i.e. 2020-09-01 (Y-M-D)" value="<?php echo getCurrentValue('billing_pta_date'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">iLab Service Number:</div>
                                </div>
                                <input type="text" class="form-control" name="billing_ilab_service_id" id="billing_ilab_service_id" placeholder="iLab Acct Number" value="<?php echo getCurrentValue('billing_ilab_service_id'); ?>" required>
                            </div>
                            <br>
                            <button class="btn btn-secondary" type="submit">Submit</button>
                            <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- End Funding Modal -->

        <!-- Add/Edit User -->
        <div class="modal fade bd-example-modal-sm" id="editusers" tabindex="-1" role="dialog" aria-labelledby="editusers" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">User Information</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate method="post">
                            <input name="action" value="users" hidden>
                            <input name="instance" id="users_instance_id" hidden>
                            <input name="instrument" id="users_instrument" hidden>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">User's First/Last Name:</div>
                                </div>
                                <input type="text" class="form-control" name="u_firstname" id="u_firstname" placeholder="First Name" value="<?php echo getCurrentValue('u_firstname'); ?>" required>
                                <input type="text" class="form-control" name="u_lastname" id="u_lastname" placeholder="Last Name" value="<?php echo getCurrentValue('u_lastname'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">User's sunetID:</div>
                               </div>
                                <input type="text" class="form-control" name="u_sunet" id="u_sunet" placeholder="SunetID" value="<?php echo getCurrentValue('u_sunet'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">User's E-mail:</div>
                                    <input type="email" class="form-control" name="u_email" id="u_email" placeholder="Email Address" value="<?php echo getCurrentValue('u_email'); ?>" required>
                                </div>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">User's Phone:</div>
                                </div>
                                <input type="phone" class="form-control" name="u_phone" id="u_phone" placeholder="(xxx) xxx-xxxx" value="<?php echo getCurrentValue('u_phone'); ?>" required>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">User's Status:</div>
                               </div>
                                <select name="u_status" id="u_status">
                                    <?php echo getSelectOptions('u_status'); ?>
                                </select>
                            </div>
                            <fieldset class="form-group">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">User's Role:</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <?php echo getSelectOptions('u_role'); ?>
                                    </div>
                                </div>
                            </fieldset>
                            <button class="btn btn-secondary" type="submit">Submit</button>
                            <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add/Edit User -->

        <!-- Add/Edit Redcap Projects -->
        <div class="modal fade bd-example-modal-sm" id="editredcap_projects" tabindex="-1" role="dialog" aria-labelledby="editredcap_projects" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">REDCap Projects</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate method="post">
                            <input name="action" value="redcap" hidden>
                            <input name="instance" id="redcap_instance_id" hidden>
                            <input name="instrument" id="redcap_instrument" hidden>

                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Project ID:</div>
                                </div>
                                <input type="text" class="form-control" name="redcap_pid" id="redcap_pid" required>
                            </div>
                            <br>
                            <button class="btn btn-secondary" type="submit">Submit</button>
                            <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>   <!-- modal-content  -->
            </div>
        </div>
        <!-- End Add/Edit Redcap Project -->

        <!-- Add/Edit Requests -->
        <div class="modal fade bd-example-modal-sm" name="editrequests" id="editrequests" tabindex="-1" role="dialog" aria-labelledby="editrequests" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">Requests</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" novalidate method="post">
                            <input name="action" value="request" hidden>
                            <input name="instance" id="request_instance_id" hidden>
                            <input name="instrument" id="request_instrument" hidden>

                            <div>
                                <h6>These fields on top are automatically filled in from Salesforce and are not editable here.  They are shown for informational purposes only.</h6>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Case Number:</div>
                                </div>
                                <input type="text" class="form-control" name="r_case_num" id="r_case_num"  readonly>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Initial Request Date:</div>
                                </div>
                                <input type="text" class="form-control" name="r_date" id="r_date" readonly>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Request Status:</div>
                                </div>
                                <input type="text" class="form-control" name="r_status" id="r_status" readonly>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Last Updated On:</div>
                                </div>
                                <input type="text" class="form-control" name="r_last_updated_on" id="r_last_updated_on" readonly>
                            </div>
                            <br>
                            <div>
                                <h6>The following fields are available for modification.</h6>
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Requestor:</div>
                                </div>
                                <input type="text" class="form-control" name="r_requestor" id="r_requestor" >
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Case Owner:</div>
                                </div>
                                <input type="text" class="form-control" name="r_owner" id="r_owner" >
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Request Description:</div>
                                </div>
                                <input type="text" class="form-control" name="r_description" id="r_description" >
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">REDCap Project ID (if applicable):</div>
                                </div>
                                <input type="text" class="form-control" name="r_redcap_pid" id="r_redcap_pid">
                            </div>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">New Comment:</div>
                                </div>
                                <textarea class="form-control" name="r_comment" id="r_comment" rows="3" placeholder="Enter a new comment for this case..."></textarea>
                            </div>
                            <br>
                            <div class="align-content-center" role="group" aria-label="Submit/Close buttons">
                                <button class="btn btn-secondary" type="submit">Submit</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add/Edit Requests -->

        <!-- Delete Instance Modal -->
        <div class="modal fade bd-example-modal-sm" id="delete_instance" tabindex="-1" role="dialog" aria-labelledby="delete_instance" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">Are you sure?</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post">
                            <input name="action" value="delete" hidden>
                            <input name="instance" id="delete_instance_id" hidden>
                            <input name="instrument" id="delete_instrument" hidden>

                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" value="" required>This will permanently delete this data - are you sure?
                                </label>
                            </div>
                            <br>
                            <button class="btn btn-secondary" type="submit">Submit</button>
                            <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>   <!-- modal-content  -->
            </div>
        </div>
        <!-- Delete Instance Modal -->


    </div>
</div>

</body>
</html>

<script>

    // Disable form submissions if there are invalid fields
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Capture modal so we can save the instance and instrument to delete
    $('#delete_instance').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var instance = button.data('instance');
        var instrument = button.data('instrument');
        document.getElementById("delete_instance_id").value = instance;
        document.getElementById("delete_instrument").value = instrument;
    })

    // Capture modal so we can load the initial values into the modal for this project
    $('#editProject').on('show.bs.modal', function (event) {
        srap.initialProjectForm();
    })

    // Capture modal so we can set the instance ID for repeating forms and initialize the form
    $('#editrequests').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var instrument = button.data('instrument');
        var instance = button.data('instance');
        document.getElementById("request_instance_id").value = instance;
        document.getElementById("request_instrument").value = instrument;

        // Make an API call to retrieve instrument data to initialize modal
        srap.initialRequestForm(instrument, instance);
    })

    // Capture modal so we can set the instance ID for repeating forms and initialize the form
    $('#editredcap_projects').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var instrument = button.data('instrument');
        var instance = button.data('instance');
        document.getElementById("redcap_instance_id").value = instance;
        document.getElementById("redcap_instrument").value = instrument;

        // Make an API call to retrieve instrument data to initialize modal
        srap.initialRequestForm(instrument, instance);
    })

    // Capture modal so we can set the instance ID for repeating forms and initialize the form
    $('#editusers').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var instrument = button.data('instrument');
        var instance = button.data('instance');
        document.getElementById("users_instance_id").value = instance;
        document.getElementById("users_instrument").value = instrument;

        // Make an API call to retrieve instrument data to initialize modal
        srap.initialRequestForm(instrument, instance);
    })

    // Capture modal so we can set the instance ID for repeating forms and initialize the form
    $('#editfunding_information').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var instrument = button.data('instrument');
        var instance = button.data('instance');
        document.getElementById("funding_instance_id").value = instance;
        document.getElementById("funding_instrument").value = instrument;

        // Make an API call to retrieve instrument data to initialize modal
        srap.initialRequestForm(instrument, instance);
    })


    var srap = srap || {};

    srap.initialRequestForm = function (instrument, instance) {
       // Load PI Info and go look for projects
        $.ajax({
            type: "POST",
            datatype: "html",
            async: false,
            data: {
                "action"     : "initialize_request",
                "instrument" : instrument,
                "instance"   : instance
            },
            success:function(html) {
            },
            error:function(jqXhr, textStatus, errorThrown) {
                console.log("Error in initialRequestForm: ", jqXHR, textStatus, errorThrown);
            }

        }).done(function (data) {
            // Parse the json data and call the routine that will put the form data into the correct fields
            if (data == 'false') {
           } else {
                var init_data = JSON.parse(data);
                if (instrument == 'users') {
                    srap.initializeUserInstrument(init_data);
                } else if (instrument == 'requests') {
                    srap.initializeRequestInstrument(init_data);
                } else if (instrument == 'funding_information') {
                    srap.initializeFundingInstrument(init_data);
                } else if (instrument == 'redcap_projects') {
                    srap.initializeRedcapInstrument(init_data);
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log("Failed in initialRequestForm for instrument " + instrument + " and instance " + instance);
        });
    }

    srap.initializeUserInstrument = function (data) {
        document.getElementById("u_firstname").value = data.u_firstname;
        document.getElementById("u_lastname").value = data.u_lastname;
        document.getElementById("u_email").value = data.u_email;
        document.getElementById("u_phone").value = data.u_phone;
        document.getElementById("u_status").value = data.u_status;
        document.getElementById("u_sunet").value = data.u_sunet;
        for (var i = 0; i < 3; i++) {
            if (data.u_role[i] == 1) {
                var field = "u_role___" + i;
                document.getElementById(field).checked = 'on';
            }
        }
   }

    srap.initializeRequestInstrument = function (data) {
        document.getElementById("r_case_num").value = data.r_case_num;
        document.getElementById("r_date").value = data.r_date;
        document.getElementById("r_status").value = data.r_status;
        document.getElementById("r_last_updated_on").value = data.r_last_updated_on;
        document.getElementById("r_requestor").value = data.r_requestor;
        document.getElementById("r_owner").value = data.r_owner;
        document.getElementById("r_description").value = data.r_description;
        document.getElementById("r_redcap_pid").value = data.r_redcap_pid;
        document.getElementById("r_comment").value = data.r_comment;
    }

    srap.initializeFundingInstrument = function (data) {
        document.getElementById("billing_first_name").value = data.billing_first_name;
        document.getElementById("billing_last_name").value = data.billing_last_name;
        document.getElementById("billing_email").value = data.billing_email;
        document.getElementById("billing_phone").value = data.billing_phone;
        document.getElementById("billing_pta").value = data.billing_pta;
        document.getElementById("billing_pta_date").value = data.billing_pta_date;
        document.getElementById("billing_ilab_service_id").value = data.billing_ilab_service_id;
    }

    srap.initializeRedcapInstrument = function (data) {
        document.getElementById("redcap_pid").value = data.redcap_pid;
    }

    srap.initialProjectForm = function () {

        // Load PI Info and go look for projects
        $.ajax({
            type: "POST",
            datatype: "html",
            async: false,
            data: {
                "action"     : "initialize_project"
            },
            success:function(html) {
            },
            error:function(jqXhr, textStatus, errorThrown) {
                console.log("Error in initialProjectForm: ", jqXHR, textStatus, errorThrown);
            }

        }).done(function (returned_data) {
            // Parse the json data and call the routine that will put the form data into the correct fields
            var data = JSON.parse(returned_data)[0];
            document.getElementById("rp_pi_firstname").value = data.rp_pi_firstname;
            document.getElementById("rp_pi_lastname").value = data.rp_pi_lastname;
            document.getElementById("rp_pi_email").value = data.rp_pi_email;
            document.getElementById("rp_pi_phone").value = data.rp_pi_phone;
            document.getElementById("rp_pi_department").value = data.rp_pi_department;
            document.getElementById("rp_pi_sunetid").value = data.rp_pi_sunetid;
            document.getElementById("rp_name_short").value = data.rp_name_short;
            document.getElementById("rp_description").value = data.rp_description;
            document.getElementById("rp_funding_status").value = data.rp_funding_status;
            document.getElementById("rp_start_date").value = data.rp_start_date;
            document.getElementById("rp_end_date").value = data.rp_end_date;
            document.getElementById("rp_irb_number").value = data.rp_irb_number;
            document.getElementById("rp_irb_status").value = data.rp_irb_status;
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log("Failed in initialProjectForm");
        });
    }


</script>