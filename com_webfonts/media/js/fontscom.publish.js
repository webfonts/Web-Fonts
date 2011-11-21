window.addEvent('domready', function(){

    var request = new Request({
	method : 'post',
	url : 'index.php?option=com_webfonts&task=fontscom.publish&format=raw',
	onComplete : function(response){
	    console && console.log(response);
	}
    });
    request.send();

});