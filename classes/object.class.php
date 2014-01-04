<?php
/**
 * @class object
 * @author 김주현
 * @brief 모든 클래스의 조상이 되는 최상위 클래스
 * @version 0.1
 * 
 * 1. 특징
 * - 기본적인 접근 함수(__get, __set)를 가지고 있어서 하위 클래스들에게 상속됨. 
 * - 클래스의 프로퍼티들을 XML Fragment 형태와 XML Document 형태로 serialize 시켜주는 메소드를 기본적으로 내장하고 있어서 하위 클래스들에게 상속됨. 
 * - 객체 생성없이 사용할 수 있는 2개의 static 메소드를 포함하고 있어서 다른 클래스들에서도 자유롭게 사용할 수 있음. 
 *   (createXMLDecl, xmlSerializeByType)
 *
 * 2. 수정사항
 * 
 * 3. 주의사항 
 *
 * [코딩시 지켜야할 Naming Rule]
 * - 변수 이름은 소문자로만. 단어간 연결은 '_'로.
 * - 상수 이름은 모두 대문자로만. '_ENC_'(접두어)로 시작하고, '_'(접미어)로 종료.
 * - 함수 이름역시 변수와 동일하게 소문자로만. 단어간 연결은 '_'로. 
 * - 클래스 이름은 소문자로 시작. 단어간 연결은 camel 방식으로. 
 * - 클래스 내의 프로퍼티 이름은 소문자로 시작. 단어간 연결은 camel 방식으로. 
 * - 클래스 내의 메소드 이름 역시 프로퍼티와 동일하게 소문자로 시작. 단어간 연결은 camel 방식으로. 
 * - Iterator와 Exception은 모두 대문자로 시작. 단어간 연결은 camel 방식으로. 단, 접미어로 'Iterator' 또는 'Exception'을 반드시 붙이도록 함. 
 *  
 * - 변수 이름은 일부 데이터 타입에 한해 접두어로 타입을 표시하도록 한다. 
 * - 배열의 경우, 접두어로 'arr_'(변수)를 붙여준다. 프로퍼티의 경우에는 'List'(접미어)를 붙여준다. 
 * - 객체의 경우, 접두어로 'o_'(변수)를 붙여준다. 
 * - 자원의 경우, 접두어로 'r_'(변수)를 붙여준다. 
 * - 혼합형인 경우, 접두어로 'm_'(변수)를 붙여준다. 
 */
class object {
	
	/**
	 * TODO : 
	 * - serialize되어진 XML Document를 파일로 Save하는 메소드도 추가. 
	 * - 파일 상태로 존재하는 XML Document를 메모리로 로드하는 메소드도 추가. 
	 * - 파일 상태로 존재하는 XML Document를 메모리로 로드한 후 루트 엘리먼트 부터 모든 내용을 그대로 '문자열'(!) 형태로 serialize 시키는 메소드도 추가. 
	 * - 파일 상태로 존재하는 XML Document를 메모리로 로드한 후 각각의 엘리먼트들을 동일한 이름의 객체들로 생성하고 관계를 형성해주는 메소드도 추가. 
	 * - 파일 상태로 존재하는 XML Document를 메모리로 로드한 후 특정 엘리먼트를 추가하거나 삭제한 후 다시 파일로 Save하는 메소드도 추가. 
	 * 
	 * - 객체 단위에서 입력으로 들어온 $format 파라미터에 따라 순수 xml, 다이렉트 html, xslt를 포함한 xml, 서버에서 변환된 후 내려오는 html 등으로 브라우저로 전송하는 공통 메소드를 추가해야 함. 메소드 이름은 그냥 display($format) 또는 displayToBrowser($format)
	 * - 이 메소드의 목적은 그야말로 객체 단위 자체만으로도 다양한 포맷으로 전송할 수 있는 능력을 가지도록 하는 것에 있음. 
	 * - 특히, 향후 전개될 다양한 환경의 모바일 디바이스를 고려할때 더욱 필요한 메소드임. 
	 */
	
	// 속성들은 직접 접근할 수는 없지만, 상속은 가능하도록 protected로 선언
	// 하지만, __set 접근 함수를 아래처럼 사용하면 실제적으로는 모든 프로퍼티에 대해 직접 접근할 수 있는 것과 마찬가지임. 
	// 값이 잘못 변경되는 것을 막아야하는 프로퍼티들의 경우에는 __set 접근 함수에서 제외시키고, 해당 기능을 수행하는 메소드를 통해서만 안정적으로 값이 수정될 수 있도록 하는 것이 좋음. 
	// 그런데, 그렇게 보호해야할 프로퍼티들이 실제적으로는 어떤 것들이 있을까... 해서 개발과정에서는 일단 모두 열어두는 것으로 함. 

	
	// 생성자
	public function __construct() {

	}
	
