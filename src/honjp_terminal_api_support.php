<?php
//****************************************************
//	honjp_terminal_api_support.php
//	ターミナルAPIヘデータを流し込む為のウェブブラウザエクセルアプリ
//****************************************************

//array_combineをPHP4に実装
if( ! function_exists('array_combine')) {
	function array_combine($arr1,$arr2) {
		$out = array();
		foreach ($arr1 as $key1 => $value1) {
			$out[$value1] = $arr2[$key1];
		} unset($key1); unset($value1);
		return $out;
	}
}

////config///////////////////////////////////
//デリミタ(タブ)
$delimiter = chr(9);

//改行
$newline = array(chr(10), chr(13));

//Ajax送信時の改行
$encodedNewLine = "_-_-";

//列の上限
$limit = 50;
////config///////////////////////////////////

//honjpAPITagsはtsv, ヘッダはclassification, display_name, xml_tag, sample, display
$fp = @fopen('../lib/honjpAPITags', 'r');
if ( ! $fp)	die('failed to get Colum Headers.');
$header = fgetcsv($fp, 256, $delimiter);

$multiple_item = array('author_array' => array(), 'label_array' => array(), 'printing_array' => array());
while(($temp = fgetcsv($fp, 256, $delimiter)) !== false) {
	$temp = array_combine($header, $temp);

	//displayが0で、必須項目でないなら
	if($temp['display'] == 0 && ($temp['xml_tag'] != 'namespace' || $temp['xml_tag'] != 'title' || $temp['xml_tag'] != 'name_person' || $temp['xml_tag'] != 'name_role'))
		continue;

	$column[] = $temp['display_name'];
	$example[] = $temp['sample'];

	switch($temp['classification']) {
		case '著者':
			$multiple_item['author_array']['display_name'][] = $temp['display_name'];
			$multiple_item['author_array']['sample'][] = $temp['sample'];
			break;
		case 'レーベル・ブランド等':
			$multiple_item['label_array']['display_name'][] = $temp['display_name'];
			$multiple_item['label_array']['sample'][] = $temp['sample'];
			break;
		case '掲載誌':
			$multiple_item['printing_array']['display_name'][] = $temp['display_name'];
			$multiple_item['printing_array']['sample'][] = $temp['sample'];
			break;
		default:
			break;
	}
}
fclose($fp);

$ability_of_author = empty($multiple_item['author_array']) ? "disabled" : "";
$ability_of_label = empty($multiple_item['label_array']) ? "disabled" : "";
$ability_of_printing = empty($multiple_item['printing_array']) ? "disabled" : "";

$count_author_array = count($multiple_item['author_array']['display_name']);
$count_label_array = count($multiple_item['label_array']['display_name']);
$count_printing_array = count($multiple_item['printing_array']['display_name']);

//列の番号を保持する
$num_of_namespace = -1;
$num_of_title = -1;
$num_of_author = -1;
$num_of_role = -1;
$num_of_label = -1;
$num_of_printing = -1;

$count = 0;
foreach($column as $val) {
	switch($val) {
		case 'namespace':
			$num_of_namespace = $count;
			break;
		case '作品名':
			$num_of_title = $count;
			break;
		case '著作者名':
			$num_of_author = $count;
			break;
		case '著作者役割':
			$num_of_role = $count;
			break;
		case 'ブランド':
			$num_of_label = $count;
			break;
		case '掲載物':
			$num_of_printing = $count;
			break;
	}
	$count++;
} unset($val);

//カラムの数
$count = count($column);

//javascriptのArrayに挿入するための文字列作成////////////////////////
foreach($column as $val) {
	$column_header .= "\"" . $val . "\", ";
} unset($val);
$column_header = substr($column_header, 0, -2);	//最後の「カンマ＋スペース」を除去

foreach($example as $val) {
	$example_header .= "\"" . $val . "\", ";
} unset($val);
$example_header = substr($example_header, 0, -2);	//最後の「カンマ＋スペース」を除去

foreach($multiple_item as $key => $section) {
	$display_temp = "";
	$sample_temp = "";
	foreach($section as $section_name => $item) {
		foreach($item as $val) {
			($section_name === "display_name") ? $display_temp .= "\"" . $val . "\", " : $sample_temp .= "\"" . $val . "\", ";
		} unset($val);

		$display_temp = substr($display_temp, 0, -2);	//最後の「カンマ＋スペース」を除去
		$sample_temp = substr($sample_temp, 0, -2);	//最後の「カンマ＋スペース」を除去

		($section_name === "display_name") ? $multiple_item[$key]['display_name'] = $display_temp : $multiple_item[$key]['sample'] = $sample_temp;
	} unset($section_name);	unset($item);
} unset($key);	unset($section);
//javascriptのArrayに挿入するための文字列作成////////////////////////
?>

