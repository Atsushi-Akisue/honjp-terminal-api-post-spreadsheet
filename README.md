#hon.jp ターミナルAPI サンプルアプリ

##概要
jQuery&HTMLで動作する<a href="https://github.com/Atsushi-Akisue/jquery-handsontable" target="_blank">handsontable</a>のアプリを用いて書誌情報をエクセル風に入力させ、そのデータをAjaxでPOSTし、POSTした先のスクリプトでXMLフォーマットにデータを整形して、hon.jp ターミナルAPIへPOSTする <br>

(※「hon.jpターミナル」アカウントが必要です。 申請は<a href="http://hon.jp/doc/honjpterminal.html" target="_blank">こちら</a>をお読みになった上、<a href="mailto:info@hon.jp">info@hon.jp</a>までメールしてください)

##利用ソース
PHP 4.4.9<br>
PEAR - HTTP/Request.php<br>
jQuery<br>
handsontable (jQueryベース)<br>

##使い方
(./src/honjp_terminal_api_support.php)をブラウザから読み込むと、実際にデータを入力するためのhandsontable(エクセル風シート)が表示されます。<br>
hon.jpのアカウントとパスワードを入力し、handsontableの行ごとに各書誌のデータを入力します。コピペが可能なので、Excel上のデータをまるまる移すことも可能です。
「[著者／ブランド／掲載物]を追加する」ボタンを押すと、著者の項目、ブランドの項目、掲載物の項目を入力する列が増え、複数入力することができます。
データの入力が終わったら、「データ登録」ボタンを押して送信。データのXML整形＆hon.jpターミナルAPIへの送信を行うスクリプト(./src/getDataFromHandsontable.php)の処理を待てば登録完了。

##handsontableの各列項目の設定
handsontableに出力する列の各項目は、./lib/honjpAPITagsというtsvファイルを読み込んで出力しています。
このファイルは、ターミナルAPIで、書誌情報を更新するときに利用できるxmlタグのインデックスとなっています。
このファイルの各行の最後の項目が「'display'」という項になっていて、「0か1」を入力します。
1が入力されている項目のみがhandsontableに出力されますが、必須項目(namespace, title)はこの項の値に関わらず表示されます。

##ターミナルAPIの仕様
<a href="http://hon.jp/doc/about_terminal_api.html">こちら</a>を参照してください。
