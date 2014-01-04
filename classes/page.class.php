<?php
require_once("myDB.class.php");

class page extends myDB {

	protected $title;
	protected $description;
	protected $keywords;
	protected $external_style;
	protected $internal_style;
	protected $external_script;
	protected $internal_script;
	protected $body;
	
	public function __construct() {
		// 데이터베이스 연결
		$this->connect();
	}
	
	// 파라미터로 넘어온 $sql 문을 이용해서 페이지 검색하기
	private function searchPage($sql) {
		// select SQL문 실행
		$result = $this->select($sql);
		// id 또는 pid 컬럼을 사용해서 쿼리하므로 무조건 1행만 넘어오는 상황. 따라서, while문 사용하지 않음
		$row = $result->fetch_assoc();
		
		// 연관되어진 프라퍼티로 값을 세팅
		$this->title = trim($row['title']);
		$this->description = trim($row['description']);
		$this->keywords = trim($row['keywords']);
		$this->external_style = trim($row['external_style']);
		$this->internal_style = trim($row['internal_style']);
		$this->external_script = trim($row['external_script']);
		$this->internal_script = trim($row['internal_script']);
		$this->body = $row['body'];
	}
	
	// xml 출력하기전 호출하는 메소드(테스트)
	public function searchPageById($id, $type="general") {
		// SQL문 조립
		$sql = "SELECT * FROM pages WHERE id = '" . $id . "' AND type = '" . $type . "'";
		$this->searchPage($sql);
	}
	
	// xml 출력하기전 호출하는 메소드(테스트)
	public function searchPageByPid($pid, $type="general") {
		// SQL문 조립
		$sql = "SELECT * FROM pages WHERE pid = '" . $pid . "' AND type = '" . $type . "'";
		$this->searchPage($sql);
	}
	
	public function displayPageById($id, $type="general", $title="", $html_string="") {
		// SQL문 조립
		$sql = "SELECT * FROM pages WHERE id = " . $id . " AND type = '" . $type . "'";
		$this->searchPage($sql);
		$this->displayPage($title, $html_string);
	}
	
	public function displayPageByPid($pid, $type="general", $title="", $html_string="") {
		// SQL문 조립
		$sql = "SELECT * FROM pages WHERE pid = '" . $pid . "' AND type = '" . $type . "'";
		$this->searchPage($sql);
		$this->displayPage($title, $html_string);
	}
	
	public function displayPageWithManageById($id, $type="general", $title="", $html_string="") {
		// SQL문 조립
		$sql = "SELECT * FROM pages WHERE id = " . $id . " AND type = '" . $type . "'";
		$this->searchPage($sql);
		
		// 페이지 관리 도구 추가
		$str_manage = "<ul id=\"page_tool\">\n";
		$str_manage .= "\t<li><a href=\"index.php?mode=list\">Page List</a></li>\n";
		$str_manage .= "\t<li><a href=\"index.php?id=" . $id . "&mode=updateform\">Update</a></li>\n";
		$str_manage .= "\t<li><a href=\"index.php?id=" . $id . "&mode=delete\">Delete</a></li>\n";
		$str_manage .= "</ul>\n";
		$this->body = $str_manage . $this->body;
		
		$this->displayPage($title, $html_string);
	}
	
	public function displayPageWithManageByPid($pid, $type="general", $title="", $html_string="") {
		// SQL문 조립
		$sql = "SELECT * FROM pages WHERE pid = '" . $pid . "' AND type = '" . $type . "'";
		$this->searchPage($sql);

		// 페이지 관리 도구 추가
		$str_manage = "<ul id=\"page_tool\">\n";
		$str_manage .= "\t<li><a href=\"index.php?mode=list\">Page List</a></li>\n";
		$str_manage .= "\t<li><a href=\"index.php?pid=" . $pid . "&mode=updateform\">Update</a></li>\n";
		$str_manage .= "\t<li><a href=\"index.php?pid=" . $pid . "&mode=delete\">Delete</a></li>\n";
		$str_manage .= "</ul>\n";
		$this->body = $str_manage . $this->body;
		
		$this->displayPage($title, $html_string);
	}
	
