<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;
use \ExternalModules;

require_once ($module->getModulePath() . "pages/sRAP_setup.php");
require_once ($module->getModulePath() . "pages/sRAP_header_classes.php");
require_once ($module->getModulePath() . "classes/sRAP_utilities.php");

global $pid, $user, $user_instrument;

$user = USERID;
$pi_projects = null;

$pid = $module->getSystemSetting("portal_pid");
$module->emLog("this project doesnt even exist anymore?", $pid);
// DEFINE(PROJECT_PID, $pid);

$action = isset($_POST['action']) && !empty($_POST['action']) ? $_POST['action'] : null;
if ($action == 'pi_projects') {
    $pi_sunetid = isset($_POST['pi_sunetid']) && !empty($_POST['pi_sunetid']) ? $_POST['pi_sunetid'] : null;
    $pi_projects = null;
    $pi_last_name = null;
    $project_data = array();
    $spl = ExternalModules\ExternalModules::getModuleInstance('stanford_person_lookup');
    $spl_results = $spl->personLookup($pi_sunetid);
    if ($spl_results["success"] == true) {
        $pi_last_name = $spl_results["user"]["last_name"];
    }

    if ($action == 'pi_projects') {
        // Look for portal projects that are already created for this PI
        $module->emLog("Looking up portal projects for PI " . $pi_sunetid . " by " . $user);
        $display_fields = array("id", "rp_irb_number", "rp_type", "rp_name_short");
        $proj_list = filterProjects($pid, $pi_sunetid);

        // If the PI has some portal projects already, display them
        if (!empty($proj_list)) {
            $proj = get_Projects($pid, $proj_list, $display_fields);
/*
            if ($list <> false) {
                $pi_projects .= '<div id="pi_portal_proj">';
                $pi_projects .= '<h4>Select the project you wish to join:</h4><br>';
                $pi_projects .= '<h6>Here is a list of the current research projects for ' . $pi_sunetid . ':</h6>';
                $pi_projects .= '<div class="dropright">';
                $pi_projects .= '<button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $pi_projects .= 'Research Projects';
                $pi_projects .= '</button>';
                $pi_projects .= '<div class="dropdown-menu" id="pi_projects" aria-labelledby="dropdownMenuButton">';
                foreach ($proj as $key => $value) {
                    $pi_projects .= '<a class="dropdown-item" value="' . $value["id"] . '">' . $value["rp_name_short"] . '</a>';
                }
                $pi_projects .= '<div class="dropdown-divider"></div>';
                $pi_projects .= '<a class="dropdown-item" value="None">NONE OF THE ABOVE</a>';
                $pi_projects .= '</div><div id="pi_project_selection"></div></div><br>';
                $module->emLog("This is the html for portal projects: ", $pi_projects);
            }
*/
            if ($proj <> false) {
                $pi_projects .= '<br><div id="pi_portal_proj">';
                $pi_projects .= '<h4>Select the project you wish to join:</h4>';
                if (is_null($pi_last_name)) {
                    $pi_projects .= '<h6>Here is a list of the current research projects for ' . $pi_sunetid . ':</h6>';
                } else {
                    $pi_projects .= '<h6>Dr ' . $pi_last_name . ' already has the following registered projects. Is your project one of these?</h6>';
                }
                $pi_projects .= '<select class="custom-select" id="pi_projects" onchange="selectedPortalProject(value)">';
                $pi_projects .= '<option value="" hidden>Please select one ...</option>';
                foreach ($proj as $key => $value) {
                    $pi_projects .= '<option class="custom-select" value="' . $value["id"] . '">' . $value["rp_name_short"] . '</option>';
                }
                $pi_projects .= '<option class="custom-select" disabled="disabled">───────────────────</option>';
                $pi_projects .= '<option class="custom-select" value="None">NONE OF THE ABOVE</option>';
                $pi_projects .= '</select><div id="pi_project_selection"></div></div><br>';
            }
        }

        // Now add the IRB Protocols for this PI
        $module->emLog("Looking up IRB projects for PI " . $pi_sunetid . " by " . $user);

        // Retrieve IRB numbers that this user is associated with that does not currently have a research project associated with it.
        $protocols = get_IRBBySunetID($pi_sunetid);
        $module->emLog("Protocols: " . json_encode($protocols));

        // If PI has valid IRBs, display them but keep them hidden for now
        if (!empty($protocols)) {
            $pi_projects .= '<br><div id="pi_irb_proj">';
            if (!empty($proj_list)) {
                $pi_projects .= '<h4>Is your project related to an IRB?</h4>';
            }
            if (is_null($pi_last_name)) {
                $pi_projects .= '<h6>IRBs for ' . $pi_sunetid . ':</h6>';
            } else {
                $pi_projects .= '<h6>Dr ' . $pi_last_name . ' has the following IRBs. Is your project one of these?</h6>';
            }
            $pi_projects .= '<select class="custom-select" id="pi_irbs" onchange="selectedIRBProject(value)">';
            $pi_projects .= '<option value="" hidden>Please select one ...</option>';
            foreach ($protocols as $key => $value) {
                $pi_projects .= '<option class="custom-select" value="' . $value["protocolNumber"] . '">' . $value["protocolTitle"] . '</option>';
            }
            $pi_projects .= '<option class="custom-select" disabled="disabled">───────────────────</option>';
            $pi_projects .= '<option value="None">NONE OF THE ABOVE</option>';
            $pi_projects .= '</select></div><div id="irb_project_selection"></div><br>';
        }

        // PI does not have portal projects or IRBs yet. Just display a message so user knows
        if (empty($proj_list) && empty($protocols)) {
            $pi_projects .= '<div id="no_pi_proj">The PI ' . $pi_sunetid . ' does not currently have any portal projects or IRBs. Please continue to create a new project.</div>';
        } else {
            $pi_projects .= '<div id="no_pi_proj">Please continue to create a new project.</div>';
            $pi_projects .= '<div id="join_pi_proj">Please continue to join the project.</div>';
            $pi_projects .= '<div id="irb_pi_proj">Please continue to create a new project from this IRB.</div><br><br>';
        }

        print $pi_projects;
        return;
    }

} else if ($action == 'process_request') {
    $proj_record_id = "";
    $instance_id = null;

    $module->emLog("POST: ", $_POST);
    $pi_sunetid = isset($_POST['pi_sunetid']) && !empty($_POST['pi_sunetid']) ? $_POST['pi_sunetid'] : null;
    $p_desc = isset($_POST['proj_description']) && !empty($_POST['proj_description']) ? $_POST['proj_description'] : null;
    $pi_proj = isset($_POST['portal_proj']) && !empty($_POST['portal_proj']) ? $_POST['portal_proj'] : null;
    $irb_proj = isset($_POST['irb_proj']) && !empty($_POST['irb_proj']) ? $_POST['irb_proj'] : null;
    $user_role_names = isset($_POST['user_role_names']) && !empty($_POST['user_role_names']) ? $_POST['user_role_names'] : null;
    $user_role_values = isset($_POST['user_role_values']) && !empty($_POST['user_role_values']) ? $_POST['user_role_values'] : null;

    // Convert user roles to correct format to save. i.e. "u_role" => [0,1,0,0]
    $user_roles = array();
    for ($i = 0; $i < count($user_role_names); $i++) {
        if ($user_role_values[$i] == 'true') {
            $user_roles[$i] = "1";
        } else {
            $user_roles[$i] = "0";
        }
    }

    // See if we are adding user to an existing project
    if ($pi_proj <> "None" && !is_null($pi_proj)) {
        $data = retrieveUserInfo($user);
        $module->emLog("Data to save: ", $data);
        $data = array_merge($data, array("u_role" => $user_roles, $user_instrument . '_complete' => 2));
        $module->emLog("After merge: ", $data);
        saveRepeatingForm($pi_proj, $user_instrument, $instance_id, $data);
        $module->emLog("After saving new user");
        $proj_record_id = $pi_proj;
    } else {
        // Get SPL info for PI
        $project_data = array();
        $spl = ExternalModules\ExternalModules::getModuleInstance('stanford_person_lookup');
        $spl_results = $spl->personLookup($pi_sunetid);

        if ($spl_results["success"] == true) {
            $project_data = array("rp_pi_firstname" => $spl_results["user"]["first_name"],
                                "rp_pi_lastname" => $spl_results["user"]["last_name"],
                                "rp_pi_email" => $spl_results["user"]["email"],
                                //"rp_pi_phone" => $spl_results["user"]["telephonenumber"],
                                "rp_pi_sunetid" => $spl_results["user"]["sunet"],
                                "rp_pi_affliation" => $spl_results["user"]["affiliation"],
                                "rp_pi_department" => $spl_results["user"]["department"]);
        } else {
            $module->emError("Could not retrieve person-lookup information for PI $pi_sunetid");
            $project_data = array("rp_pi_sunetid" => $pi_sunetid);
        }

        // If this project is associated with an IRB, add in the IRB number and description from the IRB
        $irb_data = array();
        if ($irb_proj <> "None" && !is_null($irb_proj)) {
            // Retrieve IRB information
            $protocols = get_IRBByIRBNum($irb_proj);
            if ($protocols["isPresent"] && $protocols["isValid"]) {
                $irb_data = array("rp_irb_number" => $irb_proj,
                                  "rp_name_short" => $protocols["protocolTitle"],
                                  "rp_irb_status" => 2 );           // Approved
            } else if ($protocols["isPresent"]) {
                $irb_data = array("rp_irb_number" => $irb_proj,
                                  "rp_name_short" => $protocols["protocolTitle"],
                                  "rp_irb_status" => 1 );           // Applied but not Approved
            } else {
                $irb_data = array("rp_irb_number" => $irb_proj,
                                  "rp_name_short" => $p_desc,
                                  "rp_irb_status" => 99 );           // Unknown
            }
        } else {
            $irb_data = array("rp_name_short" => $p_desc,
                              "rp_irb_status" => 99 );              // Unknown
        }
        $project_data = array_merge($project_data, $irb_data);

        // Create a new project and new user with the information above.
        $proj_record_id = getNextId($pid, "id");
        $record_info = array("id" => $proj_record_id,
                             "research_project_complete" => 1,  // Unverified
                             "id_complete" => 2);               // Complete
        $project_data = array_merge($record_info, $project_data);

        // Save a new record with this project info
        $results = REDCap::saveData($pid, 'json', json_encode(array($project_data)));
        if (isset($results["errors"]) and !empty($results["errors"])) {
            $module->emError("Error saving  new project: ", $results);
        } else {
            // Project was successfully created. Now add a user record.
            $data = retrieveUserInfo($user);
            $module->emLog("Data to save: ", $data);
            $data = array_merge($data, array("u_role" => $user_roles, $user_instrument . '_complete' => 2));
            $module->emLog("After merge: ", $data);
            //$return = saveRepeatingForm($proj_record_id, $user_instrument, $instance_id, $data);
            //$module->emLog("Return from saveRepeatingForm $return");
        }
    }

    $project_page_url = $module->getUrl('pages/sRAP_projects.php') . "&record_id=" . $proj_record_id;
    print $project_page_url;
    return;
}

