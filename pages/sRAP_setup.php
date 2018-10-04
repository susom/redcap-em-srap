<?php
/**
 * Created by PhpStorm.
 * User: LeeAnnY
 * Date: 10/1/2018
 * Time: 11:38 PM
 */

$user_role_names = array("u_role___0", "u_role___1", "u_role___2", "u_role___3");
$user_instrument = "users";
$user_display_fields = array("u_firstname", "u_lastname", "u_role", "u_status");

$redcap_instrument = "redcap_projects";
$redcap_display_fields = array("redcap_pid");

$request_instrument = "requests";
$request_display_fields = array("r_date", "r_requestor", "r_case_num", "r_description", "r_status", "r_last_updated_on");

$funding_instrument = "funding_information";
$funding_display_fields = array("billing_ilab_service_id", "billing_first_name", "billing_last_name", "billing_email", "billing_pta", "billing_pta_date");

$project_display_fields = array("id", "rp_name_short", "rp_type", "rp_irb_number", "rp_irb_status", "rp_funding_status",
    "rp_start_date", "rp_end_date", "rp_pi_firstname", "rp_pi_email", "rp_pi_phone", "rp_pi_department", "rp_pi_sunetid",
    "rp_description", "rp_pi_lastname" );

$popover_content =
    array("A Principal Investigator will have access to make changes to any part of the project. The Principal Investigator may designate an Administrator to have the same set of privileges that they have.",
        "An Administrator will have access to make changes to any part of this Research Project. They will be able to approve users who request access to this project and edit financial data.",
        "The Financial Staff will have access to the financial data. They will not be able to approve users who have requested access to the project but they will have access to all other parts of the project.",
        "The Research Staff will not have access to financial data and will not be able to approve users who request access to the project.  They will have access to all other parts of the project.");

?>