	public function displayPageList($page_no=1, $count_per_page=20) {
		// SQL문 조립
		//$sql = "SELECT * FROM pages WHERE pid != '_default'";
		$sql = "SELECT * FROM pages LIMIT " . ($page_no-1)*$count_per_page . ", " . $count_per_page;
		
		// select SQL문 실행
		$result = $this->select($sql);

		$title = "Web Page List";
		$html_string = "<h1>" . $title . "</h1>\n";
		
		// 출력을 원하는 컬럼 목록 지정
		$columns = array("id","pid","type","title","external_style","external_script");
		$html_string .= $this->getHtmlTagByResultSet($result, $columns, "manage");
		// 모든 컬럼 출력하기(일반 모드)
		//$body .= $this->getHtmlTagByResultSet($result);	
		// 모든 컬럼 출력하기(관리자 모드)
		//$body .= $this->getHtmlTagByResultSet($result, "all", "manage");	
		
		$this->displayPageByPid("_default", "manage", $title, $html_string);
	}
	
	public function displayCreatePage($title="") {
		if ($title != "") {
			$html_string = "<h1>" . $title . "</h1>\n";
		} else {
			$html_string = "";
		}
		
		$html_string .= $this->getCreateForm("pages");
		$this->displayPageByPid("_default", "general", $title, $html_string);
	}
	
	public function createPage() {
		// pages 태이블의 구조를 읽어서 'column_name=>column_data_type' 형태의 배열로 가져오기
		$arr_post = $this->getColumnNameAndDataType("pages");
		/* [참고] 기존 방식
		$arr_post = array("pid", "type", "title", "description", "keywords", "external_style", "internal_style", "external_script", "internal_script", "body");
		*/
		
		// 짧은 변수로 바꾸기
		foreach ($arr_post as $column_name=>$column_data_type) {
			${$column_name} = trim($_POST[$column_name]);
		}
		
		// 입력 값 체크
		
		// INSERT SQL문 조립하고 실행
		$this->setupInsertSqlAndExecute($arr_post);
		
		// 알림 메세지 출력
		//$this->displayNotifyPage("정상적으로 입력되었습니다");
	}
	
	public function displayUpdatePageById($id, $title="") {
		if ($title != "") {
			$html_string = "<h1>" . $title . "</h1>\n";
		} else {
			$html_string = "";
		}
		
		$html_string .= $this->getUpdateForm("pages", "id", $id);
		$this->displayPageByPid("_default", "general", $title, $html_string);
	}
	
	public function displayUpdatePageByPid($pid, $title="") {
		if ($title != "") {
			$html_string = "<h1>" . $title . "</h1>\n";
		} else {
			$html_string = "";
		}
		
		$html_string .= $this->getUpdateForm("pages", "pid", $pid);
		$this->displayPageByPid("_default", "general", $title, $html_string);
	}
	
	public function updatePage($id) {
		// pages 태이블의 구조를 읽어서 'column_name=>column_data_type' 형태의 배열로 가져오기
		$arr_post = $this->getColumnNameAndDataType("pages");
		/* [참고] 기존 방식
		$arr_post = array("pid", "type", "title", "description", "keywords", "external_style", "internal_style", "external_script", "internal_script", "body");
		*/
		
		// 짧은 변수로 바꾸기
		foreach ($arr_post as $column_name=>$column_data_type) {
			${$column_name} = $_POST[$column_name];
		}
		
		// 입력 값 체크
		
		// UPDATE SQL문 조립하고 실행
		$this->setupUpdateSqlAndExecute($id, $arr_post);
		
		// 알림 메세지 출력
		//$this->displayNotifyPage("정상적으로 수정되었습니다");
	}
	