function getUser() {
    global $user;
    return $user;
}

?>

<!doctype html>
<html>

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

<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
<body>

<!-- Top nav bar -->
<?php echo getPageHeader(); ?>

<div id="background" class="background">
    <div class="container" width="60%" id="index_container">
        <form id="ResearchProject">

            <!-- One "tab" for each step in the form: -->
            <div class="tab" id="tab0">
                <h5>Welcome to the new Research Portal!</h5>
                <p>
                    To facilitate better tracking, communication and support of your research projects, RIT is
                    requesting all projects have an entry in the research portal. From this portal, you
                    will be able to manage users, track issues, and communicate with RIT personnel.
                </p>
                <p>
                    To see your current list of research portal projects, please select from the Research Projects
                    link above.
                </p>
                <p>
                    To get started on a new research portal project, select Next.
                </p>
            </div>
            <div class="tab" id="tab1">
                <div>
                    <h5>Please help us determine if your research project is already registered: </h5>
                </div>
                <br>

                <div id="pi_info">
                    <p>
                        <label for="pi_sunetid"><b>Principal Investigator's sunetID whose project you will be working on:</b></label>
                        <input type="text" class="form-control" size="40%" name="pi_sunetid" id="pi_sunetid" placeholder="PI's sunetID..." required>
                        <div class="invalid-feedback">
                            * The PI sunetID is required.
                        </div>
                    </p>
                   <p>
                        <a><b>Enter a project title</b> (max. 70 characters)</a>
                        <input type="text" class="form-control" size="40%" maxlength="70" name="proj_desc" id="proj_desc" placeholder="Project Title ..." required>
                        <div class="invalid-feedback">
                            * A project title is required.
                        </div>
                    </p>
                    <p>
                        <input type="checkbox" id="pi_attest" value="checked" required>
                        <a><b>I attest that I am working with this Principal Investigator.</b></a><br>
                        <a><i>  (Please Note: The PI is able to view all users who agree to this attestation)</i></a>
                        <div class="invalid-feedback">
                            * Agreeing to this attestation is required to continue.
                        </div>
                    </p>
                    <p id="no_pi_sunetid">
                        **<a id="alert">You cannot continue until you enter a sunetID for the Principal Investigator.</a>
                    </p><br>
                    <p id="no_proj_description">
                        **<a id="alert">You cannot continue until you enter a project description.</a>
                    </p><br>
                    <p id="no_attestation">
                        **<a id="alert">You cannot continue until you agree to the attestation above.</a>
                    </p>
                    <br><br>
                 </div>
            </div> <!-- tab1 -->

            <div class="tab" id="tab2">
                <h5 id="description1"></h5>
                <div id="pi_proj">
                </div>
            </div>  <!-- tab 2 -->
            <div class="tab" id="tab3">
                <h5 id="description2"></h5>
                <input type = "text" name="user" id="user" value="<?php echo getUser()?>" hidden>
                <p>
                    <br><a ><b> What is your role(s) in this project </b ></a ><br>
                    <?php echo getSelectOptions("u_role"); ?>
               </p>
            </div> <!-- tab 3 -->

            <br>
            <div style="overflow:auto;">
                <div style="float:right;">
                    <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                    <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
                </div>
            </div>
            <!-- Circles which indicates the steps of the form: -->
            <div style="text-align:center;margin-top:10px;">
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
            </div>
        </form>
    </div>   <!-- End container -->
