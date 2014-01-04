<?php
require_once("config.db.php");
require_once("object.class.php");

/* 
[참고] 
- php.ini 의 magic_quotes_gpc 값이 enable 되어있는 것을 기본으로 함. (대부분의 PHP 버젼에서 기본적으로 enable 되어 있음) 따라서, addslashes와 stripslashes 함수는 사용하지 않아도 됨. 
*/
class myDB extends object {

	private $server;
	private $id;
	private $passwd;
	private $dbname;
	private $db;		// db handler
	
	protected function connect() {
		// 데이터베이스 연결 정보 세팅
		/*
		$this->server = "localhost";
		$this->id = "root";
		$this->passwd = "apmsetup";
		$this->dbname = "m3";
		*/
		$this->server = _DB_HOST_;
		$this->id = _DB_USER_;
		$this->passwd = _DB_PASSWORD_;
		$this->dbname = _DB_DATABASE_;
		
		// 데이터베이스 연결
		@$this->db = new mysqli($this->server, $this->id, $this->passwd, $this->dbname);
		
		if (mysqli_connect_errno()) {
			$this->displayPageFront();
		?>
			<ul class="error">
				<li class="message">데이터베이스 연결 오류입니다. 관리자에게 문의해주세요</li>
				<li>
					<ul class="db_error">
						<li class="db_errno">DB Error Code : <?php echo mysqli_connect_errno(); ?></li>
						<li class="db_errmsg">DB Error Msg : <?php echo mysqli_connect_error(); ?></li>
					</ul>
				</li>
			</ul>		
		<?php
			$this->displayPageEnd();
			exit;
		}
		
		$this->db->set_charset("utf8");
	}
	
	protected function select($sql) {
		// SQL문 실행
		$result = $this->db->query($sql);
		
		if ($result == false) {
			// 데이터베이스 오류가 발생한 경우
			$this->displayQueryError($sql);
			exit;
		} else {
			if ($result->num_rows == 0) {
				// 데이터가 없는 경우
				$this->displayNoData();
				exit;
			} elseif ($result->num_rows >= 1) {
				return $result;
			}
		}
	}
	
	// insert, update, delete를 위한 메소드
	protected function execute($sql) {
		// SQL문 실행
		$result = $this->db->query($sql);
		
		if ($result == false) {
			// 데이터베이스 오류가 발생한 경우
			$this->displayQueryError($sql);
			exit;
		} else {
			return $result;		// return true;
		}
	}
	
	protected function getCreateForm($table) {
		// table 구조 가져오기
		$result_schema = $this->getTableStructure($table);
		
		$html_string = "<form action=\"index.php?mode=create\" method=\"post\">\n";
		$html_string .= "<ul>\n";
		
		while ($column_info = $result_schema->fetch_assoc()) {
			$html_string .= $this->getHtmlTagByColumnDataType("create", $column_info);
		}
		
		$html_string .= "<li><input type=\"submit\" value=\"Create\" /></li>\n";
		$html_string .= "</ul>\n";
		$html_string .= "</form>\n";
		
		return $html_string;
	}
	
	protected function getUpdateForm($table, $key, $key_value) {
		$sql = "SELECT * FROM " . $table . " WHERE " . $key . " = '" . $key_value . "'";
		$result = $this->select($sql);
		// PK 또는 Unique Key로 검색하므로 항상 리턴되는 결과는 1행. 따라서, while문 사용하지 않음.
		$row = $result->fetch_assoc();
		
		// table 구조 가져오기
		$result_schema = $this->getTableStructure($table);
		
		$html_string = "<form action=\"index.php?" . $key . "=" . $key_value . "&mode=update\" method=\"post\">\n";
		$html_string .= "<ul>\n";
		
		foreach ($row as $value) {
			// 미리 검색해둔 $result_schema로부터 현재 칼럼의 메타정보 가져오기
			$column_info = $result_schema->fetch_assoc();
			$html_string .= $this->getHtmlTagByColumnDataType("update", $column_info, htmlspecialchars($value));
		}
		
		$html_string .= "<li><input type=\"submit\" value=\"Update\" /></li>\n";
		$html_string .= "</ul>\n";
		$html_string .= "</form>\n";
		
		return $html_string;
	}

	protected function getColumnNameAndDataType($table) {
		// table 구조 가져오기
		$result_schema = $this->getTableStructure($table);
		
		// POST 입력 값을 위한 기본 정보를 table 구조로부터 가져와 $arr_post 배열 변수 완성하기 
		while ($column_info = $result_schema->fetch_assoc()) {
			// auto_increment 컬럼은 제외
			if ($column_info['EXTRA'] == "auto_increment") 
				continue;
				
			// '컬럼이름=>데이터타입' 형태로 $arr_post 배열 변수에 순서대로 입력(원래는 바로 밑에 주석 처리한 부분처럼 하던 것을 데이터타입 부분이 필요해서 table 구조로부터 가져오는 것으로 변경)
			$arr_post[$column_info['COLUMN_NAME']] = $column_info['DATA_TYPE'];
		}
		
		return $arr_post;
	}
	
