<?php
//↓↓↓関数群↓↓↓/////////////////////////////////////////////
function write_parent_child_node($parent, $child, &$xml_data) {

	global $encodedNewLine, $NL;	//エンコードされた改行を元にもどす

	$xml_data .= "<" . $parent . ">";

	if(is_array($child)) {
		foreach($child as $sub_parent => $sub_child) {
			$sub_parent = ereg_replace("_[0-9]{1,3}", "", $sub_parent);
			write_parent_child_node($sub_parent, $sub_child, $xml_data);
		} unset($sub_parent);	unset($sub_child);
	}
	else
		$xml_data .= str_replace($encodedNewLine, $NL, $child);

	$xml_data .= "</" . $parent . ">";
}

if( ! function_exists('array_combine')) {
	function array_combine($arr1,$arr2) {
		$out = array();
		foreach ($arr1 as $key1 => $value1)
			$out[$value1] = $arr2[$key1];
		return $out;
	}
}
//↑↑↑関数群↑↑↑/////////////////////////////////////////////

if( ! isset($_POST['data']) || empty($_POST['user']) || empty($_POST['passwd']))
	die("{\"result\": \"ng\"}");

////config///////////////////////////////////////////////////////
//デリミタ(タブ)
$delimiter = chr(9);

//改行
$newline = array(chr(10), chr(13));
$NL = chr(10);

//Ajax送信時の改行(改行があると送信できないため。)
//honjp_terminal_api_support.phpにも同じ変数があるので、変更する場合はそちらも同時に変更すること
$encodedNewLine = "_-_-";

//APIアクセスサーバ
$access_server = "https://hon.jp/rest/terminal/1.0/";
////config///////////////////////////////////////////////////////

//アカウントとパスワード
$account = $_POST['user'];
$pw = $_POST['passwd'];

//デバッグモードor本試験モードの選択
//true : デバッグ．実際のデータは送信されず，画面に送られるべきxmlを出力
//false : 実際に生成されたデータを送信する. 
$is_debug = true;

//xmlに記述する際に変換しなければいけない文字列
$raw_char = array('&', '<', '>', '\'', '"');
$xml_char = array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;');

//親-子の関係にあるタグ
$parent_child = array ( 'label_array' => array('name_label', 'kana_label'), 
									'person_array' => array('name_role', 'name_person', 'kana_person', 'person_pbl_cd', 'person_pbl_sex', 'person_pbl_siteview', 'person_pbl_prf'),
									'printing_array' => array('name_printing', 'kana_printing'));

//シリーズはユニークなタグだが、構造は入れ子．
$series_array = array('name_series', 'kana_series', 'srs_pbl_catch', 'srs_pbl_cmnt_st', 'srs_pbl_cmnt_lg', 'srs_pbl_admn');

//↓↓↓事前準備開始↓↓↓////////////////////////////////////////////////////////////////
$fp = @fopen('../lib/honjpAPITags', 'r');
if ( ! $fp)	die("{\"result\": \"failed to get Column Headers.\"}");

//ヘッダの取得
$header = fgetcsv($fp, 256, $delimiter);

while(($temp = fgetcsv($fp, 256, $delimiter)) !== false) {
	$temp = array_combine($header, $temp);
	$key = $temp['display_name'];
	$val = $temp['xml_tag'];
	$allAPITags[$key] = $val;
}
fclose($fp);

$posted_header = $_POST['header'];
$header = array();
foreach($posted_header as $val) {
	$val = explode("_", $val);	//複数項目存在するもの(honjpAPITagsのclassificationが著者、レーベル、掲載誌の項目群)
	if(count($val) == 1) {
		$in_array = false;
		foreach($parent_child as $array) {
			if(in_array($allAPITags[$val[0]], $array)) {
				$in_array = true;
				break;
			}
		} unset($array);

		if($in_array)
			$insert = $allAPITags[$val[0]] . "__1";
		else
			$insert = $allAPITags[$val[0]];
	}
	else
		$insert = $allAPITags[$val[0]] . "__" . $val[1];
	$header[] = $insert;
} unset($val); unset($posted_header);

