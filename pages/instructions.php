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
<?php echo $module->getUrl('pages/index.php', false, true) ?>
</pre>
<br>

<h4>This is URL for the Salesforce API to store current Salesforce data into the Research Portal</h4>
<pre>
<?php echo $module->getUrl('pages/sRAP_salesforce.php', true, true) ?>
</pre>
<br>


