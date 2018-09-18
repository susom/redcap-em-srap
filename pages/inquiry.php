<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

require_once ($module->getModulePath() . "pages/sRAP_header_classes.php");
require_once ($module->getModulePath() . "classes/sRAP_instances.php");

$user = USERID;

?>

<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <title>Inquiry Page</title>
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

<!-- Top nav bar -->
<?php echo getPageHeader(); ?>
<!-- Debug statements
< ?php echo getMessage(); ?>
-->

<div id="background" class="background">
    <!--
    <form data-toggle="validator" role="form" method=POST action='submit_v2.php'>
    -->
        <div class="container">
            <div class="panel">
                <div class="panel-heading">
                    <h3>How can I help you today?</h3>
                </div>
                <div class="panel-body">
                    </div><div class="row">
                        <div class="col">
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="REDCap"  name="Primary_Category__c" data-value="REDCap" data-toggle="modal" data-target="#referralModal" data-index="0">
                                        I need help with REDCap
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="pro"  name="Primary_Category__c" data-value="pro" data-toggle="modal" data-target="#referralModal" data-index="1">
                                        I want to collect self-reported outcomes from my patients / study participants
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="alerting"  name="Primary_Category__c" data-value="alerting" data-toggle="modal" data-target="#referralModal" data-index="2">
                                        I wish to be alerted when a patient of interest turns up at Stanford
                                </label>
                            </div>
                             <div class="radio col-sm-12">
                                 <label>
                                    <input type="radio" id="registry"  name="Primary_Category__c" data-value="registry" data-toggle="modal" data-target="#referralModal" data-index="3">
                                        I want to submit Stanford data to a clinical research registry
                                 </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="rdm"  name="Primary_Category__c" data-value="rdm" data-toggle="modal" data-target="#referralModal" data-index="4">
                                    I am looking for advice on research data management tools
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="datalake"  name="Primary_Category__c" data-value="datalake" data-toggle="modal" data-target="#referralModal" data-index="5">
                                        I'm interested in establishing a research data resource for my lab / division
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="mhealth"  name="Primary_Category__c" data-value="mHealth" data-toggle="modal" data-target="#referralModal" data-index="6">
                                    I am looking for help developing a mobile health app
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="cadata"  name="Primary_Category__c" data-value="cadata" data-toggle="modal" data-target="#referralModal" data-index="7">
                                    I want to use Stanford clinical data *not* found in Epic for research
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="grants"  name="Primary_Category__c" data-value="grants" data-toggle="modal" data-target="#referralModal" data-index="8">
                                        I am writing a grant and need a description of Stanford Medicine's informatics resources
                                </label>
                            </div>
                        </div>  <!-- end col -->
                        <div class="col">
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="recruitment"  name="Primary_Category__c" data-value="recruitment" data-toggle="modal" data-target="#referralModal" data-index="9">
                                        I am looking to boost my study enrollment
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" id="cohortid"  name="Primary_Category__c" data-value="cohortid" data-toggle="modal" data-target="#referralModal" data-index="10">
                                        I want to count how many patients have a specific clinical profile at Stanford
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" data-value="epicdata" data-toggle="modal" data-target="#referralModal" data-index="11">
                                        I want to use Stanford clinical data from Epic for research
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="greenbutton" data-toggle="modal" data-target="#referralModal" data-index="12">
                                        I want a clinical outcomes report on patients like mine seen at Stanford
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="stats" data-toggle="modal" data-target="#referralModal" data-index="13">
                                        I'm looking for statistical expertise / advice
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="compliance" data-toggle="modal" data-target="#referralModal" data-index="14">
                                        I have a compliance question
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="PHS" data-toggle="modal" data-target="#referralModal" data-index="15">
                                        I'm looking for big datasets for a Population Health study
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="compute" data-toggle="modal" data-target="#referralModal" data-index="16">
                                        I am looking for secure high-performance computing resources for my big data project
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="anon" data-toggle="modal" data-target="#referralModal" data-index="17">
                                        I am looking for help de-identifying clinical data
                                </label>
                            </div>
                            <div class="radio col-sm-12">
                                <label>
                                    <input type="radio" name="Primary_Category__c" class="btn btn-primary" data-value="general" data-toggle="modal" data-target="#referralModal" data-index="18">
                                        I don't see my topic on your list
                                </label>
                            </div>
                        </div> <!-- End of col -->
                    </div>  <!-- End of row -->
                </div> <!-- End panel-body -->
            </div>   <!-- End panel -->


            <!-- Modal -->
            <div class="modal fade bd-example-modal-lg" id="referralModal" tabindex="-1" role="dialog" aria-labelledby="referralModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <img id="modal-image" src="" alt="" max-height="200px" max-width="150px">
                                <h6 class="modal-title-alt" align="left">Title</h6>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                             Body
                        </div>
                        <div class="modal-footer">
                            <h6 class="modal-title" align="left">Title</h6>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal -->

            <!-- Start card -->