	public function deletePage($id) {
		// sql 조립
		$sql = "DELETE FROM pages WHERE id = " . $id;
		
		// sql 실행
		$this->execute($sql);		// 항상 true
		
		// 알림 메세지 출력
		//$this->displayNotifyPage("정상적으로 삭제되었습니다");
	}
	
	public function displayPage($title="", $html_string="") {
		if ($title != "")
			$this->title = $title;

		if ($html_string != "")
			$this->body .= $html_string;
	
		$this->displayDoctype();
		echo "<html>\n";
		$this->displayHead();
		$this->displayBody();
		echo "</html>\n";
	}
	
	private function displayDoctype($doctype="html5") {
		if ($doctype == "html5")	{
			echo "<!DOCTYPE html>\n";
		} elseif ($doctype == "strict") {
			echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
		} elseif ($doctype == "loose") {
			echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
		} else {
			echo "지원하지 않는 DOCTYPE입니다";
			exit;
		}
	}
	
	private function displayHead() {
		echo "<head>\n";
		
		if ($this->title != "") 
			echo "<title>" . $this->title . "</title>\n";
			
		$this->displayMeta();
		$this->displayExternalStyle();
		$this->displayExternalScript();
		$this->displayInternalStyle();
		$this->displayInternalScript();
		echo "</head>\n";
	}
	
	private function displayMeta() {
		if ($this->description != "")
			echo "<meta name=\"description\" content=\"" . $this->description . "\">\n";
		
		if ($this->keywords != "")
			echo "<meta name=\"keywords\" content=\"" . $this->keywords . "\">\n";
		
		echo "<meta name=\"author\" content=\"Juhyun Kim(freentour@gmail.com)\">\n";
		echo "<meta charset=\"UTF-8\">\n";
	}
	
	private function displayExternalStyle() {
		if ($this->external_style != "") {
			$arr_style = explode(",", $this->external_style);
		
			foreach ($arr_style as $style) {
				echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/" . $style . ".css\">\n";
			}
		}
	}
	
	private function displayExternalScript() {
		// jquery는 기본으로 추가
		// [주의] script 태그는 반드시 end tag 있어야 함. end tag가 없으면 크롬의 경우 화면에 아무것도 나타나지는 않는데 '소스코드 보기'하면 소스코드는 정상적으로 보이는 상황이 연출됨. 왜냐하면, end tag가 없으면 start tag 이후 부분을 모두 스크립트의 내용으로 인식하기 때문임. 
		echo "<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js\"></script>\n";
		
		if ($this->external_script != "") {
			$arr_script = explode(",", $this->external_script);
			
			foreach ($arr_script as $script) {
				echo "<script src=\"js/" . $script . ".js\"></script>\n";
			}
		}
	}
	
	private function displayInternalStyle() {
		if ($this->internal_style != "") {
			echo "<style type=\"text/css\">\n";
			echo $this->internal_style;
			echo "\n</style>\n";
		}
	}
	
	private function displayInternalScript() {
		if ($this->internal_script != "") {
			echo "<script>\n";
			echo $this->internal_script;
			echo "\n</script>\n";
		}
	}
	
	private function displayBody() {
		echo "<body>\n";
		echo $this->body;
		echo "\n</body>\n";
	}

	public function displayNotifyPage($message) {
		$html_string = "<ul class\"notify\">\n";
		$html_string .= "\t<li class=\"message\">" . $message . "</li>\n";
		$html_string .= "</ul>\n";
		
		$this->displayPageByPid("_default", "general", "", $html_string);
	}
	
	public function displayErrorPage($message) {
		$html_string = "<ul class\"error\">\n";
		$html_string .= "\t<li class=\"message\">" . $message . "</li>\n";
		$html_string .= "</ul>\n";
		
		$this->displayPageByPid("_default", "error", "", $html_string);
	}
	
}
?>