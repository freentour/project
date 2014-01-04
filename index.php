<?php
require_once("classes/page.class.php");

// QueryString 문자열 설정
$arr_get = array("mode", "id", "pid", "page_no", "count");

foreach ($arr_get as $value) {
	${$value} = $_GET[$value];
}

// 기본값 설정
if (!isset($mode)) {
	//$mode = "display";
	$mode = "display_m";
}

if (!isset($page_no)) {
	$page_no = 1;		// 현재 페이지 번호(list 모드에서)
}

if (!isset($count)) {
	$count = 20;		// 페이지당 출력 항목 갯수(list 모드에서)
}

// page 객체 생성
$page = new page();

// 입력값 체크
if (isset($id) && !is_numeric($id)) {
	$message = "id에 숫자가 아닌 값이 입력되었습니다. id는 숫자여야 합니다.";
	$page->displayErrorPage($message);
	exit;
}

if (!in_array($mode, array("display","display_m","list","create","createform","update","updateform","delete"))) {
	$message = "mode에 허용되지 않은 값이 입력되었습니다. mode는 display, display_m, list, create, createform, update, updateform, delete 중에 하나여야 합니다.";
	$page->displayErrorPage($message);
	exit;
}

if (in_array($mode, array("list","create","createform")) && (isset($id) || isset($pid))) {
	$message = "list, create, createform 모드에서는 id와 pid 파라미터를 사용하지 않습니다. id와 pid 파라미터는 제외해주세요.";
	$page->displayErrorPage($message);
	exit;
}

if (in_array($mode, array("update","updateform","delete")) && (!isset($id))) {
	$message = "id에 값이 입력되지 않았습니다. update, updateform, delete 모드에서 id 파라미터는 필수입니다.";
	$page->displayErrorPage($message);
	exit;
}

if (in_array($mode, array("display","display_m")) && (!isset($id) && !isset($pid))) {
	$message = "id와 pid에 모두 값이 입력되지 않았습니다. display, display_m 모드에서 id 또는 pid 파라미터는 필수입니다.";
	$page->displayErrorPage($message);
	exit;
}

// 관리자 권한 필요한 모드 지정
$arr_manage_mode = array("display_m","list","create","createform","update","updateform","delete");

// 관리자 권한 체크
if (in_array($mode, $arr_manage_mode)) {

}

// mode에 따라 해당 메소드 호출
switch ($mode) {
	case "display" :
		if (isset($id)) {
			$page->displayPageById($id);
		} elseif (isset($pid)) {
			$page->displayPageByPid($pid);
			//$page->searchPageByPid($pid);
			//echo $page->xmlSerializeAsDocument();
		}
		break;
	case "display_m" : 
		if (isset($id)) {
			$page->displayPageWithManageById($id);
		} elseif (isset($pid)) {
			$page->displayPageWithManageByPid($pid);
		}
		break;
	case "list" : 
		$page->displayPageList(intval($page_no), intval($count));
		break;
	case "create" : 
		$page->createPage();
		// 생성 후 곧바로 전체 페이지 리스트 출력하기
		$page->displayPageList(intval($page_no), intval($count));
		break;
	case "update" : 
		$page->updatePage($id);
		// 수정 후 곧바로 수정된 페이지 출력하기
		$page->displayPageWithManageById($id);
		break;
	case "delete" : 
		$page->deletePage($id);
		// 삭제 후 곧바로 전체 페이지 리스트 출력하기
		$page->displayPageList(intval($page_no), intval($count));
		break;
	case "createform" : 
		$page->displayCreatePage("Create a Page");
		break;
	case "updateform" : 
		$page->displayUpdatePageById($id, "Update the Page #" . $id);
		break;
}
?>