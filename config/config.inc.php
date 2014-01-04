<?php
// 웹에서 직접 호출되는 것을 막기 위한 조치(ZBXE 스타일)
//if(!defined('__ENC__')) exit();

/**
 * @brief ENC 솔루션이 설치된 장소의 base path
 */
define('_PATH_', str_replace('config/config.inc.php', '', str_replace('\\', '/', __FILE__)));
define('_ABS_PATH_', str_replace('config/config.inc.php', '', str_replace(str_replace('/enc/config/config.inc.php', '', str_replace('\\', '/', __FILE__)), '', str_replace('\\', '/', __FILE__))));
// 시스템에서 첨부파일이 저장되는 기본 경로
define("_IMG_RELATIVE_PATH_", "files/attach/images/");

// DB 접속 정보
// Mysql.class.php에서 config.DB.inc.php 파일을 require_once 하고 있기 때문에 
// Mysql.class.php 하나만 require_once하면 자동으로 config.DB.inc.php 파일도 포함되게 됨. 
// 그런데, DB를 사용하는 곳에서는 항상 Mysql.class.php 파일을 require_once 해야 하므로 
// 별도로 다른 파일에서 또 한번 config.DB.inc.php 파일을 require_once 할 필요는 없음. 
// 그래서, 아래 부분은 주석 처리함. 
//require_once("config.DB.inc.php");
?>