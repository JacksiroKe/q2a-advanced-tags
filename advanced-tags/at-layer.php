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

	require_once QA_INCLUDE_DIR.'db/metas.php';
			
	class qa_html_theme_layer extends qa_html_theme_base
	{

		function doctype() 
		{		
			if (strpos($this->request,'tag/') !== false)
			{
				require_once QA_INCLUDE_DIR . 'db/selects.php';
				require_once QA_INCLUDE_DIR . 'app/format.php';
				require_once QA_INCLUDE_DIR . 'app/updates.php';

				$tag = qa_request_part(1); // picked up from qa-page.php
				$start = qa_get_start();
				$userid = qa_get_logged_in_userid();

				// Find the questions with this tag
				if (!strlen($tag)) qa_redirect('tags');

				list($questions, $tagword) = qa_db_select_with_pending(
					qa_db_tag_recent_qs_selectspec($userid, $tag, $start, false, qa_opt_if_loaded('page_size_tag_qs')),
					qa_db_tag_word_selectspec($tag)
				);

				$pagesize = qa_opt('page_size_tag_qs');
				$questions = array_slice($questions, 0, $pagesize);
				$usershtml = qa_userids_handles_html($questions);


				// Prepare content for theme
				$this->content = qa_content_prepare(true);
				$this->content['title'] = qa_lang_html_sub('main/questions_tagged_x', qa_html($tag));

				if (isset($userid) && isset($tagword)) {
					$favoritemap = qa_get_favorite_non_qs_map();
					$favorite = @$favoritemap['tag'][qa_strtolower($tagword['word'])];

					$this->content['favorite'] = qa_favorite_form(QA_ENTITY_TAG, $tagword['wordid'], $favorite,
						qa_lang_sub($favorite ? 'main/remove_x_favorites' : 'main/add_tag_x_favorites', $tagword['word']));
				}
								
				if (!count($questions)) $this->content['q_list']['title'] = qa_lang_html('main/no_questions_found');
				
				$description = qa_db_tagmeta_get($tag, 'description');
				$iconblobid = qa_db_tagmeta_get($tag, 'iconblobid');
				$sponsored = qa_db_tagmeta_get($tag, 'sponsored');
				$tagwiki = qa_db_tagmeta_get($tag, 'tagwiki');
				
				$allowediting =! qa_user_permit_error('at_lang_permit_edit');
				
				$at_html = '<div style="min-width: 200px;">';
				if (strlen($iconblobid)) 
					$at_html .= '<div style="margin:2px 0; float:left;">' .
				qa_get_avatar_blob_html($iconblobid, 175, 175, 175) . '</div>';
				
				if (strlen($description)) $at_html .= '<p><b>'.qa_html($tag).'</b>: '.$description.'</p>';
				else  $at_html .= '<p>'.at_html_suggest_edit_tags($tag).'</p>';
				if (strlen($tagwiki)) $at_html .= at_html_tag_wiki($tagwiki);

				if ($allowediting)
					$at_html .= '<a title="'.qa_lang_html_sub('at_lang/edit_detail_for_x', qa_html($tag)).
						'" class="qa-form-light-button qa-form-light-button-edit" href="'.
						qa_path_html('tag_edit/'.$tag).'"></a><hr>';
								
				if (strlen($sponsored)) 
					$at_html .= '<div style="margin: 10px; padding: 10px; border:2px solid #000; width: 100%;clear: both;">'.
						qa_lang('at_lang/sponsored_content').$sponsored . '</div>';
				
				
				$at_html .= '</div>';
				
				$this->content['custom'] = $at_html;
				$this->content['q_list']['form'] = array(
					'tags' => 'method="post" action="' . qa_self_html() . '"',

					'hidden' => array(
						'code' => qa_get_form_security_code('vote'),
					),
				);

				$this->content['q_list']['qs'] = array();
				foreach ($questions as $postid => $question) {
					$this->content['q_list']['qs'][] =
						qa_post_html_fields($question, $userid, qa_cookie_get(), $usershtml, null, qa_post_html_options($question));
				}

				$this->content['canonical'] = qa_get_canonical();

				$this->content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $tagword['tagcount'], qa_opt('pages_prev_next'));

				if (empty($this->content['page_links']))
					$this->content['suggest_next'] = qa_html_suggest_qs_tags(true);

				if (qa_opt('feed_for_tag_qs')) {
					$this->content['feed'] = array(
						'url' => qa_path_html(qa_feed_request('tag/' . $tag)),
						'label' => qa_lang_html_sub('main/questions_tagged_x', qa_html($tag)),
					);
				}

				return $this->content;
			}
			qa_html_theme_base::doctype();
		}
		
		public function post_tag_itemy($taghtml, $class)
		{
			$this->output('<li class="' . $class . '-tag-item">' . $taghtml . '</li>');
			
			/*$iconblobid = qa_db_tagmeta_get($tag, 'iconblobid');
			if (strlen($iconblobid)) 
				$at_html .= '<div style="margin:2px 0; float:left;">' .
			qa_get_avatar_blob_html($iconblobid, 175, 175, 175) . '</div>';
			*/
				
		}

		function post_tag_item($taghtml, $class)
		{
			global $at_lang_list, $at_lang_map, $at_lang_img;
			if (count(@$at_lang_list)) {
				$result_desc = qa_db_query_sub(
					'SELECT tag, content FROM ^tagmetas WHERE tag IN ($) AND title="description"',
					array_keys($at_lang_list)
				);
				
				$result_icon = qa_db_query_sub(
					'SELECT tag, content FROM ^tagmetas WHERE tag IN ($) AND title="iconblobid"',
					array_keys($at_lang_list)
				);
				
				$at_lang_map = qa_db_read_all_assoc($result_desc, 'tag', 'content');
				$at_lang_img = qa_db_read_all_assoc($result_icon, 'tag', 'content');
				$at_lang_list = null;			
			}
			
			if (preg_match('/,TAG_DESC,([^,]*),/', $taghtml, $matches)) {
				$taglc = $matches[1];
				$description = @$at_lang_map[$taglc];
				$tag_image = @$at_lang_img[$taglc];
				$description = qa_shorten_string_line($description, qa_opt('at_lang_max_len'));
				$taghtml = str_replace($matches[0], qa_html($description), $taghtml);
				if (strlen($tag_image)) $tagicon = qa_get_avatar_blob_html($tag_image, 32, 32, 32);
				else $tagicon = '';
			}
			
			$this->output('<li class="' . $class . '-tag-item">' . $tagicon . $taghtml . '</li>');
			//qa_html_theme_base::post_tag_item($taghtml, $class);
		}
	}