<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

global $pid, $user;

$pid = "27";
//$user = USERID;
$user = 'yasukawa';
$pi_projects = null;

//define('PROJECT_ID', $pid);

require_once ($module->getModulePath() . "pages/sRAP_header_classes.php");
require_once ($module->getModulePath() . "classes/sRAP_utilities.php");

$action = isset($_POST['action']) && !empty($_POST['action']) ? $_POST['action'] : null;
if ($action == 'pi_projects') {
    $pi_sunetid = isset($_POST['pi_sunetid']) && !empty($_POST['pi_sunetid']) ? $_POST['pi_sunetid'] : null;
    $pi_projects = null;

    if ($action == 'pi_projects') {
        // Look for portal projects that are already created for this PI
        $module->emLog("Looking up portal projects for PI " . $pi_sunetid . " by " . $user);
        $display_fields = array("id", "rp_irb_number", "rp_type", "rp_name_short");
        $proj_list = filterProjects($pid, $pi_sunetid);

        // If the PI has some portal projects already, display them
        if (!empty($proj_list)) {
            $list = getDisplayData($pid, $proj_list, $display_fields);
            $proj = json_decode($list, true);

            if ($list <> false) {
                $pi_projects .= '<div id="pi_portal_proj">';
                $pi_projects .= '<h4>Please select the project you wish to join:</h4><br>';
                $pi_projects .= '<h6>Research Portal Projects for ' . $pi_sunetid . ':</h6>';
                $pi_projects .= '<select id="pi_projects" onchange="selectedPortalProject(value)">';
                $pi_projects .= '<option value="">-- select one --</option>';
                foreach ($proj as $key => $value) {
                    $pi_projects .= '<option value="' . $value["id"] . '">' . $value["rp_name_short"] . '</option>';
                }
                $pi_projects .= '<option value="None">None of the above</option>';
                $pi_projects .= '</select></div><br>';
            }
        }

        // Now add the IRB Protocols for this PI
        $module->emLog("Looking up IRB projects for PI " . $pi_sunetid . " by " . $user);

        // Retrieve IRB numbers that this user is associated with that does not currently have a research project associated with it.
        $protocols = get_IRBBySunetID($pi_sunetid);

        // If PI has valid IRBs, display them but keep them hidden for now
        if (!empty($protocols)) {
            $pi_projects .= '<div id="pi_irb_proj">';
            if (!empty($proj_list)) {
                $pi_projects .= '<h5>Is your project related to an IRB?</h5><br>';
            }
            $pi_projects .= '<h6>IRBs for ' . $pi_sunetid . ':</h6>';
            $pi_projects .= '<select id="pi_irbs" onchange="selectedIRBProject(value)">';
            $pi_projects .= '<option value="">-- select one --</option>';
            foreach ($protocols as $key => $value) {
                $pi_projects .= '<option value="IRB' . $value["protocolNumber"] . '">' . $value["protocolTitle"] . '</option>';
            }
            $pi_projects .= '<option value="None">None of the above</option>';
            $pi_projects .= '</select></div><br><br>';
        }

        // PI does not have portal projects or IRBs yet. Just display a message so user knows
        if (empty($proj_list) && empty($protocols)) {
            $pi_projects .= '<div id="no_pi_proj">The PI ' . $pi_sunetid . ' does not currently have any portal projects or IRBs. Please continue to create a new project.</div>';
        } else {
            $pi_projects .= '<div id="no_pi_proj">Please continue to create a new project.</div>';
            $pi_projects .= '<div id="join_pi_proj">Please continue to join the project.</div>';
            $pi_projects .= '<div id="irb_pi_proj">Please continue to create a new project from this IRB.</div>';
        }

        print $pi_projects;
        return;
    }

} else if ($action == 'process') {
    $module->emLog("In process");
    $instrument = 'users';

    $pi_sunetid = isset($_POST['pi_sunetid']) && !empty($_POST['pi_sunetid']) ? $_POST['pi_sunetid'] : null;
    $p_desc = isset($_POST['p_desc']) && !empty($_POST['p_desc']) ? $_POST['p_desc'] : null;
    $irb_num = isset($_POST['irb_num']) && !empty($_POST['irb_num']) ? $_POST['irb_num'] : null;
    $pi_proj = isset($_POST['pi_proj']) && !empty($_POST['pi_proj']) ? $_POST['pi_proj'] : null;
    $pi_irb = isset($_POST['pi_irb']) && !empty($_POST['pi_irb']) ? $_POST['pi_irb'] : null;
    $u_role = isset($_POST['u_role']) && !empty($_POST['u_role']) ? $_POST['u_role'] : null;

    // Get LDAP info for user
    $ldap_user = file_get_contents('http://med.stanford.edu/webtools-dev/stanford_ldap/ldap_lookup.php?token=pXJ5xNwj1P&exact=true&only=displayname,mail,department,suaffiliation,ou,telephonenumber&userid=' . $user);
    $ldap_user_result = json_decode($ldap_user);

    // If the user is not the PI, get info for both
    if ($user <> $pi_sunetid) {
        $ldap_pi = file_get_contents('http://med.stanford.edu/webtools-dev/stanford_ldap/ldap_lookup.php?token=pXJ5xNwj1P&exact=true&only=displayname,mail,department,suaffiliation,ou,telephonenumber&userid=' . $pi_sunetid);
        $ldap_pi_result = json_decode($ldap_pi);
    }

    // Put together all the data and create a new record
    if (!is_null($pi_proj) and !empty($pi_proj)) {
        $module->emLog("In create_record - have pi proj");
        // If we have a project already, use add a user
        $data = array("id" => $pi_proj,
            "u_firstname" => $ldap_user_result["first_name"],
            "u_lastname" => $ldap_user_result["last_name"],
            "u_sunet" => $user,
            "u_role" => $u_role,
            "u_email" => $u_email,
            "u_phone" => $u_phone,
            "u_status" => 1,
            //"u_permissions"             =>,
            "users_complete" => 1
        );

        // Now save user provisionally
        $user = new sRAP_Instances($pid, $instrument);
        $instance_id = $user->getNextInstanceId($pi_proj);
        $user->saveInstance($pi_proj, $data, $instance_id);

    } else if ((!is_null($irb_num) and !empty($irb_num)) or (!is_null($pi_irb) and !empty($pi_irb))) {
        $module->emLog("In create_record - have IRB Num");
        // If we have an IRB number, we need the PI on the IRB

        $next_id = getNextId($pid, 'id', null, '');
        $data = array("id" => $next_id,
            "id_complete" => 2,
            "rp_name_short" => $p_desc,
            "rp_type" => 1,  // IRB required
            "rp_irb_number" => $irb_num,
            //"rp_pi_first_name"          =>,
            //"rp_pi_last_name"           =>,
            //"rp_pi_email"               =>,
            //"rp_pi_phone"               =>,
            //"rp_pi_sunetid"             =>,
            //"rp_pi_affliation"          =>,
            //"rp_pi_department"          =>,
            "research_project_complete" => 1
        );
        $pi_proj = REDCap::saveData($pid, 'json', json_encode($data));

        // Now save user provisionally
        $user = new sRAP_Instances($pid, $instrument);
        $instance_id = $user->getNextInstanceId($pi_proj);
        $user->saveInstance($pi_proj, $data, $instance_id);
    }

    $project_page_url = $module->getUrl('pages/sRAP_projects.php') . "&record_id=" . $pi_proj;
    $module->emLog("URL in create record: " . $project_page_url);
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
    <div class="container" width="50%" id="index_container">
        <form id="ResearchProject" submit="">
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
                    <h5>Please help us determine if a portal project has already been created for your project: </h5>
                </div>
                <div id="pi_info">
                    <p>
                       <a><b>Principal Investigator's sunetID whose project you will be working on:</b></a>
                       <input placeholder="PI's sunetID..." name="pi_sunetid" id="pi_sunetid">
                    </p>
                    <p>
                       <a><b>Enter a short description of the project</b></a>
                       <input placeholder="Project Description..." size="50%" name="proj_desc" id="proj_desc"><br><br>
                   </p>
                    <p>
                       <input type="checkbox" id="pi_attest" value="checked">
                       <a><b>I attest that I am working with this Principal Investigator.</b></a><br>
                       <a><i>  (The PI will be notified of this attestation)</i></a>
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
                    <a ><b> What is your role(s) in this project </b ></a ><br>
                    <input type = "checkbox" name = "pi" id = "pi" value = "pi" > Principal Investigator </input ><br >
                    <input type = "checkbox" name = "finance" id = "finance" value = "finance" > Financial Staff </input ><br >
                    <input type = "checkbox" name = "research" id = "research" value = "research" > Research Staff </input ><br >
               </p>
            </div>
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

<script>
    var currentTab = 0; // Current tab is set to be the first tab (0)
    showTab(currentTab); // Display the current tab
    document.getElementById("no_attestation").style.display = "none";
    document.getElementById("no_pi_sunetid").style.display = "none";
    document.getElementById("no_proj_description").style.display = "none";

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
        x[n].style.display = "block";
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

            // If this is the PI, check the PI on the roles page
            if (user == pi_sunetid) {
                document.getElementById("pi").checked = true;
            } else {
                document.getElementById("pi").checked = false;
            }

            // Make sure each of the fields has a reasonable value before proceeding
            if ((pi_sunetid.length < 2) || (proj_description.length < 2) || (attest == false)) {
                n = currentTab - n;
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
        }

        // Otherwise, display the correct tab:
        showTab(currentTab);
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
        // Load PI Info and go look for projects
        $.ajax({
            type: "POST",
            datatype: "html",
            async: false,
            data: {
                "action": "create_record",
                "pi_first": pi_first,
                "pi_last" : pi_last,
                "p_desc"  : p_desc,
                "irb_num" : irb_num,
                "pi_proj" : pi_proj,
                "pi_irb"  : pi_irb,
                "u_role"  : u_role,
                "u_email" : u_email,
                "u_phone" : u_phone
            },
            success:function(html) {
            },
            error:function(jqXhr, textStatus, errorThrown) {
                console.log("Error in getIRBsProjects: ", jqXHR, textStatus, errorThrown);
            }

        }).done(function (html) {
            if (html.length > 0 ) {
                window.location = html;
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log("Failed in getIRBsProjects for PI " + pi_first + ' ' + pi_last);
        });

    }

</script>

</body>
</html>

