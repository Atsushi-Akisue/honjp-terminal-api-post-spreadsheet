#hon.jp ターミナルAPI サンプルアプリ

##概要
jQuery&HTMLで動作する<a href="https://github.com/Atsushi-Akisue/jquery-handsontable" target="_blank">handsontable</a>のアプリを用いて書誌情報をエクセル風に入力させ、そのデータをAjaxでPOSTし、POSTした先のスクリプトでXMLデータに整形して、hon.jp ターミナルAPIへPOSTする <br><br>

(※hon.jpアカウントが必要です。 申請は<a href="http://hon.jp/doc/honjpterminal.html" target="_blank">こちら</a>をお読みになった上、<a href="mailto:info@hon.jp">info@hon.jp</a>までメールしてください)

##サンプル　実行環境
PHP 4.4.9<br>
PEAR - HTTP/Request.phpを利用<br>
jQuery<br>
handsontable (jQuery利用アプリ)<br>

##使い方
hon.jpのアカウントとパスを入力し、handsontableの列ごとに各書誌のデータを入力していく。
コピペが可能なので、Excelのデータをごっそりコピペも可能。
「著者／ブランド／掲載物を追加する」ボタンを押すと、著者の項目、ブランドの項目、掲載物の項目を入力する列が増え、複数入力することができます。
データの入力が終わったら、「データ登録」ボタンを押して送信。データのXML整形＆送信スクリプト(./getDataFromHandsontable.php)の処理を待てば登録完了。

##handsontableの各列項目の変更
handsontableに出力する列の各項目は、/lib/honjpAPITagsというtsvファイルを読み込んで出力しています。
このファイルは、ターミナルAPIで利用できるxmlタグのインデックスとなっています。
このファイルの各行の最後の項目が「'display'」という項になっていて、「0か1」を入力します。
1が入力されている項目のみがhandsontableに出力されますが、必須項目はこの項の値に関わらず表示されます。