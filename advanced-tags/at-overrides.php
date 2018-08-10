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

function qa_tag_html($tag, $microformats=false, $favorited=false)
{
	global $at_lang_list;
	
	$taghtml = qa_tag_html_base($tag, $microformats, $favorited);
	
	require_once QA_INCLUDE_DIR.'util/string.php';
	
	$taglc=qa_strtolower($tag);
	$at_lang_list[$taglc]=true;
	
	$anglepos=strpos($taghtml, '>');
	if ($anglepos!==false)
		$taghtml=substr_replace($taghtml, ' title=",TAG_DESC,'.$taglc.',"', $anglepos, 0);
		//$taghtml=substr_replace($taghtml, ' title=",TAG_DESC,'.$taglc.',"', $anglepos, 0);
	
	return $taghtml;
}
