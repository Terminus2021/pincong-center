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

class HTTP
{

	/**
	 * NO CACHE 文件头
	 *
	 * @param $type
	 * @param $charset
	 */
	public static function no_cache_header($type = 'text/html', $charset = 'utf-8')
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Pragma: no-cache');
		header('Content-Type: ' . $type . '; charset=' . $charset . '');
	}

	/**
	 * 获取 COOKIE
	 *
	 * @param $name
	 */
	public static function get_cookie($name)
	{
		if (isset($_COOKIE[G_COOKIE_PREFIX . $name]))
		{
			return $_COOKIE[G_COOKIE_PREFIX . $name];
		}

		return false;
	}

	/**
	 * 设置 COOKIE
	 *
	 * @param $name
	 * @param $value
	 * @param $expire
	 * @param $path
	 * @param $domain
	 * @param $secure
	 * @param $httponly
	 */
	public static function set_cookie($name, $value = '', $expire = null, $path = '/', $domain = null, $secure = false, $httponly = true)
	{
		if (! $domain and G_COOKIE_DOMAIN)
		{
			$domain = G_COOKIE_DOMAIN;
		}

		return setcookie(G_COOKIE_PREFIX . $name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	public static function error_403()
	{
		if ($_POST['_post_type'] == 'ajax')
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, 'HTTP/1.1 403 Forbidden'));
		}
		else
		{
			header('HTTP/1.1 403 Forbidden');

			echo TPL::render('global/error_403');
			exit;
		}
	}

	public static function error_404()
	{
		if ($_POST['_post_type'] == 'ajax')
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, 'HTTP/1.1 404 Not Found'));
		}
		else
		{
			header('HTTP/1.1 404 Not Found');

			echo TPL::render('global/error_404');
			exit;
		}
	}

	public static function parse_redirect_url($url)
	{
		if (!$url)
		{
			$url = '/';
		}
		if (!is_website($url))
		{
			return url_rewrite($url);
		}
		return $url;
	}

	public static function redirect($url)
	{
		if ($url = HTTP::parse_redirect_url($url))
		{
			header('Location: ' . $url);
			exit;
		}
	}


	/**
	 * Browser detection system - returns whether or not the visiting browser is the one specified
	 *
	 * @param	string	Browser name (opera, ie, mozilla, firebord, firefox... etc. - see $is array)
	 * @param	float	Minimum acceptable version for true result (optional)
	 *
	 * @return	boolean
	 */
	public static function is_browser($browser, $version = 0)
	{
		static $is;

		if (! is_array($is))
		{
			$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

			$is = array(
				'opera' => 0,
				'ie' => 0,
				'mozilla' => 0,
				'firebird' => 0,
				'firefox' => 0,
				'camino' => 0,
				'konqueror' => 0,
				'safari' => 0,
				'webkit' => 0,
				'webtv' => 0,
				'netscape' => 0,
				'mac' => 0
			);

			// detect opera
			# Opera/7.11 (Windows NT 5.1; U) [en]
			# Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.0) Opera 7.02 Bork-edition [en]
			# Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 4.0) Opera 7.0 [en]
			# Mozilla/4.0 (compatible; MSIE 5.0; Windows 2000) Opera 6.0 [en]
			# Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC) Opera 5.0 [en]
			if (strpos($useragent, 'opera') !== false)
			{
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			}

			// detect internet explorer
			# Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Q312461)
			# Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.0.3705)
			# Mozilla/4.0 (compatible; MSIE 5.22; Mac_PowerPC)
			# Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC; e504460WanadooNL)
			if (strpos($useragent, 'msie ') !== false and ! $is['opera'])
			{
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}

			// detect macintosh
			if (strpos($useragent, 'mac') !== false)
			{
				$is['mac'] = 1;
			}

			// detect safari
			# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/74 (KHTML, like Gecko) Safari/74
			# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/51 (like Gecko) Safari/51
			if (strpos($useragent, 'applewebkit') !== false and $is['mac'])
			{
				preg_match('#applewebkit/(\d+)#', $useragent, $regs);
				$is['webkit'] = $regs[1];

				if (strpos($useragent, 'safari') !== false)
				{
					preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
					$is['safari'] = $regs[1];
				}
			}

			// detect konqueror
			# Mozilla/5.0 (compatible; Konqueror/3.1; Linux; X11; i686)
			# Mozilla/5.0 (compatible; Konqueror/3.1; Linux 2.4.19-32mdkenterprise; X11; i686; ar, en_US)
			# Mozilla/5.0 (compatible; Konqueror/2.1.1; X11)
			if (strpos($useragent, 'konqueror') !== false)
			{
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}

			// detect mozilla
			# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.4b) Gecko/20030504 Mozilla
			# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.2a) Gecko/20020910
			# Mozilla/5.0 (X11; U; Linux 2.4.3-20mdk i586; en-US; rv:0.9.1) Gecko/20010611
			if (strpos($useragent, 'gecko') !== false and ! $is['safari'] and ! $is['konqueror'])
			{
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = $regs[1];

				// detect firebird / firefox
				# Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.3a) Gecko/20021207 Phoenix/0.5
				# Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4b) Gecko/20030516 Mozilla Firebird/0.6
				# Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4a) Gecko/20030423 Firebird Browser/0.6
				# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.6) Gecko/20040206 Firefox/0.8
				if (strpos($useragent, 'firefox') !== false or strpos($useragent, 'firebird') !== false or strpos($useragent, 'phoenix') !== false)
				{
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];

					if ($regs[1] == 'firefox')
					{
						$is['firefox'] = $regs[3];
					}
				}

				// detect camino
				# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-US; rv:1.0.1) Gecko/20021104 Chimera/0.6
				if (strpos($useragent, 'chimera') !== false or strpos($useragent, 'camino') !== false)
				{
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}

			// detect web tv
			if (strpos($useragent, 'webtv') !== false)
			{
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}

			// detect pre-gecko netscape
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs))
			{
				$is['netscape'] = "$regs[1].$regs[2]";
			}
		}

		// sanitize the incoming browser name
		$browser = strtolower($browser);
		if (substr($browser, 0, 3) == 'is_')
		{
			$browser = substr($browser, 3);
		}

		// return the version number of the detected browser if it is the same as $browser
		if ($is["$browser"])
		{
			// $version was specified - only return version number if detected version is >= to specified $version
			if ($version)
			{
				if ($is["$browser"] >= $version)
				{
					return $is["$browser"];
				}
			}
			else
			{
				return $is["$browser"];
			}
		}

		// if we got this far, we are not the specified browser, or the version number is too low
		return 0;
	}

}