<?php
/*
PukiWiki - Yet another WikiWikiWeb clone.
pageinfo.inc.php, v1.1.1 2020 M.Taniguchi
License: GPL v3 or (at your option) any later version

ページ情報を表示するPukiWiki用プラグイン。

【使い方】
#pageinfo([label])
&pageinfo([label]);

label … ページ情報表示へのリンクラベル。省略するとアイコン画像がラベルとなる

【使用例】
&pageinfo(ページ情報);
*/

/////////////////////////////////////////////////
// ページ情報表示プラグイン（pageinfo.inc.php）
if (!defined('PLUGIN_PAGEINFO_SHOW_PAGE_FUNCTIONS'))    define('PLUGIN_PAGEINFO_SHOW_PAGE_FUNCTIONS',    0); // ページ操作ツールを表示
if (!defined('PLUGIN_PAGEINFO_SHOW_GENERAL_FUNCTIONS')) define('PLUGIN_PAGEINFO_SHOW_GENERAL_FUNCTIONS', 0); // 一般ツールを表示
if (!defined('PLUGIN_PAGEINFO_SHOW_ATTACHEDFILES'))     define('PLUGIN_PAGEINFO_SHOW_ATTACHEDFILES',     1); // 添付ファイルリストを表示
if (!defined('PLUGIN_PAGEINFO_SHOW_RELATEDPAGES'))      define('PLUGIN_PAGEINFO_SHOW_RELATEDPAGES',      1); // 関連ページリストを表示
if (!defined('PLUGIN_PAGEINFO_SHOW_BASICINFO'))         define('PLUGIN_PAGEINFO_SHOW_BASICINFO',         1); // ページ基本情報を表示
if (!defined('PLUGIN_PAGEINFO_SHOW_VIEWS'))             define('PLUGIN_PAGEINFO_SHOW_VIEWS',             0); // ページ閲覧回数を表示（counter標準プラグインが設置されていなければ無意味）
if (!defined('PLUGIN_PAGEINFO_SHOW_PROTECTION'))        define('PLUGIN_PAGEINFO_SHOW_PROTECTION',        1); // ページ保護情報を表示
if (!defined('PLUGIN_PAGEINFO_SHOW_CMSINFO'))           define('PLUGIN_PAGEINFO_SHOW_CMSINFO',           0); // CMS（PukiWiki）情報を表示
if (!defined('PLUGIN_PAGEINFO_SHOW_SERVERINFO'))        define('PLUGIN_PAGEINFO_SHOW_SERVERINFO',        0); // サーバー情報を表示
if (!defined('PLUGIN_PAGEINFO_SHOW_USERINFO'))          define('PLUGIN_PAGEINFO_SHOW_USERINFO',          1); // 認証ユーザー情報を表示（ログイン時のみ）


function plugin_pageinfo_convert() {
	list($label) = func_get_args();
	return plugin_pageinfo_getlink($label);
}

function plugin_pageinfo_inline() {
	list($label) = func_get_args();
	return plugin_pageinfo_getlink($label);
}

function plugin_pageinfo_getlink($label) {
	global	$vars;
	$page = isset($vars['page'])? $vars['page'] : '';
	$label = ($label)? htmlsc(plugin_pageinfo_trans($label)) : '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAAAe1BMVEUAAAD///+/v78QEBKGhoZCSl/6+vvu7u7k5OQ0Okvx8fHq6+vo6Oje3d7Y2dnPz890dHRjY2NUVFQqLz0hJS8YGyIYGBgICQkCAgL19fXT09OBgYF7e3tra2xbWlpMTE1FRkY6OjsvLy8oKCggISDV1dbMzMxAQEE0NTRB938eAAAAAXRSTlMAQObYZgAAAIZJREFUGNOtyEcSwjAMAECkkNiku6d3yv9fiIFhBMOR7HEPOzh6bVeW3wlPvfjNSID8yNZPGIaDHCfK7pUxwytlCVEdM5akOFP2UJ88znGhFBAnSime4Uo5AEu11lmOG6WEhBtj8gYt5Qhp8FBgQDlBlp+borigo5zxraK8LetmbeBcRfmHO3w1B3ApqDMCAAAAAElFTkSuQmCC" title="' . plugin_pageinfo_trans('Information') . '"/>';
	return '<a href="./?plugin=pageinfo&amp;refer=' . urlencode($page) . '" rel="nofollow">' . $label . '</a>';
}

