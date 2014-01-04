<?php
// 데이터베이스 접속 정보 설정 
// 이 config.DB.inc.php 파일은 DB와 관련된 클래스 정의를 모아놓은 Mysql.class.php 파일에서 require_once 하기 때문에 
// Mysql.class.php 하나만 require_once 하면 이 파일 역시 자동으로 포함되게 됨. 
define('_DB_HOST_', 'localhost');
define('_DB_USER_', 'root');
define('_DB_PASSWORD_', 'apmsetup');
define('_DB_DATABASE_', 'm3');
?>