</div> <!-- End background -->

</body>
</html>

<script>
    var currentTab = 0; // Current tab is set to be the first tab (0)
    showTab(currentTab); // Display the current tab
    document.getElementById("no_attestation").style.display = "none";
    document.getElementById("no_pi_sunetid").style.display = "none";
    document.getElementById("no_proj_description").style.display = "none";


    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();
    });

    function selectedPortalProject(selection) {

        // If 'None of the above' is selected, unhide the IRB list if there is one.
        // If No IRB list, unhide the message which tells the user we will create a new portal project.
        document.getElementById("join_pi_proj").style.display = "none";
        document.getElementById("irb_pi_proj").style.display = "none";

        if (selection == 'None') {
            if (document.getElementById("pi_irb_proj")) {
                document.getElementById("pi_irb_proj").style.display = "inline";
            } else {
                document.getElementById("no_pi_proj").style.display = "inline";
            }
        } else {
            // We found the project the user wants to join
            document.getElementById("join_pi_proj").style.display = "inline";
            if (document.getElementById("pi_irb_proj")) {
                document.getElementById("pi_irb_proj").style.display = "none";
            }
        }
    }

    function selectedIRBProject(selection) {
        // Unhide the message which tells the user we will create a new portal project.
        document.getElementById("no_pi_proj").style.display = "none";
        document.getElementById("irb_pi_proj").style.display = "none";

        if (selection == 'None') {
            document.getElementById("no_pi_proj").style.display = "inline";
        } else {
            document.getElementById("irb_pi_proj").style.display = "inline";
        }
    }

    function showTab(n) {
        // This function will display the specified tab of the form...
        var x = document.getElementsByClassName("tab");
        x[n].style.display = "inline-block";
        //... and fix the Previous/Next buttons:
        if (n == 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n == (x.length - 1)) {
            document.getElementById("nextBtn").innerHTML = "Submit";
        } else {
            document.getElementById("nextBtn").innerHTML = "Next";
        }
        //... and run a function that will display the correct step indicator:
        fixStepIndicator(n)
    }

    function nextPrev(n) {
        // This function will figure out which tab to display
        var x = document.getElementsByClassName("tab");

        if (currentTab == 1) {
            var user = document.getElementById("user").value;
            var pi_sunetid = document.getElementById("pi_sunetid").value;
            var proj_description = document.getElementById("proj_desc").value;
            var attest = document.getElementById("pi_attest").checked;
            document.getElementById("no_attestation").style.display = "none";
            document.getElementById("no_pi_sunetid").style.display = "none";
            document.getElementById("no_proj_description").style.display = "none";
            document.getElementById("description1").innerHTML = proj_description;
            document.getElementById("description2").innerHTML = proj_description;

            // If the user is also the PI, select the PI checkbox
            if (user == pi_sunetid) {
                document.getElementById("u_role___0").checked = true;
            }

            // Make sure each of the fields has a reasonable value before proceeding
            if ((pi_sunetid.length < 2) || (proj_description.length < 2) || (attest == false)) {
                if (n > 0) {
                    currentTab = currentTab - n;
                }
                if (pi_sunetid.length < 2) {
                    document.getElementById("no_pi_sunetid").style.display = "inline";
                }
                if (proj_description.length < 2) {
                    document.getElementById("no_proj_description").style.display = "inline";
                }
                if (attest == false) {
                    document.getElementById("no_attestation").style.display = "inline";
                }
            } else {
                srap.getPIProjects(pi_sunetid);
            }
        }

        // Hide the current tab:
        x[currentTab].style.display = "none";
        // Increase or decrease the current tab by 1:
        currentTab = currentTab + n;
        // if you have reached the end of the form...
        if (currentTab >= x.length) {
            // ... the form gets submitted:
            srap.processRequest();
        } else {
            // Otherwise, display the correct tab:
            showTab(currentTab);
        }
    }

    function fixStepIndicator(n) {
        // This function removes the "active" class of all steps...
        var i, x = document.getElementsByClassName("step");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        //... and adds the "active" class on the current step:
        x[n].className += " active";
    }

    var srap = srap || {};

    srap.getPIProjects = function (pi_sunetid) {

        // Load PI Info and go look for projects
        $.ajax({
            type: "POST",
            datatype: "html",
            async: false,
            data: {
                "action": "pi_projects",
                "pi_sunetid": pi_sunetid
            },
            success:function(html) {
            },
            error:function(jqXhr, textStatus, errorThrown) {
                console.log("Error in findPIProjects: ", jqXHR, textStatus, errorThrown);
            }

        }).done(function (html) {
            if (html.length > 0 ) {
                document.getElementById("pi_proj").innerHTML = html;

                // Figure out what we are going to display.
                if (document.getElementById("pi_portal_proj")) {
                    // If the PI has portal projects, just display those first
                    if (document.getElementById("pi_irb_proj")) {
                        document.getElementById("pi_irb_proj").style.display = "none";
                    }
                    document.getElementById("no_pi_proj").style.display = "none";
                    document.getElementById("join_pi_proj").style.display = "none";
                    document.getElementById("irb_pi_proj").style.display = "none";
                } else if (document.getElementById("pi_irb_proj")) {
                    // If there are no portal projects but there are IRBs, just display the IRBs
                    document.getElementById("no_pi_proj").style.display = "none";
                    document.getElementById("join_pi_proj").style.display = "none";
                    document.getElementById("irb_pi_proj").style.display = "none";
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log("Failed in getPIProjects for PI: " + pi_first + " " + pi_last);
        });
    }

    srap.processRequest = function ()
    {
        var portal_project = null;
        var irb_project = null;
        if (document.getElementById("pi_projects")) {
            portal_project = document.getElementById("pi_projects").value;
        }
        if (document.getElementById("pi_irbs")) {
            irb_project = document.getElementById("pi_irbs").value;
        }
        var user_roles = Array.from(document.querySelectorAll('[id^=u_role]'));
        var user_role_names = [];
        var user_role_values = [];
        for (var i = 0; i < user_roles.length; i++) {
            user_role_names[i] = user_roles[i].name;
            user_role_values[i] = user_roles[i].checked;
        }

        // Load PI Info and go look for projects
        $.ajax({
            type: "POST",
            datatype: "html",
            async: true,
            data: {
                "action": "process_request",
                "pi_sunetid": document.getElementById("pi_sunetid").value,
                "proj_description": document.getElementById("proj_desc").value,
                "portal_proj": portal_project,
                "irb_proj": irb_project,
                "user_role_names": user_role_names,
                "user_role_values": user_role_values
            },
            success:function(html) {
            },
            error:function(jqXhr, textStatus, errorThrown) {
                console.log("Error in request to add user to research project: ", jqXHR, textStatus, errorThrown);
            }

        }).done(function (html) {
            if (html.length > 0 ) {
                window.location = html;
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log("Failed to process request for Research Project");
        });

    }

</script>