function plugin_pageinfo_action() {
	global	$vars, $nofollow, $function_freeze, $do_backup, $auth_type, $auth_user, $auth_user_fullname, $auth_user_groups, $whatsnew, $auth_method_type, $read_auth, $read_auth_pages, $edit_auth, $edit_auth_pages, $rss_max, $page_title, $modifier, $modifierlink;

	$nofollow = 1;

	$page = isset($vars['refer'])? $vars['refer'] : '';
	if (!is_page($page) || !check_readable($page, false, false)) $page = '';

	$msg = plugin_pageinfo_trans('Information for $1');
	$thStyle = ' style="text-align:right;width:8em"';
	$tdStyle = ' style="min-width:14.5em"';

	//if (!$page) return array('msg' => $msg, 'body' => 'Page not found.');

	if ($page) {
		$vars['page'] = $page;
		$fullUrl = get_page_uri($page, PKWK_URI_ABSOLUTE);
		$file = get_filename($page);
		$length = filesize($file);
		$modified = format_date(get_filetime($page));
		$encodedPage = urlencode($page);
	}

	$enable_login = $enable_logout = false;
	if (AUTH_TYPE_FORM === $auth_type || AUTH_TYPE_EXTERNAL === $auth_type || AUTH_TYPE_SAML === $auth_type) {
		if ($auth_user) $enable_logout = true;
		else $enable_login = true;
	} else
	if (AUTH_TYPE_BASIC === $auth_type && $auth_user) {
		$enable_logout = true;
	}

	// Title
	//$body = '<h2>Information for <a href="' . get_page_uri($page) . '">' . htmlsc($page) . '</a></h2>';

	// Tools
	if (PLUGIN_PAGEINFO_SHOW_PAGE_FUNCTIONS != 0 || PLUGIN_PAGEINFO_SHOW_GENERAL_FUNCTIONS != 0) {
		$body .= '<p><nav>';

		// Top page link
		//$body .= '[ <a href="./">' . plugin_pageinfo_getLabel('top') . '</a> ]&emsp;';

		// Page functions
		if (PLUGIN_PAGEINFO_SHOW_PAGE_FUNCTIONS != 0 && $page) {
			$nav = '';
			if (exist_plugin_action('edit')) $nav .= '<a href="./?cmd=edit&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('edit') . '</a>';
			if (exist_plugin_action('diff')) $nav .= (($nav)? ' | ' : '') . '<a href="./?cmd=diff&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('diff') . '</a>';
			if ($do_backup && exist_plugin_action('backup')) $nav .= (($nav)? ' | ' : '') . '<a href="./?cmd=backup&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('backup') . '</a>';
			if (exist_plugin_action('attach') && !PKWK_READONLY && (bool)ini_get('file_uploads')) $nav .= (($nav)? ' | ' : '') . '<a href="./?plugin=attach&pcmd=upload&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('upload') . '</a>';
			if (exist_plugin_action('template')) $nav .= (($nav)? ' | ' : '') . '<a href="./?plugin=template&refer=' . $encodedPage . '">' . plugin_pageinfo_getLabel('copy') . '</a>';
			if (!PKWK_READONLY && $function_freeze && exist_plugin_action('freeze') && exist_plugin_action('unfreeze')) $nav .= (($nav)? ' | ' : '') . ((!is_freeze($page))? '<a href="./?cmd=freeze&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('freeze') . '</a>' : '<a href="./?cmd=unfreeze&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('unfreeze') . '</a>');
			if (exist_plugin_action('rename')) $nav .= (($nav)? ' | ' : '') . '<a href="./?plugin=rename&refer=' . $encodedPage . '">' . plugin_pageinfo_getLabel('rename') . '</a>';
			$body .= '[ ' . $nav . ' ]&emsp;';
		}

		// General functions
		if (PLUGIN_PAGEINFO_SHOW_GENERAL_FUNCTIONS != 0) {
			$nav = '';
			if (exist_plugin_action('newpage')) $nav .= '<a href="./?plugin=newpage&refer=' . $encodedPage . '">' . plugin_pageinfo_getLabel('new') . '</a>';
			if (exist_plugin_action('list')) $nav .= (($nav)? ' | ' : '') . '<a href="./?cmd=list">' . plugin_pageinfo_getLabel('list') . '</a>';
			if (exist_plugin_action('search')) $nav .= (($nav)? ' | ' : '') . '<a href="./?cmd=search">' . plugin_pageinfo_getLabel('search') . '</a>';
			if (is_page($whatsnew)) $nav .= (($nav)? ' | ' : '') . '<a href="./?' . $whatsnew . '">' . plugin_pageinfo_getLabel('recent') . '</a>';
			if (is_page('Help')) $nav .= (($nav)? ' | ' : '') . '<a href="./?Help">' . plugin_pageinfo_getLabel('help') . '</a>';
			if (exist_plugin_action('loginform')) {
				if ($enable_login) $nav .= (($nav)? ' | ' : '') . '<a href="./?plugin=loginform&pcmd=login&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('login') . '</a>';
				else if ($enable_logout) $nav .= (($nav)? ' | ' : '') . '<a href="./?plugin=loginform&pcmd=logout&page=' . $encodedPage . '">' . plugin_pageinfo_getLabel('logout') . '</a>';
			}
			$body .= '[ ' . $nav . ' ]&emsp;';

			// Web feed
			if ($rss_max > 0) {
				$nav = '';
				if (exist_plugin_action('rss')) $nav .= '<a href="./?cmd=rss&ver=1.0">RSS</a>';
				if (exist_plugin_action('jsonfeed')) $nav .= (($nav)? ' | ' : '') . '<a href="./?plugin=jsonfeed">JSON Feed</a>';
				if ($nav) $body .= '[ ' . $nav . ' ]';
			}
		}

		$body .= '</nav></p>';
	}

	// Attached files
	if (PLUGIN_PAGEINFO_SHOW_ATTACHEDFILES != 0 && $page && exist_plugin_action('attach')) {
		$vars['pcmd'] = 'list';
		$vars['refer'] = $page;
		$v = do_plugin_action('attach');
		$v = preg_replace('/\<\s*br\s*\/?\>/i', '', $v['body']);
		$body .= '<h3>' . plugin_pageinfo_trans('Attached files') . '</h3><p>' . preg_replace('/\<\s*a\s+[^\<]+\<\/a\>/i', '', $v, 1) . '</p>';
	}

	// Related pages
	if (PLUGIN_PAGEINFO_SHOW_RELATEDPAGES != 0 && $page && exist_plugin_action('related')) {
		$v = do_plugin_action('related');
		$v = preg_replace('/\<\s*br\s*\/?\>/i', '', $v['body']);
		$v = preg_replace('/\<\s*a\s+[^\<]+\<\/a\>/i', '', $v, 1);
		$body .= '<h3>' . plugin_pageinfo_trans('Related pages') . '</h3><p>' . $v . '</p>';
	}

	// Page information
	if ($page && (PLUGIN_PAGEINFO_SHOW_BASICINFO != 0 || (PLUGIN_PAGEINFO_SHOW_VIEWS != 0 && exist_plugin_inline('counter')) || PLUGIN_PAGEINFO_SHOW_PROTECTION != 0)) {
		$body .= '<h3>' . plugin_pageinfo_trans('Page information') . '</h3><p><table class="style_table" cellspacing="1" border="0"><tbody>';

		// Basic information
		if (PLUGIN_PAGEINFO_SHOW_BASICINFO != 0) {
			$body .= '<tr><th class="style_th" colspan="2" style="text-align:center">' . plugin_pageinfo_trans('Basic information') . '</th></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Page name</th><td class="style_td"' . $tdStyle . '>' . htmlsc($page) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Page URL</th><td class="style_td"' . $tdStyle . '>' . htmlsc($fullUrl) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Page length</th><td class="style_td"' . $tdStyle . '>' . number_format($length) . '&thinsp;bytes</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Last modified</th><td class="style_td"' . $tdStyle . '>' . $modified . '</td></tr>';
		}

		// Page views
		if (PLUGIN_PAGEINFO_SHOW_VIEWS != 0 && exist_plugin_inline('counter')) {
			$v = '';
			$views[0] = do_plugin_inline('counter', 'total', $v);
			$views[1] = do_plugin_inline('counter', 'today', $v);
			$views[2] = do_plugin_inline('counter', 'yesterday', $v);

			$body .= '<tr><th class="style_th" colspan="2" style="text-align:center">' . plugin_pageinfo_trans('Page views') . '</th></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Total</th><td class="style_td"' . $tdStyle . '>' . number_format($views[0]) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Today</th><td class="style_td"' . $tdStyle . '>' . number_format($views[1]) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Yesterday</th><td class="style_td"' . $tdStyle . '>' . number_format($views[2]) . '</td></tr>';
		}

		// Page protection
		if (PLUGIN_PAGEINFO_SHOW_PROTECTION != 0) {
			if ($auth_method_type == 'pagename') {
				$readAuth = false;
				if ($read_auth) foreach ($read_auth_pages as $preg => $v) if ($readAuth = preg_match($preg, $page)) break;
				$readAuth = $readAuth ? 'authenticated users only' : 'everyone';

				$editAuth = false;
				if ($edit_auth) foreach ($edit_auth_pages as $preg => $v) if ($editAuth = preg_match($preg, $page)) break;
				$editAuth = $editAuth ? 'authenticated users only' : 'everyone';
			}

			$body .= '<tr><th class="style_th" colspan="2" style="text-align:center">' . plugin_pageinfo_trans('Page protection') . '</th></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Read</th><td class="style_td"' . $tdStyle . '>' . (!$read_auth ? 'everyone' : (isset($readAuth)? $readAuth : 'N/A')) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Edit</th><td class="style_td"' . $tdStyle . '>' . ((!PKWK_READONLY)? (!$edit_auth ? 'everyone' : (isset($editAuth)? $editAuth : 'N/A')) : 'prohibited') . '</td></tr>';
			if ($function_freeze) $body .= '<tr><td class="style_td"' . $thStyle . '>Freezed</th><td class="style_td"' . $tdStyle . '>' . (is_freeze($page) ? 'yes' : 'no') . '</td></tr>';
			if (PKWK_READONLY) $body .= '<tr><td class="style_td"' . $thStyle . '>Read only</th><td class="style_td"' . $tdStyle . '>yes</td></tr>';
		}

		$body .= '</tbody></table></p>';
	}

	// System information
	if (PLUGIN_PAGEINFO_SHOW_CMSINFO != 0 || PLUGIN_PAGEINFO_SHOW_SERVERINFO != 0) {
		$body .= '<h3>' . plugin_pageinfo_trans('System information') . '</h3><p><table class="style_table" cellspacing="1" border="0"><tbody>';
		$thStyle = ' style="text-align:right;width:11.5em"';
		$tdStyle = ' style="min-width:11em"';

		// Content management system
		if (PLUGIN_PAGEINFO_SHOW_CMSINFO != 0) {
			$adminName = ($modifier)? htmlsc($modifier) : 'anonymous';
			$skin = htmlsc(str_replace('.', ' ', str_ireplace('.php', '', end(explode('/', SKIN_FILE)))));

			$body .= '<tr><th class="style_th" colspan="2" style="text-align:center">' . plugin_pageinfo_trans('Content management system') . '</th></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>CMS software</th><td class="style_td"' . $tdStyle . '>PukiWiki ' . S_VERSION . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>CGI scripting</th><td class="style_td"' . $tdStyle . '>PHP ' . PHP_VERSION . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Character set</th><td class="style_td"' . $tdStyle . '>' . CONTENT_CHARSET . '</td></tr>';
			if (LANG == UI_LANG) {
				$body .= '<tr><td class="style_td"' . $thStyle . '>Language</th><td class="style_td"' . $tdStyle . '>' . LANG . '</td></tr>';
			} else {
				$body .= '<tr><td class="style_td"' . $thStyle . '>UI language</th><td class="style_td"' . $tdStyle . '>' . UI_LANG . '</td></tr>';
				$body .= '<tr><td class="style_td"' . $thStyle . '>Content language</th><td class="style_td"' . $tdStyle . '>' . LANG . '</td></tr>';
			}
			$body .= '<tr><td class="style_td"' . $thStyle . '>Local time zone</th><td class="style_td"' . $tdStyle . '>UTC' . sprintf('%+d', round(LOCALZONE / 3600)) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>CMS skin</th><td class="style_td"' . $tdStyle . '>' . $skin . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Site title</th><td class="style_td"' . $tdStyle . '>' . htmlsc($page_title) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Site administrator</th><td class="style_td"' . $tdStyle . '>' . (($modifierlink)? '<a href="' . $modifierlink . '">' . $adminName . '</a>' : $adminName) . '</td></tr>';
		}

		// Server information
		if (PLUGIN_PAGEINFO_SHOW_SERVERINFO != 0) {
			$body .= '<tr><th class="style_th" colspan="2" style="text-align:center">' . plugin_pageinfo_trans('Server information') . '</th></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Server name</th><td class="style_td"' . $tdStyle . '>' . htmlsc(SERVER_NAME) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Server IP address</th><td class="style_td"' . $tdStyle . '>' . $_SERVER['SERVER_ADDR'] . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Web server software</th><td class="style_td"' . $tdStyle . '>' . htmlsc(SERVER_SOFTWARE) . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>Web server port</th><td class="style_td"' . $tdStyle . '>' . (int)SERVER_PORT . '</td></tr>';
			$body .= '<tr><td class="style_td"' . $thStyle . '>HTTPS</th><td class="style_td"' . $tdStyle . '>' . (($_SERVER['HTTPS'])? 'yes' : 'no') . '</td></tr>';
		}

		$body .= '</tbody></table></p>';
	}

	// User information
	if (PLUGIN_PAGEINFO_SHOW_USERINFO != 0 && $auth_user) {
		$body .= '<h3>' . plugin_pageinfo_trans('Authenticated user information') . '</h3><p><table class="style_table" cellspacing="1" border="0"><tbody>';
		$thStyle = ' style="text-align:right;width:8em"';
		$tdStyle = ' style="min-width:8em"';

		$body .= '<tr><td class="style_td"' . $thStyle . '>User name</th><td class="style_td"' . $tdStyle . '>' . ($auth_user ? $auth_user : '-') . '</td></tr>';
		$body .= '<tr><td class="style_td"' . $thStyle . '>Full name</th><td class="style_td"' . $tdStyle . '>' . ($auth_user_fullname ? $auth_user_fullname : '-') . '</td></tr>';
		$body .= '<tr><td class="style_td"' . $thStyle . '>Groups</th><td class="style_td"' . $tdStyle . '>';
		foreach($auth_user_groups as $group) $body .= $group . '<br/>';
		$body .= '</td></tr>';
		$body .= '<tr><td class="style_td"' . $thStyle . '>Auth type</th><td class="style_td"' . $tdStyle . '>' . preg_replace('/\:$/ui', '', get_auth_user_prefix()) . '</td></tr>';
		$body .= '</tbody></table></p>';
	}

	return array('msg' => $msg, 'body' => $body);
}

function plugin_pageinfo_getLabel($key) {
	$lang = &$GLOBALS['_LANG']['skin'];
	return isset($lang[$key])? $lang[$key] : $key;
}

function plugin_pageinfo_trans($str) {
	static	$data = array(
		'ja' => array(
			'Information' => '情報',
			'Page info' => 'ページ情報',
			'Page information' => 'ページ情報',
			'Attached files' => '添付ファイル',
			'Related pages' => '関連ページ',
			'System information' => 'システム情報',
			'Basic information' => '基本情報',
			'Page views' => '閲覧回数',
			'Page protection' => 'ページ保護',
			'Content management system' => '文書管理システム',
			'Server information' => 'サーバー情報',
			'Information for $1' => '$1 の情報',
			'Authenticated user information' => '認証ユーザー情報',
		),
	);

	return (CONTENT_CHARSET == 'UTF-8' && isset($data[UI_LANG][$str]))? $data[LANG][$str] : $str;
}
