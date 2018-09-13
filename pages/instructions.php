<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

require APP_PATH_DOCROOT . "ControlCenter/header.php";


if (!SUPER_USER) {
    ?>
    <div class="jumbotron text-center">
        <h3><span class="glyphicon glyphicon-exclamation-sign"></span> This utility is for the Research Portal project.</h3>
    </div>
    <?php
    exit();
}

?>

<h3>sRAP Description</h3>
    <p>
        This External Module
    </p>
<br>

<h4>This is the starting point for the Research Portal</h4>
<pre>
<?php echo $module->getUrl('Pages/index.php', true, true) ?>
</pre>
<br>

<h4>This is URL for the Salesforce API to store current Salesforce data into the Research Portal</h4>
<pre>
<?php echo $module->getUrl('Pages/sRAP_salesforce.php', true, true) ?>
</pre>
<br>

<!--
<h4>API Example</h4>
< ?php
if (empty($module->getSystemSetting("biocatalyst-api-token"))) {
    echo "<div class='alert alert-danger'>No API token has been defined.  This service will not work until you enter a shared secret in the External Modules configuration page.</div>";
} else {
    ?>
    <p>
        The following parameters are valid in the body of the POST
    </p>
    <pre>
    token:       < ?php echo $module->getSystemSetting("biocatalyst-api-token"); ?> (this token is a shared secret and can only be reset by Super Users)
    request:     users | reports
    user:        SUNETID (e.g. jdoe)
    project_id:  (optional) REDCap Project ID (e.g. 12345)
    report_id:   (optional) REDCap Report ID (e.g. 1234)

    See the gitlab README.md file for more detailed API instructions.

    </pre>
    <br>

    < ?php
}
?>
-->

