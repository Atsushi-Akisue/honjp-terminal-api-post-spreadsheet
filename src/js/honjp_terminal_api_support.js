function loaded() {
	//handsontable���������
	if(getBrowserWidth() < 1240)	//wrapper��max-width(1240px)��꾮����
		document.getElementById("wrapper").style.cssText = "width: " + (getBrowserWidth() - 60) + "px;";	//60�ϥޡ�����ʬ

	//handsontable�ι⤵�����
	var rest_height = getBrowserHeight() - (document.getElementById("options").offsetHeight + 20);	//20�ϥޡ�����ʬ
	if(rest_height < 800) {	//wrapper��max-height(800px)��꾮����
		document.getElementById("exampleGrid").style.cssText = "height: " + (rest_height - 40) + "px;";	//40�ϥޡ�����ʬ
		document.getElementById("waiting").style.cssText = "height: " + (rest_height - 40) + "px; line-height: " + (rest_height - 40) + "px;";	//40�ϥޡ�����ʬ
	}
	else
		document.getElementById("waiting").style.cssText = "line-height: 800px;";
}

function getBrowserWidth() {
	if ( window.innerWidth )
		return window.innerWidth;
	else if ( document.documentElement && document.documentElement.clientWidth != 0 )
		return document.documentElement.clientWidth;
	else if ( document.body )
		return document.body.clientWidth;
	return 0;
}

function getBrowserHeight() {
	if ( window.innerHeight )
		return window.innerHeight;
	else if ( document.documentElement && document.documentElement.clientHeight != 0 )
		return document.documentElement.clientHeight;
	else if ( document.body )
		return document.body.clientHeight;
	return 0;
}

document.addEventListener('DOMContentLoaded', function () { setTimeout(loaded, 200); }, false);