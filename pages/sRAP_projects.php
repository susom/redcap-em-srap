<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

global $pid, $user, $message;
$pid = $module->getSystemSetting("portal_pid");
//$user = USERID;
$user = "yasukawa";

require_once ($module->getModulePath() . "pages/sRAP_header_classes.php");
require_once ($module->getModulePath() . "classes/sRAP_instances.php");
require_once ($module->getModulePath() . "classes/displayTable.php");
require_once ($module->getModulePath() . "classes/sRAP_utilities.php");

$record_id = isset($_GET['record_id']) && !empty($_GET['record_id']) ? $_GET['record_id'] : null;

function displayResearchProject() {
    global $pid, $record_id, $instrument, $user_instrument;

    $html = null;
    if (is_null($record_id) or empty($record_id) or $record_id == 'null') {
        // if the record_id is null, we are creating a new record.
        $html .= newResearchProject();
    } else {
        $html .= get_ProjectHeader($pid, $record_id);
        $html .= "<br><br>";
        $html .=
                '<ul class="nav nav-tabs" id="projectTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="request-tab" data-toggle="tab" href="#requests" role="tab" aria-controls="requests" aria-selected="true">Requests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="redcap-tab" data-toggle="tab" href="#redcap" role="tab" aria-controls="redcap" aria-selected="false">REDCap Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="users-tab" data-toggle="tab" href="#users" role="tab" aria-controls="users" aria-selected="false">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="financial-tab" data-toggle="tab" href="#financial" role="tab" aria-controls="financial" aria-selected="false">Financial Information</a>
                    </li>
                </ul>
                <div class="tab-content" id="projectTabContent">
                    <div class="tab-pane fade show active" id="requests" role="tabpanel" aria-labelledby="request-tab"><br>';

        $html .= get_Requests($pid, $record_id);
        $html .= '</div>
                  <div class="tab-pane fade" id="redcap" role="tabpanel" aria-labelledby="redcap-tab"><br>';
        $html .= get_REDCap($pid, $record_id);
        $html .= '</div>
                  <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab"><br>';
        $html .= get_Users($pid, $record_id);
        $html .= '</div>
                  <div class="tab-pane fade" id="financial" role="tabpanel" aria-labelledby="financial-tab"><br>';
        $html .= get_Funding($pid, $record_id);
        $html .= '</div>
                </div>';
        /*
        // Retrieve the specified research project
        $html .= get_ProjectHeader($pid, $record_id);
        $html .= "<br><br>";
        $html .= get_Users($pid, $record_id);
        $html .= "<br><br>";
        $html .= get_REDCap($pid, $record_id);
        $html .= "<br><br>";
        $html .= get_Requests($pid, $record_id);
        */
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

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</head>

<body>

<?php echo getPageHeader(); ?>

<?php echo getMessage(); ?>

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
        <div class="modal fade bd-example-modal-lg" id="editProject" tabindex="-1" role="dialog" aria-labelledby="editProject" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">Edit Project</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Body
                    </div>
                    <div class="modal-footer">
                       <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Project Modal -->

        <!-- Edit Funding Modal -->
        <div class="modal fade bd-example-modal-lg" id="editFunding" tabindex="-1" role="dialog" aria-labelledby="editFunding" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">Edit Funding</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Body
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Funding Modal -->

        <!-- Add/Edit User -->
        <div class="modal fade bd-example-modal-lg" id="editusers" tabindex="-1" role="dialog" aria-labelledby="editusers" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" align="left">Users</h6>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Body
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add/Edit User -->

        <!-- Add/Edit Redcap Projects -->
        <div class="modal fade bd-example-modal-lg" id="editredcap_projects" tabindex="-1" role="dialog" aria-labelledby="editredcap_projects" aria-hidden="true">
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
                        Body
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add/Edit Redcap Project -->

        <!-- Add/Edit Requests -->
        <div class="modal fade bd-example-modal-lg" id="editrequests" tabindex="-1" role="dialog" aria-labelledby="editrequests" aria-hidden="true">
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
                        Body
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add/Edit Requests -->


    </div>
</div>

</body>
</html>