	protected function setupInsertSqlAndExecute($arr_post) {
		// INSERT SQL문 조립
		$sql = "INSERT INTO pages (";
		
		$i = 0;
		foreach ($arr_post as $column_name=>$column_data_type) {
			if ($i == 0) {
				$sql .= $column_name;
			} else {
				$sql .= ", " . $column_name;
			}
			$i++;
		}

		$sql .= ") VALUES (";

		$i = 0;
		foreach ($arr_post as $column_name=>$column_data_type) {
			if ($i == 0) {
				if (in_array($column_data_type, array("int","tinyint","smallint","mediumint","bigint","decimal","float","double","real","bit","bool","serial"))) {
					$sql .= $_POST[$column_name];
				} else {
					$sql .= "'" . $_POST[$column_name] . "'";
				}
			} else {
				if (in_array($column_data_type, array("int","tinyint","smallint","mediumint","bigint","decimal","float","double","real","bit","bool","serial"))) {
					$sql .= ", " . $_POST[$column_name];
				} else {
					$sql .= ", '" . $_POST[$column_name] . "'";
				}
			}
			$i++;
		}

		$sql .= ") ";
		
		// sql 실행
		$this->execute($sql);		// 항상 true
	}
	
	protected function setupUpdateSqlAndExecute($id, $arr_post) {
		// UPDATE SQL문 조립
		$sql = "UPDATE pages SET ";
		
		$i = 0;
		foreach ($arr_post as $column_name=>$column_data_type) {
			if ($i == 0) {
				if (in_array($column_data_type, array("int","tinyint","smallint","mediumint","bigint","decimal","float","double","real","bit","bool","serial"))) {
					$sql .= $column_name . "=" . $_POST[$column_name];
				} else {
					$sql .= $column_name . "='" . $_POST[$column_name] . "'";
				}
			} else {
				if (in_array($column_data_type, array("int","tinyint","smallint","mediumint","bigint","decimal","float","double","real","bit","bool","serial"))) {
					$sql .= ", " . $column_name . "=" . $_POST[$column_name];
				} else {
					$sql .= ", " . $column_name . "='" . $_POST[$column_name] . "'";
				}
			}
			$i++;
		}

		$sql .= " WHERE id = " . $id;
		
		// sql 실행
		$this->execute($sql);		// 항상 true
	}
	
	protected function getHtmlTagByResultSet($result, $columns="all", $is_manage="") {
		// 'Create' 링크 출력
		$html_string .= "<div><a href=\"index.php?mode=createform\">Create</a></div>\n";
		
		$html_string .= "<table>\n";

		// 테이블 헤더 출력
		$html_string .= "<tr>\n";
		
		if ($columns == "all") {
			// 첫번째 행만 먼저 읽어오기
			$row = $result->fetch_assoc();
			foreach ($row as $key=>$value) {
				$html_string .= "<th>" . $key . "</th>\n";
			}
			// 전체 데이터 출력을 위해 $result의 첫번째 행으로 포인터를 다시 옮기기
			$result->data_seek(0);
		} elseif (is_array($columns)) {
			foreach ($columns as $value) {
				$html_string .= "<th>" . $value . "</th>\n";
			}
		}
		
		if ($is_manage == "manage") {
			$html_string .= "<th>&nbsp;</th>\n";
			$html_string .= "<th>&nbsp;</th>\n";
			$html_string .= "<th>&nbsp;</th>\n";
		}
		
		$html_string .= "</tr>\n";		
		
		// 테이블 바디 출력
		while ($row = $result->fetch_assoc()) {
			$html_string .= "<tr>\n";
			foreach ($row as $key=>$value) {
				if ($columns == "all") {
					// 모든 컬럼 출력
					$html_string .= "<td>" . htmlspecialchars($value) . "</td>";
				} elseif ($columns != "all" && is_array($columns)) {
					// 특정 컬럼만 출력
					if (in_array($key, $columns)) {
						$html_string .= "<td>" . htmlspecialchars($value) . "</td>";
					}
				} 
			}
			
			if ($is_manage == "manage") {
				$html_string .= "<td><a href=\"index.php?id=" . $row['id'] . "\">View</a></td>";
				$html_string .= "<td><a href=\"index.php?id=" . $row['id'] . "&mode=updateform\">Update</a></td>";
				$html_string .= "<td><a href=\"index.php?id=" . $row['id'] . "&mode=delete\">Delete</a></td>";
			}
			$html_string .= "</tr>\n";
		}
		
		$html_string .= "</table>\n";
		
		// 'Page Navigation' 영역 출력
		$html_string .= "<div id=\"page_navi\"></div>\n";

		return $html_string;
	}
	
