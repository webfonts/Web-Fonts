window.addEvent('domready', function(){

    var enterOrClick = function(inputBox, clicker, triggerMethod){
	$(clicker).addEvent('click', triggerMethod);
	$(inputBox).addEvent('keypress', function(evt){
	    if(evt.key === 'enter') {
		evt.stop();
		triggerMethod();
	    }
	});
    };

    var search = function(){

	that = {};

	/* Might need to pull this out of this object if this is all it does */
	that.search = function(){
	    $('adminForm').submit();
	};

	return that;

    };

    SqueezeBox.initialize();

    var initFontIcons = function(){

	var i = 1;
	
	$$('.fontTile').each(function(el){
	    var id  = 'tileMeta' + i;
	    i++;
	    el.addEvent('mouseover', function(){
		el.addClass('activeTile');
	    });
	    el.addEvent('mouseout', function(){
		el.removeClass('activeTile');
	    });
	    el.addEvent('click', function(){
		SqueezeBox.open($(id), { handler: 'clone', size: {x: 550, y: 200} });
	    });
	});
    }();

    var r = $('resetForm');

    r && r.addEvent('click', function(){
	$('filters').dispose();
	var limit = $$('input[name="limitstart"]')[0];
	limit && limit.setProperty('value', '0');
	$('adminForm').submit();
    });

    searcher = search();

    enterOrClick('keyword', 'keywordClick', searcher.search);

});

var addFont = function(fid){
    fontAction(fid, 'google.addFont');
}

var removeFont = function(fid){
    fontAction(fid, 'google.removeFont');
}

var fontAction = function(fid, action){
    $('fontFormTask').setProperty('value', action);
    $('fid').setProperty('value', fid);
    $('adminForm').setProperty('method', 'POST');
    $('adminForm').submit();
}