<!--
            <div class="card" id="referralModal" style="width: 18rem;" aria-labelledby="referralModal" aria-hidden="true>
                <img class="card-img-top" src=" " alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    <a href="#" class="btn btn-primary">Go somewhere</a>
                </div>
            </div>
-->
            <!-- End Card -->

        </div>   <!-- End container -->
    <!--
    </form>
    -->

</div>  <!-- End background -->

<script type="text/javascript">

    function selectedOption($this) {
        alert("Select radio is " + $this);
    }

    $(document).ready(function() {
        $('#referralModal').on('show.bs.modal', function (event) {

            var referrals = [
                            //name      title    alternate    image     body
                            ['REDCap', 'REDCap Consulting Services and Support', 'REDCap logo', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/redcap-logo-medium.png','<b>Something about REDCap</b>'],
                            ['pro', 'Research IT and Patient Reported Outcomes', 'N/A', 'N/A', '<p>Research IT operates two complementary patient-reported outcomes systems, REDCap and CHOIR.  REDCap is a self-service full-featured system for developing and running complex studies,  including the collection of patient-reported measures and outcomes and is the ideal tool for a lab with a tech-savvy research coordinator. </p><p> CHOIR on the other hand is purpose-built patient reported outcomes collection system  that is well-integrated with clinical workflow at Stanford  that can be operated by clinical front desk staff in clinics with an integrated clinical-research mission.  </p>  <p>If you are interested in hearing more about either REDCap or CHOIR, please complete the request form below to get in touch with us.</p>'],
                            ['alerting', 'Research Alerting Services offered by Research IT', 'N/A', 'N/A', '<p>Research IT receives real-time data from both hospitals and can use that information to design, build and deploy  real time alerts for time-sensitive research alerting, such as being notified when the patient in your study  arrives in clinic, or when a new potential candidiate for recruitment presents at the hospital.</p> <p>If your study needs would be met by periodic e.g. daily, weekly or even quarterly notifications,  a more cost-effective option is to contact <a href="ric.php?ric=1">the Research Informatics Center</a> to arrange for  a monitored chart review cohort.</p>'],
                            ['registry', 'Registry Submission Services offered by Research IT', 'N/A', 'N/A', '<p>Establishing participation in an externally operated research registry is a complex undertaking, involving  the IRB, Privacy, the Information Security Office, and possibly Stanford Legal if PHI is involved (they help with the BAA).  Once you have put in place all the mutually required confidentiality and data privacy safeguards, Research IT  can assist with the technical work of assembling the clinical data and automating the submission to the registry. </p>'],
                            ['rdm', 'Research Data Management at Stanford', 'N/A', 'N/A', '<p>There are many good tools to manage your valuable research data at Stanford, but the one we recommend the most is  <a href="https://med.stanford.edu/researchit/infrastructure/redcap.html">REDCap.</a> </p>  <p><a href="https://redcap.stanford.edu/">REDCap</a> is a secure, safe, reliable and auditable  multi-user research data capture system operated by Research IT  for the Stanford Medicine research community. It is designed to be self-service, so you can get started using it  right away. Not convinced yet? <a href="https://med.stanford.edu/researchit/infrastructure/redcap/edc-faq.html">Check this out.</a> </p> <p> There are training videos online in REDCap itself. Just click on the Training Resources tab. </p> <p> There is <a href="https://medwiki.stanford.edu/display/redcap/School+of+Medicine%3A+REDCap+Home">Frequently Asked Questions (FAQ)</a> on our wiki. </p> <p> REDCap Office Hours are 9-noon on Wednesday at 3172 Porter Drive if you want to drop in for a chat. </p> <p> Still have questions? Fill out the form below to get in touch with the REDCap support team. </p> '],
                            ['datalake', 'Research IT and Data Lakes', 'N/A', 'N/A', '<p>Research IT can assist your research program by working with you to identify and assemble relevant  clinical data for your research program, placing it  into a queryable resource such as a relational database. </p>  <p>Please complete the request form below to get in touch with us.</p> '],
                            ['mHealth', 'mHealth Resources: Research IT /  Stanford Center for Digital Health', 'N/A', 'N/A', '<p> If you are looking for expertise on running health studies involving mobile devices, you  should contact the <a href="https://med.stanford.edu/cdh.html">Stanford Center for Digital Health.</a> </p> <p>Research IT operates the back-end for the Apple Heart Study and has collected 23andMe data for the Gene Pool research study. </p>  <p>If you are interested in hearing more about our capacity to support your mHealth research study, please complete the request form below to get in touch with us.</p>'],
                            ['cadata', 'Research IT and Clinical Ancillary System Data', 'N/A', 'N/A', '<p>If you are looking for a "STARR Insufficient Letter", the <a href="http://med.stanford.edu/ric.html">Research Informatics Center</a> can assist you.  Please <a href="ric.php?ric=1">fill out their online request form</a> to get in touch. </p>  <p>If on the other hand you are looking to engage with Research IT to request information on the process to integrate new forms of clinical data into the  STARR data lake, please complete the request form below. We look forward to working with you!</p> '],
                            ['grants', 'Grant Writing Tips by Research IT', 'N/A', 'N/A', '<p> Research IT maintains <a href="http://med.stanford.edu/researchit/resources/grant-writing-resources.html"> an online resource</a>  designed for researchers to support their grant application </p> <p>If you need further information after <a href="http://med.stanford.edu/researchit/resources/grant-writing-resources.html">reviewing the information on our site,</a>  please complete the project request form below to get in touch.</p>'],
                            ['recruitment', 'Recruitment Enhancement Core / Research IT', 'N/A', 'N/A', '<p>Sounds like you may wish to contact the <a href="mailto:recparticipantrecruitment@stanford.edu">Recruitment Enhancement Core</a>  operated by Spectrum.  The Research Informatics Center has a page on their site  <a href="https://med.stanford.edu/ric/resources/contacting-patients-for-research-recruitment.html">explaining a bit more.</a> </p>  <p>Another option to consider is to work with us, Research IT, to set up a real time patient enrollment alert.  Research IT operates a service that sends you a secure email or SMS message when a clinical event  or sequence/combination of events occurs.</p>  <p>If this option sounds interesting, please complete the project request form below to get in touch.</p> '],
                            ['cohortid', 'Research Informatics Center / Hospital IT', 'Research Informatics Center Website', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/ric_v2.png', '<h3>Cohort Identification Tools at Stanford</h3> <p>There are several options for identifying groups of similar patients at Stanford; which one you select depends in part on your intent. </p> <p> If your intent is research, your best option is the <a href="https://stride-service.stanford.edu/stride/web">Cohort Identification Tool</a>.  If the <a href="http://med.stanford.edu/researchit/infrastructure/clinical-data-warehouse/cohort-tool.html">online help</a> does not answer your question or you are unable to log in, please <a href="ric.php?ric=1">contact the Research Informatics Center</a> for assistance. </p> <p> If your intent on the other hand is clinical care, your hospital IT department can assist you. </p>  '],
                            ['epicdata', 'Research Informatics Center', 'Research Informatics Center Website', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/ricthumb.png', '<h3>Obtaining Clinical Data for Research Purposes</h3> <p>We are unable to assist with that, but you should reach out to the <a href="http://med.stanford.edu/ric.html">Research Informatics Center</a> (RIC) team.  The RIC is a center of excellence in the Department of Biomedical Data Sciences. They are Stanford Medicine\'s official clearinghouse  for all requests to use clinical data for any secondary purpose, including research. They partner with the  <a href="http://privacy.stanford.edu/">University Privacy Office</a> and  <a href="http://humansubjects.stanford.edu/">Stanford Internal Review Board</a> to bring you a  <a href="http://med.stanford.edu/ric/resources/som-compliance-processes.html">streamlined compliance  process</a> to rapidly get you using the clinical data relevant to your research. </p>  <p>Please <a href="ric.php?ric=1">fill out their online request form</a> to get in touch.</p>'],
                            ['greenbutton', 'Clinical Informatics Consult at Stanford', 'Shah Lab Website', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/shah.png', '<h3>Shah Lab "Green Button" Consultation Service</h3> <p>We are unable to assist with that, but it sounds like you are interested in a <a href="https://shahlab.stanford.edu/inf-consult">Clinical Informatics Consult.</a> </p> <p> Given a specific clinical question, they provide you with a report with a descriptive summary of similar patients in Stanford’s  clinical data warehouse, treatment choices made, and observed outcomes as envisioned in the  <a href="https://shahlab.stanford.edu/greenbutton">Green Button</a> paper. </p> <p>Please <a href="https://shahlab.stanford.edu/inf-consult">visit their website</a> to get in touch.</p> '],
                            ['stats', 'BDS Data Studio / QSU', 'Data Studio Website', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/data_studio.png', '<h3> Statistical Expertise at Stanford</h3> We are unable to assist with that, but there are two great resources for biostatistical support at Stanford Medicine,  the <a href="https://med.stanford.edu/dbds/service/data-studio.html">Data Studio</a>  in the Department of Biomedical Data Science, and the <a href="https://med.stanford.edu/qsu.html">  Quantitative Sciences Unit</a>, in the Biomedical Informatics Research division of the Department  of Medicine. Please visit their websites for information on how to reach them for a consultation.'],
                            ['compliance', 'Compliance Resources at Stanford', 'Compliance Resources at Stanford', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/security.png', '<p>We are unable to assist you directly, but ensuring compliance with all applicable laws, rules and regulations is crucially important,  and Stanford supports you with the following partner organizations:</p> <ul> <li>The <a href="https://researchcompliance.stanford.edu/">Stanford Research Compliance</a> office on main campus  is your first stop. They are charged with preventing harm to study subjects. <li>The <a href="http://privacy.stanford.edu/">University Privacy Office</a> is responsible for data confidentiality. <li> The <a href="https://uit.stanford.edu/organization/iso">Information Security Office</a> on main campus  is responsible for data security. They work closely with Privacy to help avoid HIPAA breaches from occurring. <li> The <a href="http://med.stanford.edu/ric.html">Research Informatics Center</a> in the Department  of Biomedical Data Science has a well documented process for the  <a href="http://med.stanford.edu/ric/resources/som-compliance-processes.html">compliant secondary use of clinical  data for research purposes.</a> <li> Most of the clinical record is pre-approved by the hospitals for research use (after all applicable processes  have been duly completed), but  there are certain types of clinical data that require additional approval by  <a href="https://acrp.stanford.edu/compliance/hospitals-and-clinics-compliance">Hospital Compliance.</a>  Hospital cost data is a good example of this: actual procedure costs are considered far too sensitive  for general research use. <li>If you plan to build a new computing system that handles high risk data, you will be required  to complete a <a href="https://uit.stanford.edu/security/dra">Data Risk Assessment</a>. <li>And if you are sharing high risk data with any outside  entity, you will need at the very  least a <a href="https://privacy.stanford.edu/other-resources/data-use-agreement-dua-faqs"> Data Use Agreement </a>  </li> </ul> '],
                            ['PHS', 'Population Health Sciences', 'PHS Website', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/phs.png', '<h3>Big Datasets at Stanford</h3> <p>We are unable to help you with that, but the Stanford experts in population health studies can be found at the   <a href="http://med.stanford.edu/phs.html">Stanford Center for Population Health Sciences</a>.  Information on their services and how to reach them is available <a href="http://med.stanford.edu/phs.html">on their website.</a> </p>'],
                            ['compute', 'Secure High-Performance Computing at Stanford', 'SRCC Website', 'http://localhost/redcap/redcap_v8.4.3/ExternalModules/?prefix=sRAP&page=images/srcc.png', '<h3>Secure Big Data Computing Stanford</h3> We are unable to assist with that, but the  <a href="https://srcc.stanford.edu/">Stanford Research Computing Center</a>  operates a cluster of secure compute servers, including some high performance systems,  suitable for running compute-intensive algorithms on big data, including high risk data / PHI. <p><br/></p>You can consult their <a href="https://srcc.stanford.edu/office-hours-and-support/support">Office Hours and Support</a>  page to find options to get in touch with them.</p>'],
                            ['anon', 'Clinical Data De-identification / Anonymization', 'N/A', 'N/A', '<p>Research IT builds and operates pipelines for clinical data anonymization and de-identification,  processing a variety of data including clinical documents and reports, and DICOM images.  Our methods are <a href="https://docs.google.com/document/d/1ZFAqubETuXpCmizglb5PJAKTRJuJCOKpshwRw8Vc9CE/edit?usp=sharing">  documented in this whitepaper. </a> </p><p>If you are interested in hearing more about our tools and services for anonymizing clinicial data for research,  please complete the request form below to get in touch with us. </p> '],
                            ['general', 'General Questions', 'N/A', 'N/A', 'Describe what to do']
                            ];

            var button = $(event.relatedTarget);
            var org = button.data('value');
            var index = button.data('index');

            if ((index >= 0) && (index < referrals.length)) {
                var modal = $(this);
                modal.find('.modal-body').text(referrals[index][4]);
                var image = document.getElementById("modal-image");
                if (referrals[index][3] == 'N/A') {
                    image.src = "";
                    modal.find('.modal-title-alt').text(referrals[index][1]);
                    modal.find('.modal-title').text("");
                } else {
                    image.src = referrals[index][3];
                    modal.find('.modal-title-alt').text("");
                    modal.find('.modal-title').text(referrals[index][1]);
                }
            }
/*
            if ((index >= 0) && (index < referrals.length)) {
                var modal = $(this);
                modal.find('.card-text').text(referrals[index][4]);
                var image = document.getElementById("card-img-top");
                if (referrals[index][3] == 'N/A') {
                    image.src = "";
                    //modal.find('.modal-title-alt').text(referrals[index][1]);
                    modal.find('.card-title').text("");
                } else {
                    image.src = referrals[index][3];
                    //modal.find('.modal-title-alt').text("");
                    modal.find('.card-title').text(referrals[index][1]);
                }
            }
*/
        });
    });

</script>


</body>
</html>
