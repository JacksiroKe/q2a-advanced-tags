<?php

/*
	Advanced Tags
	https://www.github.com/jacksiro/Q2A-Advanced-Tags-Plugin
	
	Advance your tags with description, image, wiki, adverts and so much more
	
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

return array(
	'edit_detail_for_x' => 'Edit the details for the tag: ^',
	'learn_more' => '^1Learn More ... ^2',
	'no_desc_for_x' => 'This is no description yet for tag: ^1. Help get things started by ^2editting this tag^3.',
	'remove_icon' => 'Remove Icon',
	'sponsored_content' => 'Sponsored content for this tag',
	'suggest_qs_tags' => 'To see more, click for the ^1full list of questions^2 or ^3popular tags^4.',
	'save_tag_button' => 'Save Tag Details',
	'tag_advertisement' => 'Sponsored Advertisement - HTML Allowed (Optional):',
	'tag_description' => 'Tag Description:',
	'tag_icon' => 'Tag Icon (Optional):',
	'tag_wiki' => 'Tag Wiki URL (Optional):',
	'create_desc_link' => 'Create tag description',
);
