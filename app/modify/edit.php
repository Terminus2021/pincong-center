<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class edit extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';

		$rule_action['redirect'] = false; // 不跳转到登录页面直接输出403
		return $rule_action;
	}

	public function answer_action()
	{
		$id = intval($_GET['id']);
		if (!$id)
		{
			HTTP::error_403();
		}
		if (!$answer_info = $this->model('content')->get_reply_info_by_id('answer', $id))
		{
			HTTP::error_403();
		}
		if ($answer_info['uid'] != $this->user_id AND !$this->user_info['permission']['edit_any_post'] AND !$this->user_info['permission']['edit_specific_post'])
		{
			HTTP::error_403();
		}

		TPL::assign('answer_info', $answer_info);
		TPL::output("publish/edit_answer_template");
	}

	public function article_comment_action()
	{
		$id = intval($_GET['id']);
		if (!$id)
		{
			HTTP::error_403();
		}
		if (!$comment_info = $this->model('content')->get_reply_info_by_id('article_comment', $id))
		{
			HTTP::error_403();
		}
		if ($comment_info['uid'] != $this->user_id AND !$this->user_info['permission']['edit_any_post'] AND !$this->user_info['permission']['edit_specific_post'])
		{
			HTTP::error_403();
		}

		TPL::assign('comment_info', $comment_info);
		TPL::output("publish/edit_article_comment_template");
	}

	public function video_comment_action()
	{
		$id = intval($_GET['id']);
		if (!$id)
		{
			HTTP::error_403();
		}
		if (!$comment_info = $this->model('content')->get_reply_info_by_id('video_comment', $id))
		{
			HTTP::error_403();
		}
		if ($comment_info['uid'] != $this->user_id AND !$this->user_info['permission']['edit_any_post'] AND !$this->user_info['permission']['edit_specific_post'])
		{
			HTTP::error_403();
		}

		TPL::assign('comment_info', $comment_info);
		TPL::output("publish/edit_video_comment_template");
	}

}
