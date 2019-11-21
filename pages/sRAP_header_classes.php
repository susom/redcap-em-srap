<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

require_once ($module->getModulePath() . "classes/sRAP_utilities.php");


function getResearchProjects($proj_list) {

    global $pid, $existingUser, $module;

    $html = "";
    $research_display_fields = array("id", "rp_irb_number", "rp_type", "rp_name_short");

    $results = array();
    if (sizeof($proj_list) > 0) {
        // First see if there are projects where the pi is this person
        $results = REDCap::getData($pid, "json", $proj_list, $research_display_fields);
        $projects = json_decode($results);
    }

    $existingUser = (sizeof($results) > 0 ? true: false);

    foreach ($projects as $project) {
        if ($project->rp_type == "1") {
            $html .= '<a class="dropdown-item" href="' . $module->getURL("pages/sRAP_projects.php") . "&record_id=" . $project->id . '">[IRB ' . $project->rp_irb_number . "] " . $project->rp_name_short .'</a>';
        } else {
            $html .= '<a class="dropdown-item" href="' . $module->getURL("pages/sRAP_projects.php") . "&record_id=" . $project->id . '">' . $project->rp_name_short .'</a>';
        }
    }

    return $html;
}

function getRedcapProjects($proj_list) {

    global $pid;

    $html = "";
    $research_display_fields = array("id", "redcap_pid", "redcap_name");

    // First see if there are projects where the pi is this person
    $results = REDCap::getData($pid, "json", $proj_list, $research_display_fields);
    $redcaps = json_decode($results);

    $html .= '<a class="dropdown-item" href="#">New Redcap Project</a>';
    foreach ($redcaps as $redcap) {
        if (!is_null($redcap->redcap_pid) && !empty($redcap->redcap_pid)) {
            $html .= '<a class="dropdown-item" href="#">[IRB ' . $redcap->redcap_pid . '] ' . $redcap->redcap_name .'</a>';
        }
    }

    return $html;
}

function getRequests($proj_list) {

    global $pid;

    $html = "";
    $research_display_fields = array("id", "r_ticket_id", "r_description");

    // First see if there are projects where the pi is this person
    $results = REDCap::getData($pid, "json", $proj_list, $research_display_fields);
    $requests = json_decode($results);

    $html .= '<a class="dropdown-item" href="#">New Requests</a>';
    foreach ($requests as $request) {
        if (!is_null($request->r_ticket_id) && !empty($request->r_ticket_id)) {
            $html .= '<a class="dropdown-item" href = "#" >[' . $request->r_ticket_id . '] ' . $request->r_description . "</a>";
        }
    }

    return $html;
}

function getUsers($proj_list) {

    global $pid;

    $html = "";
    $research_display_fields = array("u_sunet");

    // First see if there are projects where the pi is this person
    $results = REDCap::getData($pid, "json", $proj_list, $research_display_fields);
    $users = json_decode($results);

    $user_list = array();
    foreach ($users as $user) {
        $user_list[] = $user->u_sunet;
    }

    $unique_list = array_unique($user_list);
    asort($unique_list);

    $html .= '<a class="dropdown-item" href="#">New User</a>';
    foreach ($unique_list as $user) {
        $html .= '<a class="dropdown-item" href="#" >' . $user . '</a>';
    }

    return $html;
}


function getPageHeader() {

    global $module, $existingUser, $pid, $user;

    $home = $module->getURL('pages/index.php');
    $inquiry_page = $module->getURL('pages/faq.php');
    $logo = $module->getURL('images/researchit.png');

    $proj_list = filterProjects($pid);
    $research_projects = getResearchProjects($proj_list);
    $users = getUsers($proj_list);
    $requests = getRequests($proj_list);
    $projects = getRedcapProjects($proj_list);
    $disable = !$existingUser;
    $display_user =  '<span class="navbar-text">Hello ' . $user . '!</span>';

// Top nav-bar
$html = '<nav class="navbar navbar-expand-lg navbar-dark bg-dark justify-content-between">
              <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <li class="nav-item active">
                            <a class="nav-link" href="' . $home . '">Home<span class="sr-only">(current)</span></a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarResearchProjects" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disable="'. $disable . '">
                                Research Projects
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarResearchProjects">'
                                . $research_projects .
                            '</div>
                        </li>
                        <li>
                            <a class="nav-link" href="' . $inquiry_page . '" >FAQ</a>
                        </li>
<!--
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarUsers" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Users
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarUsers">'
                                . //$users .
                            '</div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarRequests" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Requests
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarRequests">'
                                . //$requests .
                            '</div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarRedcapProjects" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                REDCap Projects
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarRedcapProjects">'
                                . //$projects .
                            '</div>
                        </li>
-->
                    </ul> 
              </div>
              ' . $display_user . '
         </nav>
          <div class="header">
            <img class="logo-img" src="' . $logo . '" alt="RIT logo"/>
          </div>
          <div class="maroon-accent">&nbsp;</div>';

    return $html;
}

