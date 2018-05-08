<?php
/**
 * Created by Sublime.
 * Desc: 方法配置,主要配置具体方法的返回的加密方式等
 * User: yang
 * Date: 17/05/24
 * Time: 11:07
 */



return [
	#数据库对应表
	'LOGDATABASETYPE'				=> array(
											"actions"			=> 'log_actions',
											"clicks"			=> 'log_clicks',
											"content"			=> 'log_content',
										),
	#日活路径（flurry）
	"ACTIVEUSER"					=> "/data/web/manga/spiderData/active_user",

	#日活路径（flurry-en）
	"ACTIVEUSER-EN"					=> "/data/web/manga/spiderData/active_user-en",

	#次留路径（flurry）
	"RETENTION"						=> "/data/web/manga/spiderData/retention",

	#次留路径（flurry-en）
	"RETENTION-EN"					=> "/data/web/manga/spiderData/retention-en",
];
