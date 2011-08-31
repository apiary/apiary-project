<?php
$server_base = variable_get('apiary_research_base_url', 'http://localhost');
$fedora_base_url = variable_get('fedora_base_url', 'http://localhost:8080');
$home_link = '<p><h2><a href="'.$server_base.'/drupal">Home</a></h2></p>';
?>
<?php echo $home_link;?>
<p><h2><span><span>Update Apiary Project Variables</span></span></h2></p>
<p><a href="<?php echo $server_base;?>/drupal/admin/settings/apiary_project">Apiary Project Variables</a></p>
<p>Edit and update the variables stored in Drupal for the Apiary Project.</p>
<p><h2><span><span>Choose Admin Task to Perform</span></span></h2></p>
<p><a href="<?php echo $server_base;?>/drupal/apiary?ref=administer_workflows">Create and Edit Workflows</a></p>
<p>Manage workflows by assigning a specimen pool, users, permissions and strategies.</p>
<p><a href="<?php echo $server_base;?>/drupal/apiary?ref=groundtruth">Create and Edit Ground Truth</a></p>
<p>Manage the ground truth data for a specimen.</p>
<p><a href="<?php echo $server_base;?>/drupal/apiary?ref=create_new_specimen">Create New Specimen</a></p>
<p>Ingest one or more specimen and image objects from the Apiary Research djatoka server, including optional specimen metadata.</p>
<p><a href="<?php echo $server_base;?>/drupal/apiary?ref=batch_create_specimens">Batch Create Specimens</a></p>
<p>Ingest multiple specimen and image objects using a source text file and jp2 base url</p>
<p><a href="<?php echo $server_base;?>/drupal/modules/apiary_project/workflow/comparer.php">Text Comparison</a></p>
<p>Compare two text strings to view the difference between them</p>
<p><a href="<?php echo $server_base;?>/drupal/modules/apiary_project/workflow/search.php">Search Objects</a></p>
<p>Find objects based on Metadata and Status</p>
<p><a href="<?php echo $server_base;?>/drupal/apiary?ref=solr_index_all">Re-Index All Fedora Objects into Solr</a></p>
<p>Deletes all solr indexes then re-indexes all fedora objects used in the Apiary Project</p>
<p><h2><span><span>Example Links to fedora objects</span></span></h2></p>
<p><a href="<?php echo $fedora_base_url;?>/get/ap-roi:ROI-6"><?php echo $fedora_base_url;?>/get/ap-roi:ROI-6</a></p>
<p>Main Digital Object overview. Replace ap-roi:ROI-6 with the digital object you wish to access</p>
<p><a href="<?php echo $fedora_base_url;?>/get/ap-roi:ROI-6/DC"><?php echo $fedora_base_url;?>/get/ap-roi:ROI-6/DC</a></p>
<p>View an object's datastream. Replace ap-roi:ROI-6 with the digital object and DC with the datastream you wish to access</p>
<p><a href="<?php echo $fedora_base_url;?>/get/ap-roi:ROI-6/fedora-system:3/viewItemIndex"><?php echo $fedora_base_url;?>/get/ap-roi:ROI-6/fedora-system:3/viewItemIndex</a></p>
<p>View a list of datastreams for an object. Replace ap-roi:ROI-6 with the digital object</p>
<p><a href="<?php echo $fedora_base_url;?>/get/ap-roi:ROI-6/fedora-system:3/viewMethodIndex"><?php echo $fedora_base_url;?>/get/ap-roi:ROI-6/fedora-system:3/viewMethodIndex</a></p>
<p>View a list of service methods (i.e. dynamicOCR) for an object. Replace ap-roi:ROI-6 with the digital object</p>
<p>&nbsp;</p>