	/*
	// 접근 함수
	public function __get($name) {
		return $this->$name;
	}
	
	// 접근 함수
	public function __set($name, $value) {
		$this->$name = $value;
	}
	*/
	
	// 소멸자
	public function __destruct() {
		
	}
	
	// XML Fragment 형태의 문자열로 반환하는 메소드
	public function xmlSerialize() {
		$str_xml = "";
		
		// id 프로퍼티의 경우에만 속성으로 출력. 나머지는 모두 하위 엘리먼트로 출력
		if ($this->id != "") {
			$str_xml .= "<" . get_class($this) . " id=\"" . $this->id . "\">\n";		// 클래스 이름의 시작 태그
		} else {
			$str_xml .= "<" . get_class($this) . ">\n";		// 클래스 이름의 시작 태그
		}
		
		// 현재 객체가 포함하고있는 모든 프라퍼티들을 연관배열 형태로 저장
		$arr_object_properties = get_object_vars($this);
		
		foreach ($arr_object_properties as $key => $value) {
			// id 프로퍼티는 이미 속성으로 출력했으므로 다시 출력하지 않기 위해 제외함
			// 프로퍼티 가운데 mysqli 클래스의 인스턴스인 것 역시 제외함
			if ($key != "id" && !($value instanceof mysqli)) {		
				$str_xml .= object::xmlSerializeByType($key, $value);
			}
		}
		
		$str_xml .= "</" . get_class($this) . ">\n";		// 클래스 이름의 종료 태그
		
		return $str_xml;
	}
	
	// Well-Formed XML Document 형태의 문자열로 반환하는 메소드
	public function xmlSerializeAsDocument($version="1.0", $encoding="utf-8") {
		$str_xml = "";
		
		$str_xml .= object::createXMLDecl($version, $encoding);
		$str_xml .= $this->xmlSerialize();
		
		return $str_xml;
	}
	
	// XSLT 스타일쉬트가 PI 형태로 추가된 Well-Formed XML Document 형태의 문자열로 반환하는 메소드
	public function xmlSerializeAsDocumentWithXSLT($version="1.0", $encoding="utf-8", $xslt_href="") {
		$str_xml = "";
		
		$str_xml .= object::createXMLDecl($version, $encoding);
		
		if ($xslt_href != "") {
			$str_xml .= object::attachXSLT($xslt_href);
		}
		
		$str_xml .= $this->xmlSerialize();
		
		return $str_xml;
	}
	
	// (주의) static 메소드임. 타입에 따라 그에 적절한 xml serialize 방법으로 출력 (recursive call 가능)
	public static function xmlSerializeByType($key, $value) {
		$str_xml = "";
		
		if (is_object($value)) {	// 객체인 경우
			$str_xml .= $value->xmlSerialize();
		} elseif (is_array($value)) {	// 배열인 경우, 2차원 이상의 배열일 수 있으므로 recursive call
			// 배열이 비어있지 않은 경우만 출력
			if (!empty($value)) {
				$str_xml .= "<" . trim($key) . ">\n";		// 시작 태그
				
				$str_xml .= "<count>" . count($value) . "</count>\n";		// 배열의 갯수

				foreach ($value as $sub_key => $sub_value) {
					$str_xml .= object::xmlSerializeByType($sub_key, $sub_value);
				}

				$str_xml .= "</" . trim($key) . ">\n";	// 종료 태그
			}
		} else { 						// 그 외의 경우
			// 프로퍼티에 값이 세팅된 경우만 출력. 즉, 프로퍼티가 선언은 되었지만 값이 지정되어있지 않은 경우에는 출력하지 않음. 
			if (isset($value)) {
				if (is_string($value) && $value == "") {
					// 문자열이면서 비어있는 경우에는 아무것도 출력하지 않음.
				} else {
					$str_xml .= "<" . trim($key) . ">";		// 시작 태그
					
					if (is_string($value)) {
						$str_xml .= "<![CDATA[" . $value . "]]>";
					} elseif (is_bool($value)) {
						if ($value === true) {
							$str_xml .= "true";
						} elseif ($value === false) {
							$str_xml .= "false";
						}
					} else {
						$str_xml .= $value;
					}
					
					$str_xml .= "</" . trim($key) . ">\n";	// 종료 태그
				}
			}
		}

		return $str_xml;
	}
	
	// (주의) static 메소드임. 
	public static function createXMLDecl($version, $encoding) {
		$str_xml = "";		// 초기화
		
		$str_xml .= "<?xml version=\"" . $version . "\" encoding=\"" . $encoding . "\"?>\n";

		return $str_xml;
	}
	
	// (주의) static 메소드임. 
	public static function attachXSLT($xslt_href) {
		$strXml = "";		// 초기화
			
		$strXml .= "<?xml-stylesheet type=\"text/xsl\" href=\"" . $xslt_href . "\"?>\n";
			
		return $strXml;
	}

}	// END : object class
?>