$xml_insert = array();
foreach($_POST['data'] as $record) {
	$record = array_combine($header, $record);

	if(empty($record) || ( ! isset($record['title'])))	continue;	//項目がひとつもないもしくはタイトルがないものは登録できないのでパス

	foreach($record as $key => $val) {

		//項目になにも登録されていなければ、その項目はinsertもupdateもしない
		if(empty($val)) {
			unset($record[$key]);
			continue;
		}

		$val = str_replace($raw_char, $xml_char, $val);	//不要物の排除

		//最後が『_[数字]』で終わっているものは複数項目
		if(preg_match("/__[0-9]{1,3}/", $key, $count)) {
			$count = str_replace("__", "", $count[0]);
			$temp = ereg_replace('__[0-9]{1,3}', '', $key);

			foreach($parent_child as $parent => $child) {
				if(in_array($temp, $child)) {
					$record[$parent][str_replace('_array', '', $parent) . '_each_' . $count][$temp] = $val;
					unset($record[$key]);
					break;
				}
			} unset($child);	unset($parent);
		}
		else if(in_array($temp, $series_array)) {
			$record['series_array']['series_each'][$temp] = $val;
			unset($record[$key]);
		}
		else if($key == 'isbn') {
			$val = explode("/", $val);
			$i=1;
			foreach($val as $isbn) {
				$record['isbn_array']['isbn_each_' . $i] = $isbn;
				$i++;
			} unset($isbn);
			unset($record[$key]);
		}
		else if($key == 'isbn' || $key == 'sale_hardware') {
			$val = explode("/", $val);
			$i=1;
			foreach($val as $value) {
				$record[$key . '_array'][$key . '_each_' . $i] = $value;
				$i++;
			} unset($value);
			unset($record[$key]);
		}
	} unset($key); unset($val);

	//xmlのデータを作成
	$xml_temp = "<superupdate key_namespace=\"" . $record['namespace'] . "\">";
	unset($record['namespace']);

	foreach($record as $key => $val) {
		if( ! is_array($val)) {
			$val = str_replace($encodedNewLine, $NL, $val);

			//imgのタグだけは属性(登録するファイル名)があるのでここで処理する
			if($key == 'img_m' || $key == 'img_b' || $key == 'img_s') {
				$temp = explode("::", $val);
				if(count($temp) == 2) {
					$xml_temp .= "<" . $key . " org_name = \"" . $temp[1] . "\"";
					$xml_temp .= ">" . $temp[0] . "</" . $key . ">";
				}
				else if(count($temp) == 1)
					$xml_temp .= "<" . $key . ">" . $val . "</" . $key . ">";
			}
			else
				$xml_temp .= "<" . $key . ">" . $val . "</" . $key . ">";
		}
		else
			write_parent_child_node($key, $val, &$xml_temp);
	} unset($key);	unset($val);

	$xml_temp .= "</superupdate>";

	$xml_insert[] = $xml_temp;	//一件ずつしか登録できないので、とりあえずxmlの文字列を保持
} unset($record);
//↑↑↑事前準備ここまで↑↑↑////////////////////////////////////////////////////////////////

//デバッグの場合はここで先頭行のxmlを出力して終了
if($is_debug) {
	echo "{\"result\": \"" . mb_convert_encoding(str_replace(array(chr(9), chr(10), chr(13), "\""), array("", "", "", "\\\""), $xml_insert[0]), "UTF-8", "EUC-JP") . "\"}";
	exit(1);
}

//↓↓↓送信開始↓↓↓///////////////////////////////////////////////////////////////////////
require_once "HTTP/Request.php";
require_once "./xml.php";

//sign-in(session idを取得)
$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
$data .= "<honjp_xml_http_api>";
$data .= "<signin id=\"" . $account . "\" pw=\"" . $pw . "\" />";
$data .= "</honjp_xml_http_api>";

$req = new HTTP_Request($access_server);
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->addPostData("xml", $data);

$terminal_session_id = "";
if ( ! PEAR::isError($req->sendRequest())) {
	$resp = $req->getResponseBody();
	$resp = str_replace($newline, "", $resp);
	$responseXML = XML_unserialize($resp);
	$terminal_session_id = $responseXML['honjp_xml_http_api']['signin']['ok']['session_id'];
}
else die("{\"result\": \"Failed to get ResponseXML when sign in.\"}");

//データの送信
$counter = 1;
$failedRecord = array();
$successRecord = array();
$xml_data = "";
foreach($xml_insert as $val) {
	$xml_data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	$xml_data .= "<honjp_xml_http_api>";
	$xml_data .= "<edit session_id=\"" . $terminal_session_id . "\">";
	$xml_data .= mb_convert_encoding($val, "UTF-8", "EUC-JP");
	$xml_data .= "</edit>";
	$xml_data .= "</honjp_xml_http_api>";

	$req = new HTTP_Request($access_server);
	$req->setMethod(HTTP_REQUEST_METHOD_POST);
	$req->addPostData("xml", $xml_data);

	if ( ! PEAR::isError($req->sendRequest())) {
		$resp = $req->getResponseBody();
		$resp = str_replace($newline, "", $resp);
		if(strpos($resp, "<ok>") === false)
			$failedRecord[] = $counter;
		else
			$successRecord[] = $counter;
	}
	else die("{\"result\": \"Failed to get ResponseXML when insert.\"}");

	$counter++;
} unset($val);

//sign-out
$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
$data .= "<honjp_xml_http_api>";
$data .= "<signout session_id=\"" . $terminal_session_id . "\" />";
$data .= "</honjp_xml_http_api>";

$req = new HTTP_Request($access_server);
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->addPostData("xml", $data);

if ( ! PEAR::isError($req->sendRequest())) {
	$resp = $req->getResponseBody();
	$resp = str_replace($newline, "", $resp);
	$responseXML = XML_unserialize($resp);
	$is_finish = $responseXML['honjp_xml_http_api']['signout'];
	if(array_key_exists("ok", $is_finish)) {
		$responseText = "";
		if(empty($failedRecord))	$responseText = "Registered all records.";
		else if(empty($successRecord))	$responseText = "Failed all records.";
		else {
			$responseText .= "Registered at ";
			foreach($successRecord as $val) {
				$responseText .= $val . ", ";
			} unset($val);
			$responseText = substr($responseText, 0, strlen($responseText)-2);
			$responseText .= " column, ";

			$responseText .= "Failed at ";
			foreach($failedRecord as $val) {
				$responseText .= $val . ", ";
			} unset($val);
			$responseText = substr($responseText, 0, strlen($responseText)-2);
			$responseText .= " column.";
		}

		echo "{\"result\": \"" . $responseText . "\"}";	//実行結果を出力して終了
		exit(1);
	}
}
else die("{\"result\": \"Failed to get ResponseXML when sign out.\"}");
//↑↑↑送信終了↑↑↑/////////////////////////////////
?>