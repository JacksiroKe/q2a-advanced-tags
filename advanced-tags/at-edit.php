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

class at_edit_page {
	
	function match_request($request)
	{
		return strpos($request, 'tag_edit') !== false;
	}
	
	function process_request($request)
	{
		$tag = qa_request_part(1);
		$qa_content=qa_content_prepare();			
		$qa_content['title']=qa_lang_html_sub('at_lang/edit_detail_for_x', qa_html($tag));
		
		if (qa_user_permit_error('at_lang_permit_edit')) {
			$qa_content['error']=qa_lang_html('users/no_permission');
			return $qa_content;
		}

		require_once QA_INCLUDE_DIR.'db/metas.php';
		require_once QA_INCLUDE_DIR . 'util/image.php';
		
		$iconblobid = qa_db_tagmeta_get($tag, 'iconblobid');
		$userid = qa_get_logged_in_userid();
		
		if (qa_clicked('dosave')) {
			require_once QA_INCLUDE_DIR.'util/string.php';
			
			$taglc = qa_strtolower($tag);
			if (strlen(qa_post_text('tag_wiki'))) 
				qa_db_tagmeta_set($taglc, 'tagwiki', qa_post_text('tag_wiki'));
			if (strlen(qa_post_text('tag_description'))) 
				qa_db_tagmeta_set($taglc, 'description', qa_post_text('tag_description'));
			if (strlen(qa_post_text('tag_advertisement'))) 
				qa_db_tagmeta_set($taglc, 'sponsored', qa_post_text('tag_advertisement'));
			if (is_array(@$_FILES['iconfile'])) {
				$tagfileerror = $_FILES['iconfile']['error'];

				if ($tagfileerror === 1)
					$errors['tag_icon'] = qa_lang('main/file_upload_limit_exceeded');
				elseif ($tagfileerror === 0 && $_FILES['iconfile']['size'] > 0) {
					require_once QA_INCLUDE_DIR . 'app/limits.php';

					switch (qa_user_permit_error(null, QA_LIMIT_UPLOADS)) {
						case 'limit':
							$errors['tag_icon'] = qa_lang('main/upload_limit');
							break;

						default:
							$errors['tag_icon'] = qa_lang('users/no_permission');
							break;

						case false:
							qa_limits_increment($userid, QA_LIMIT_UPLOADS);
							$toobig = qa_image_file_too_big($_FILES['iconfile']['tmp_name'], 500);

							if ($toobig) 
								$errors['tag_icon'] = qa_lang_sub('main/image_too_big_x_pc', (int)($toobig * 100));
							elseif (!at_set_tag_icon($taglc, file_get_contents($_FILES['iconfile']['tmp_name']), $userid, $iconblobid))			
								$errors['tag_icon'] = qa_lang_sub('main/image_not_read', implode(', ', qa_gd_image_formats()));
							break;
					}
				}
			}
			qa_redirect('tag/'.$tag);
		}

		$useraccount = qa_db_select_with_pending(qa_db_user_account_selectspec('james', false));
		
		$iconoptions = array();
		$iconvalue = '';
		$iconoptions['uploaded'] = '<input name="iconfile" type="file">';

		if (strlen($iconblobid)) {
			$iconoptions['uploaded'] = '<span style="margin:2px 0; display:inline-block;">' .
				qa_get_avatar_blob_html($iconblobid, 32, 32, 32) . '</span>' . $iconoptions['uploaded'];
			
			$iconvalue = $iconoptions['uploaded'];

			$iconoptions['remove'] = '<span style="margin:2px 0; display:inline-block;">' .
				qa_lang_html('at_lang/remove_icon') . '</span>';
		}
		
		$qa_content['form'] = array(
			'tags' => 'enctype="multipart/form-data" method="post" action="'.qa_self_html().'"',
			'style' => 'tall',
			
			'fields' => array(
				'tag_icon' => array(
					'type' => 'select-radio',
					'label' => qa_lang_html('at_lang/tag_icon'),
					'tags' => 'name="tag_icon"',
					'options' => $iconoptions,
					'value' => $iconvalue,
					'error' => qa_html(@$errors['tag_icon']),
				),
				
				'tag_description' => array(
					'label' => qa_lang('at_lang/tag_description'),
					'type' => 'textarea',
					'rows' => 4,
					'tags' => 'name="tag_description" id="tag_description"',
					'value' => qa_html(qa_db_tagmeta_get($tag, 'description')),
					'error' => qa_html(@$errors['tag_description']),
				),
				
				'tag_wiki' => array(
					'label' => qa_lang('at_lang/tag_wiki'),
					'type' => 'url',
					'tags' => 'name="tag_wiki" id="tag_wiki"',
					'value' => qa_html(qa_db_tagmeta_get($tag, 'tagwiki')),
				),
				
				'tag_advertisement' => array(
					'label' => qa_lang('at_lang/tag_advertisement'),
					'type' => 'textarea',
					'rows' => 8,
					'tags' => 'name="tag_advertisement" id="tag_advertisement"',
					'value' => qa_html(qa_db_tagmeta_get($tag, 'sponsored')),
				),
				
			),
			
			'buttons' => array(
				array(
					'tags' => 'name="dosave"',
					'label' => qa_lang_html('at_lang/save_tag_button'),
				),
			),			
		);
		
		$qa_content['focusid'] = 'tag_description';

		return $qa_content;
	}
	
}