<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset='euc-jp' />
		<title>hon.jp terminal API support</title>

		<script src="./js/jquery.min.js"></script>
		<script src="./js/jquery.handsontable.js"></script>
		<script src="./js/honjp_terminal_api_support.js"></script>

		<link rel="stylesheet" media="screen" href="./css/jquery.handsontable.css" />
		<script>
			var column_length = <?=$count?>;

			var num_ColOfAuthor = <?=$count_author_array?>;
			var array_ColOfAuthor = new Array(<?=$multiple_item['author_array']['display_name']?>);
			var array_ColOfAuthorExample = new Array(<?=$multiple_item['author_array']['sample']?>);
			var count_Author = 2;
			var author = "著作者名";
			var role = "著作者役割";

			var num_ColOfLabel = <?=$count_label_array?>;
			var array_ColOfLabel = new Array(<?=$multiple_item['label_array']['display_name']?>);
			var array_ColOfLabelExample = new Array(<?=$multiple_item['label_array']['sample']?>);
			var count_Label = 2;

			var num_ColOfPrinting = <?=$count_printing_array?>;
			var array_ColOfPrinting = new Array(<?=$multiple_item['printing_array']['display_name']?>);
			var array_ColOfPrintingExample = new Array(<?=$multiple_item['printing_array']['sample']?>);
			var count_Printing = 2;

			var author_role_pair = new Array([<?=$num_of_author?>, <?=$num_of_role?>]);
			var limit = <?php echo $limit+1; ?>;
		</script>
	</head>

	<body>
		<div id="wrapper">
			<div id="options">
				<div id="user">
					<p>STEP1●「hon.jpターミナル」アカウントでログインしてください。</p>
					<span>ターミナルUSER ID </span><input type="text" id="account" />
					<span>パスワード </span><input type="password" id="passwd" />
				</div>
				<div id="description">
					<p>STEP2●Excelからデータを直接コピー＆ペーストしてください（最大<?=$limit?>件）。</p>
				</div>
			</div>

			<div id="editer">
				<button id="save" name="save">データ登録</button>
				<button <?=$ability_of_author?> class="addition" name="author">著者を追加する</button>
				<button <?=$ability_of_label?> class="addition" name="label">ブランドを追加する</button>
				<button <?=$ability_of_printing?> class="addition" name="printing">掲載物を追加する</button>
			</div>

			<div id="waiting">waiting...</div>
			<div id="exampleGrid" class="dataTable"></div>
		</div>

		<script>

			function countEmpty(arr) {
				var empty = 0;
				for (var i = 0, ilen = arr.length; i < ilen; i++) {
					if (arr[i] === '')	empty++;
				}
				return empty;
			}

			var container = $("#exampleGrid");

			container.handsontable({
				rows: <?=$limit?>,
				cols: <?=$count?>,
				contextMenu: false,
				colHeaders:[<?=$column_header?>],
				rowHeaders: true,
				minSpareCols: 0,
				minSpareRows: 1,
				autoWrapRow: true,

				legend: [
					{
						match:
							function (row, col, data) {
								return (row === 0);
							},
						style: {
							color: "#777",
							fontStyle: "italic"
						},
						readOnly: true
					},
					{
						match:
							function (row, col, data) {
								return (col === <?=$num_of_title?> || col === <?=$num_of_namespace?>);
							},
						style: {
							background: "#EFC1C4"
						}
					},
					{
						match:
							function (row, col, data) {
								for(var i=0; i < author_role_pair.length; i++)
									if(author_role_pair[i][0] == col || author_role_pair[i][1] == col)	return true;
							},
						style: {
							background: "#98fb98"
						}
					},
					{
						match:
							function (row, col, data) {
								return (row === limit);
							},
						style: {
							background: "#777"
						},
						readOnly: true
					}
				],

				onChange: function (changes) {
					setTimeout(function(){ 	var rowHeaders = $("#exampleGrid").handsontable('getRowHeader');
														var newRowHeader = new Array("記入例");

														for(var i=1; i < rowHeaders.length; i++)
															newRowHeader.push(i);

														$("#exampleGrid").handsontable('updateSettings', {rowHeaders: newRowHeader});

														//バグ修正
														for(var i=0; i < newRowHeader.length; i++)
															$("table:eq(1) > tbody > tr:eq(" + i + ")").css("height", $("table:eq(0) > tbody > tr:eq(" + i + ")").height() + "px");
														}, 20);
				}
			});

			//初期値の設定(入力例を出力)
			var example = [[<?=$example_header?>]];
			container.handsontable("loadData", example);

			var handsontable = container.data('handsontable');

			var parent = container.parent();
			parent.find('button[name=save]').click(
				function () {

					var data = handsontable.getData();

					data.splice(0, 1);	//記入例の配列を削除

					var incomplete_col = new Array();
					var count_incomplete_col = 1;
					for(var i = data.length-1; i >= 0; i--) {

						if(data[i][<?=$num_of_title?>] === "" || data[i][<?=$num_of_namespace?>] === "") {
							if(countEmpty(data[i]) == column_length) {
								data.splice(i, 1);

								if(data.length == 0) {
									window.alert("insert data. ");
									return;
								}

								continue;
							}
							else {
								window.alert("not registered due to lack of data \"namespace\" or \"作品名\" .  at " + (i+1) + " column.");
								return;
							}
						}

						//改行をajaxで送信できるようエンコード
						for(var j = data[i].length-1; j >= 0; j--) {
							data[i][j] = data[i][j].replace(/[\n\r]/g, "<?=$encodedNewLine?>");
						}

						//セットで入力されていなければならない場所のチェック
						for(var j=0; j < author_role_pair.length; j++)
							if((data[i][author_role_pair[j][0]] !== "" && data[i][author_role_pair[j][1]] === "") || (data[i][author_role_pair[j][0]] === "" && data[i][author_role_pair[j][1]] !== ""))
								incomplete_col.push(count_incomplete_col);

						count_incomplete_col++;
					}

					if(incomplete_col.length != 0) {
						window.alert("Incomplete at " + incomplete_col + " cols.");
						return;
					}

					if($("#account").val().length != 0 && $("#passwd").val().length != 0) {

						$("#waiting").css("visibility", "visible");

						$.ajax({
							url: "./getDataFromHandsontable.php",
							data: { "header": handsontable.getColHeader(), "data": data, "user": $("#account").val(), "passwd": $("#passwd").val() }, //returns all cells' data
							dataType: 'json',
							type: 'POST',
							success: function (res) {
								$("#waiting").css("visibility", "hidden");
								window.alert(res.result);
							},
							error: function () {
								window.alert("error");
								$("#waiting").css("visibility", "hidden");
							}
						});
					}
					else 
						window.alert("enter all of \"account\", password.");
				}
			);

			parent.find('button[name=author]').click(
				function () {
					var headers = $("#exampleGrid").handsontable('getColHeader');

					var totalCol = headers.length;
					var tempAuthorNum;
					var tempRoleNum;
					for(temp in array_ColOfAuthor) {
						headers.push(array_ColOfAuthor[temp] + "_"  + count_Author);
						if(array_ColOfAuthor[temp] == author)
							tempAuthorNum = (totalCol + parseInt(temp));
						else if(array_ColOfAuthor[temp] == role)
							tempRoleNum = (totalCol + parseInt(temp));
					}
					author_role_pair.push([tempAuthorNum, tempRoleNum]);
					count_Author++;
					$("#exampleGrid").handsontable('updateSettings', { cols: (headers.length), colHeaders: headers});

					column_length = headers.length;
					for(var i = 0; i < array_ColOfAuthorExample.length; i++)
						$("#exampleGrid").handsontable('setDataAtCell', 0, column_length-array_ColOfAuthorExample.length+i, array_ColOfAuthorExample[i]);
				}
			);

			parent.find('button[name=label]').click(
				function () {
					var headers = $("#exampleGrid").handsontable('getColHeader');

					for(temp in array_ColOfLabel)
						headers.push(array_ColOfLabel[temp] + "_"  + count_Label);
					count_Label++;
					$("#exampleGrid").handsontable('updateSettings', { cols: (headers.length), colHeaders: headers});

					column_length = headers.length;
					for(var i = 0; i < array_ColOfLabelExample.length; i++)
						$("#exampleGrid").handsontable('setDataAtCell', 0, column_length-array_ColOfLabelExample.length+i, array_ColOfLabelExample[i]);
				}
			);

			parent.find('button[name=printing]').click(
				function () {
					var headers = $("#exampleGrid").handsontable('getColHeader');

					for(temp in array_ColOfPrinting)
						headers.push(array_ColOfPrinting[temp] + "_"  + count_Printing);
					count_Printing++;
					$("#exampleGrid").handsontable('updateSettings', { cols: (headers.length), colHeaders: headers});

					column_length = headers.length;
					for(var i = 0; i < array_ColOfPrintingExample.length; i++)
						$("#exampleGrid").handsontable('setDataAtCell', 0, column_length-array_ColOfPrintingExample.length+i, array_ColOfPrintingExample[i]);
				}
			);
		</script>
	</body>
</html>