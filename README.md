#「hon.jpターミナルAPI」一括データ登録Webアプリ（サンプル）

##概要
jQuery & HTMLを使った、「hon.jpターミナルAPI」のサンプルPHPアプリです。[handsontable](http://handsontable.com/)を用いて電子書籍の書誌情報をExcel風に入力し、そのデータを一括でAPI側へHTTP POSTします。面倒なら、Excel上のデータをそのままコピペすることもできます。
<br>
<br>
![hon.jpターミナルAPI 一括データ登録アプリ](http://hon.jp/doc/terminal_sampleapp.jpg)

※「hon.jpターミナル」アカウントが必要です。 詳細については[こちら](http://hon.jp/doc/honjpterminal.html)をお読みのうえ、(mailto:info@hon.jp)までメールでアカウント要請ください

##ソース見本を使う前にインストールしてください。
+ PHP4 (サンプルでは4.4.9を使いました）
+ [PEAR - HTTP/Request.php](http://pear.php.net/)
+ [jQuery](http://jquery.com/)
+ [handsontable](http://handsontable.com/)

##使い方
(./src/honjp_terminal_api_support.php)をブラウザから読み込むと、実際にデータを入力するためのhandsontable(表計算風シート)が表示されます。<br>
hon.jpのアカウントとパスワードを入力し、handsontableの行ごとに各書誌のデータを入力します。コピペが可能なので、Excel上のデータをまるまる移すことも可能です。
「[著者／ブランド／掲載物]を追加する」ボタンを押すと、著者の項目、ブランドの項目、掲載物の項目を入力する列が増え、複数入力することができます。
データの入力が終わったら、「データ登録」ボタンを押して送信。データのXML整形＆hon.jpターミナルAPIへの送信を行うスクリプト(./src/getDataFromHandsontable.php)の処理を待てば登録完了。

##handsontableの各列項目の設定
handsontableに出力する列の各項目は、./lib/honjpAPITagsというtsvファイルを読み込んで出力しています。
このファイルは、ターミナルAPIで、書誌情報を更新するときに利用できるxmlタグのインデックスとなっています。
このファイルの各行の最後の項目が「'display'」という項になっていて、「0か1」を入力します。
1が入力されている項目のみがhandsontableに出力されますが、必須項目(namespace, title)はこの項の値に関わらず表示されます。

##ターミナルAPIの仕様
[こちら](http://hon.jp/doc/about_terminal_api.html)を参照してください。
