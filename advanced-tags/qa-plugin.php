<?php

/*
	Plugin Name: Advanced Tags
	Plugin URI: https://www.github.com/jacksiro/Q2A-Advanced-Tags-Plugin
	Plugin Description: Advance your tags with description, image, wiki, adverts and so much more
	Plugin Version: 1.0
	Plugin Date: 2018-08-10
	Plugin Author: Jackson Siro
	Plugin Author URI: https://www.github.com/jacksiro
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: https://www.github.com/jacksiro/Q2A-Advanced-Tags-Plugin/master/advanced-tags/qa-plugin.php
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

	qa_register_plugin_module('page', 'at-edit.php', 'at_edit_page', 'Tag Edit Page');
	qa_register_plugin_overrides('at-overrides.php');
	qa_register_plugin_layer('at-layer.php', 'Advanced Tags Layer');
	qa_register_plugin_phrases('at-lang-*.php', 'at_lang');

	function at_html_suggest_edit_tags($tag)
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

		$htmlmessage = qa_lang_html('at_lang/no_desc_for_x');

		return strtr(
			$htmlmessage,
			array(
				'^1' => '<b>' . $tag . '</b>',
				'^2' => '<a href="' . qa_path_html('tag_edit/' . $tag) . '">',
				'^3' => '</a>',
			)
		);
	}
	
	function at_html_tag_wiki($wiki)
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }
		$htmlmessage = qa_lang_html('at_lang/learn_more');
		return strtr(
			$htmlmessage,
			array(
				'^1' => '<a href="' . $wiki . '">',
				'^2' => '</a>',
			)
		);
	}
	
	function at_get_tag_icon_html($tag, $blobId, $width, $height, $size, $padding = false)
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

		$params = array('qa_blobid' => $blobId);
		if (isset($size)) $params['qa_size'] = $size;
		$rootUrl = $absolute ? qa_opt('site_url') : null;
		$avatarLink = qa_html(qa_path('image', $params, $rootUrl, QA_URL_FORMAT_PARAMS));
		
		qa_image_constrain($width, $height, $size);

		$params = array(
			$avatarLink,
			$width && $height ? sprintf(' width="%d" height="%d"', $width, $height) : '',
		);

		$html = vsprintf('<img src="%s"%s class="at-avatar-image" alt=""/>', $params);

		if ($padding && $width && $height) {
			$padleft = floor(($size - $width) / 2);
			$padright = $size - $width - $padleft;
			$padtop = floor(($size - $height) / 2);
			$padbottom = $size - $height - $padtop;
			$html = sprintf('<span style="display:inline-block; padding:%dpx %dpx %dpx %dpx;">%s</span>', $padtop, $padright, $padbottom, $padleft, $html);
		}

		return sprintf('<a href="%s" class="at-avatar-link">%s</a>', qa_path_html('tag/' . $tag), $html);
	}
	
	function at_get_icon_blob_html($blobId, $width, $height, $size, $padding = false)
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

		require_once QA_INCLUDE_DIR . 'util/image.php';
		require_once QA_INCLUDE_DIR . 'app/users.php';

		if (strlen($blobId) == 0 || (int)$size <= 0) {
			return null;
		}

		$avatarLink = qa_html(qa_get_avatar_blob_url($blobId, $size));

		qa_image_constrain($width, $height, $size);

		$params = array(
			$avatarLink,
			$width && $height ? sprintf(' width="%d" height="%d"', $width, $height) : '',
		);

		$html = vsprintf('<img src="%s"%s class="qa-avatar-image" alt=""/>', $params);

		if ($padding && $width && $height) {
			$padleft = floor(($size - $width) / 2);
			$padright = $size - $width - $padleft;
			$padtop = floor(($size - $height) / 2);
			$padbottom = $size - $height - $padtop;
			$html = sprintf('<span style="display:inline-block; padding:%dpx %dpx %dpx %dpx;">%s</span>', $padtop, $padright, $padbottom, $padleft, $html);
		}

		return $html;
	}
	
	function at_set_tag_icon($tag, $imagedata, $userid, $oldblobid = null)
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }
		$imagedata = qa_image_constrain_data($imagedata, $width, $height, 500);

		if (isset($imagedata)) {
			qa_db_tagmeta_set($tag, 'iconblobid', $imagedata);
			require_once QA_INCLUDE_DIR . 'app/blobs.php';
			$newblobid = qa_create_blob($imagedata, 'jpeg', null, $userid, null, qa_remote_ip_address());
			if (isset($newblobid)) {
				qa_db_tagmeta_set($tag, 'iconblobid', $newblobid);
				if (isset($oldblobid)) qa_delete_blob($oldblobid);
				return true;
			}
		}
		return false;
	}