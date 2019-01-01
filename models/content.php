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

class content_class extends AWS_MODEL
{
	private $cached_contents = array();

	private function get_cached_content_info($type, $item_id)
	{
		return $this->cached_contents[$type . '_' . $item_id];
	}

	private function cache_content_info($type, $item_id, &$content_info)
	{
		$this->cached_contents[$type . '_' . $item_id] = $content_info;
	}

	public function check_thread_type($type)
	{
		switch ($type)
		{
			case 'question':
			case 'article':
			case 'video':
				return true;
		}
		return false;
	}


	public function check_reply_type($type)
	{
		switch ($type)
		{
			case 'answer':
			case 'article_comment':
			case 'video_comment':
				return true;
		}
		return false;
	}

	public function check_thread_or_reply_type($type)
	{
		switch ($type)
		{
			case 'question':
			case 'answer':
			case 'article':
			case 'article_comment':
			case 'video':
			case 'video_comment':
				return true;
		}
		return false;
	}

	public function check_item_type($type)
	{
		switch ($type)
		{
			case 'question':
			case 'answer':
			case 'question_discussion':
			case 'answer_discussion':
			case 'article':
			case 'article_comment':
			case 'video':
			case 'video_comment':
				return true;
		}
		return false;
	}


	public function get_thread_info_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_thread_type($type))
		{
			return false;
		}

		$item_info = $this->get_cached_content_info($type, $item_id);
		if (isset($item_info))
		{
			return $item_info;
		}

		$where = 'id = ' . ($item_id);

		$item_info = $this->fetch_row($type, $where);

		$this->cache_content_info($type, $item_id, $item_info);
		return $item_info;
	}


	public function get_reply_info_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_reply_type($type))
		{
			return false;
		}

		$item_info = $this->get_cached_content_info($type, $item_id);
		if (isset($item_info))
		{
			return $item_info;
		}

		$where = 'id = ' . ($item_id);

		$item_info = $this->fetch_row($type, $where);

		$this->cache_content_info($type, $item_id, $item_info);
		return $item_info;
	}


	public function get_thread_or_reply_info_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_thread_or_reply_type($type))
		{
			return false;
		}

		$item_info = $this->get_cached_content_info($type, $item_id);
		if (isset($item_info))
		{
			return $item_info;
		}

		$where = 'id = ' . ($item_id);

		$item_info = $this->fetch_row($type, $where);

		$this->cache_content_info($type, $item_id, $item_info);
		return $item_info;
	}

	public function get_item_parent_info_by_id($type, $item_id)
	{
		$item_info = $this->get_thread_or_reply_info_by_id($type, $item_id);
		if (!$item_info)
		{
			return false;
		}

		switch ($type)
		{
			case 'question':
			case 'article':
			case 'video':
				return $item_info;

			case 'answer':
				return $this->get_thread_info_by_id('question', $item_info['question_id']);

			case 'article_comment':
				return $this->get_thread_info_by_id('article', $item_info['article_id']);

			case 'video_comment':
				return $this->get_thread_info_by_id('video', $item_info['video_id']);
		}

		return false;
	}

	/**
	 * 记录日志
	 * @param string $thread_type question|article|video
	 * @param int $thread_id
	 * @param string $item_type question|question_discussion|answer|answer_discussion|article|article_comment|video|video_comment
	 * @param int $item_id
	 * @param string $note
	 * @param int $uid
	 * @param string $child_type 附加内容type
	 * @param int $child_id 附加内容id
	 */
	public function log($thread_type, $thread_id, $item_type, $item_id, $note, $uid = 0, $child_type = null, $child_id = 0)
	{
		if (!$uid = intval($uid))
		{
			return;
		}
		$this->insert('content_log', array(
			'thread_type' => $thread_type,
			'thread_id' => intval($thread_id),
			'item_type' => $item_type,
			'item_id' => intval($item_id),
			'note' => $note,
			'uid' => ($uid),
			'child_type' => $child_type,
			'child_id' => intval($child_id),
			'time' => fake_time()
		));
	}

	/**
	 *
	 * 得到日志列表
	 *
	 * @param string  $thread_type question|article|video
	 * @param int     $thread_id
	 * @param string  $item_type
	 * @param int     $item_id
	 * @param int     $uid
	 * @param int     $page
	 * @param int     $per_page
	 *
	 * @return array
	 */
	public function list_logs($thread_type, $thread_id, $item_type, $item_id, $uid, $page, $per_page)
	{
		if ($thread_type AND !$this->check_thread_type($thread_type))
		{
			return false;
		}
		if ($item_type AND !$this->check_item_type($item_type))
		{
			return false;
		}

		$where = array();
		if ($thread_type)
		{
			$where[] = "`thread_type` = '" . ($thread_type) . "'";
		}
		if ($thread_id = intval($thread_id))
		{
			$where[] = "thread_id = " . ($thread_id);
		}
		if ($item_type)
		{
			$where[] = "`item_type` = '" . ($item_type) . "'";
		}
		if ($item_id = intval($item_id))
		{
			$where[] = "item_id = " . ($item_id);
		}
		if ($uid = intval($uid))
		{
			$where[] = "uid = " . ($uid);
		}

		$log_list = $this->fetch_page('content_log', implode(' AND ', $where), 'id DESC', $page, $per_page);
		if (!$log_list)
		{
			return false;
		}

		foreach ($log_list AS $key => $log)
		{
			$user_ids[] = $log['uid'];
		}

		if ($user_ids)
		{
			$users = $this->model('account')->get_user_info_by_uids($user_ids);
		}
		else
		{
			$users = array();
		}

		foreach ($log_list as $key => $log)
		{
			$log_list[$key]['user_info'] = $users[$log['uid']];
		}

		return $log_list;
	}

	public function delete_expired_logs()
	{
		$days = intval(get_setting('expiration_content_logs'));
		if (!$days)
		{
			return;
		}
		$seconds = $days * 24 * 3600;
		$time_before = real_time() - $seconds;
		if ($time_before < 0)
		{
			$time_before = 0;
		}
		$this->delete('content_log', 'time < ' . $time_before);
	}

	public function update_view_count($item_type, $item_id, $session_id)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$key = 'update_view_count_' . $item_type . '_' . intval($item_id) . '_' . md5($session_id);
		if (AWS_APP::cache()->get($key))
		{
			return false;
		}

		AWS_APP::cache()->set($key, time(), 60);

		// TODO: 统一字段名称避免特殊处理
		//$this->shutdown_query("UPDATE " . $this->get_table($item_type) . " SET view_count = view_count + 1 WHERE id = " . intval($item_id));
		if ($item_type == 'question')
		{
			$this->shutdown_query("UPDATE " . $this->get_table('question') . " SET view_count = view_count + 1 WHERE id = " . intval($item_id));
		}
		elseif ($item_type == 'article')
		{
			$this->shutdown_query("UPDATE " . $this->get_table('article') . " SET views = views + 1 WHERE id = " . intval($item_id));
		}
		elseif ($item_type == 'video')
		{
			$this->shutdown_query("UPDATE " . $this->get_table('video') . " SET view_count = view_count + 1 WHERE id = " . intval($item_id));
		}

		return true;
	}

	public function change_uid($item_type, $item_id, $new_uid, $old_uid, $log_uid)
	{
		$new_uid = intval($new_uid);
		if (!$new_uid OR $new_uid == $old_uid)
		{
			return false;
		}

		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('uid' => ($new_uid)), $where);

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";
		$this->update('posts_index', array('uid' => ($new_uid)), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '变更作者', $log_uid, 'user', $old_uid);

		return true;
	}

	public function change_category($item_type, $item_id, $category_id, $old_category_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('category_id' => intval($category_id)), $where);

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";
		$this->update('posts_index', array('category_id' => intval($category_id)), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '变更分类', $log_uid, 'category', $old_category_id);

		return true;
	}


	public function lock($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('lock' => 1), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '锁定', $log_uid);

		return true;
	}

	public function unlock($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('lock' => 0), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '取消锁定', $log_uid);

		return true;
	}


	public function bump($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";

		$this->update('posts_index', array(
			'update_time' => $this->model('posts')->get_last_update_time()
		), $where);

		$this->model('content')->log($item_type, $item_id, '提升', $log_uid);

		return true;
	}

	public function sink($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";

		$this->update('posts_index', array(
			'update_time' => $this->model('posts')->get_last_update_time() - (7 * 24 * 3600)
		), $where);

		$this->model('content')->log($item_type, $item_id, '下沉', $log_uid);

		return true;
	}

	public function recommend($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('recommend' => 1), $where);

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";
		$this->update('posts_index', array('recommend' => 1), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '推荐', $log_uid);
	}

	public function unrecommend($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('recommend' => 0), $where);

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";
		$this->update('posts_index', array('recommend' => 0), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '取消推荐', $log_uid);
	}


	public function pin($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('sort' => 1), $where);

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";
		$this->update('posts_index', array('sort' => 1), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '置顶', $log_uid);
	}

	public function unpin($item_type, $item_id, $log_uid)
	{
		if (!$this->check_thread_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);
		$this->update($item_type, array('sort' => 0), $where);

		$where = "post_id = " . intval($item_id) . " AND post_type = '" . $this->quote($item_type) . "'";
		$this->update('posts_index', array('sort' => 0), $where);

		$this->model('content')->log($item_type, $item_id, $item_type, $item_id, '取消置顶', $log_uid);
	}


	public function fold_reply($item_type, $item_id, $parent_type, $parent_id, $log_uid)
	{
		if (!$this->check_reply_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);

		$this->update($item_type, array('fold' => 1), $where);

		$this->model('content')->log($parent_type, $parent_id, $item_type, $item_id, '折叠', $log_uid);
	}

	public function unfold_reply($item_type, $item_id, $parent_type, $parent_id, $log_uid)
	{
		if (!$this->check_reply_type($item_type))
		{
			return false;
		}

		$where = 'id = ' . intval($item_id);

		$this->update($item_type, array('fold' => 0), $where);

		$this->model('content')->log($parent_type, $parent_id, $item_type, $item_id, '取消折叠', $log_uid);
	}


}