	private function getTableStructure($table) {
		// table 구조를 가져오기 위한 SQL문
		$sql_schema = "SELECT * FROM information_schema.columns WHERE table_schema = '" . $this->dbname . "' AND table_name = '" . $table . "' ";
		$result_schema = $this->select($sql_schema);
		
		return $result_schema;
	}
	
	// $mode : 'create', 'update' 둘 중 하나.
	// $column_info : information_schema.columns 테이블에서 가져온 특정 컬럼의 메타정보(배열). 즉, 이 메소드는 특정 컬럼 하나에 대해 처리함. 
	private function getHtmlTagByColumnDataType($mode, $column_info, $value="") {
		// auto_increment 컬럼은 출력하지 않음
		if ($column_info['EXTRA'] == "auto_increment") {
			$html_string = "";
			return $html_string;
		}
		
		// create 모드일때는 해당 컬럼의 default 값 세팅
		if (($mode == "create") && ($column_info['COLUMN_DEFAULT'] != null)) 
			$value = $column_info['COLUMN_DEFAULT'];
		
		$html_string = "<li><span class=\"column\">";
		
		// column_comment가 있는 경우에는 column_name 대신에 comment를 사용
		if ($column_info['COLUMN_COMMENT'] != "") {
			$html_string .= $column_info['COLUMN_COMMENT'] . "</span> ";
		} else {
			$html_string .= $column_info['COLUMN_NAME'] . "</span> ";
		}
		
		// data_type에 따라 html 태그 조립
		switch ($column_info['DATA_TYPE']) {
			case "int" : 
				$html_string .= "<input type=\"number\" size=\"11\" maxlength=\"11\" name=\"" . $column_info['COLUMN_NAME'] . "\" value=\"" . $value . "\" /></li>\n";
				break;
			case "varchar" : 
				$html_string .= "<input type=\"text\" size=\"" . $column_info['CHARACTER_MAXIMUM_LENGTH'] . "\" maxlength=\"" . $column_info['CHARACTER_MAXIMUM_LENGTH'] . "\" name=\"" . $column_info['COLUMN_NAME'] . "\" value=\"" . $value . "\" /></li>\n";
				break;
			case "text" : 
				$html_string .= "<textarea name=\"" . $column_info['COLUMN_NAME'] . "\" rows=\"6\" cols=\"50\">" . $value . "</textarea></li>\n";
				break;
			case "enum" : 
				// enum 타입의 경우, column_type에 "enum('general','error','manage')"와 같은 방식으로 저장되어 있음. 따라서, substr 함수의 2번째와 3번째 파라미터로 5와 -1을 사용하여 필요한 문자열 부분만('general','error','manage' 부분만) 뽑아냄. 
				$arr_enum = explode(",", substr($column_info['COLUMN_TYPE'],5,-1));
				foreach ($arr_enum as $enum_value) {
					// 값 앞뒤에 있는 "'" 제거
					$enum_value = trim($enum_value, "'");
					
					$html_string .= "<input type=\"radio\" name=\"" . $column_info['COLUMN_NAME'] . "\" value=\"" . $enum_value . "\"";
					
					if ($value == $enum_value)
						$html_string .= " checked";
						
					$html_string .= ">" . $enum_value . " ";
				}
				$html_string .= "</li>\n";
				break;
			default : 
				$html_string .= "<input type=\"text\" name=\"" . $column_info['COLUMN_NAME'] . "\" value=\"" . $value . "\" /></li>\n";
		}
		
		return $html_string;
	}
	
	private function displayQueryError($sql="") {
		$this->displayPageFront();
	?>
		<ul class="error">
			<li class="message">쿼리 실행 오류입니다. 관리자에게 문의해주세요</li>
			<li>
				<ul class="db_error">
					<li class="db_errno">DB Error Code : <?php echo $this->db->errno; ?></li>
					<li class="db_errmsg">DB Error Msg : <?php echo $this->db->error; ?></li>
					<?php
					if ($sql != "") {
					?>
						<li class="db_errsql">DB Error SQL : <?php echo $sql; ?></li>
					<?php
					}
					?>
				</ul>
			</li>
		</ul>
	<?php
		$this->displayPageEnd();
	}
	
	private function displayNoData() {
		$this->displayPageFront();
	?>
		<ul class="error">
			<li class="message">There is No Data..</li>
		</ul>
	<?php
		$this->displayPageEnd();
	}
	
	private function displayPageFront() {
	?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta name="author" content="Juhyun Kim(freentour@gmail.com)">
				<meta charset="UTF-8">
			</head>
			<body>
	<?
	}
	
	private function displayPageEnd() {
	?>
			</body>
		</html>
	<?
	}

}