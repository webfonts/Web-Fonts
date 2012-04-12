var buildSpinner = function(){

    var flag = 0;

    var that = {
	show : function(){
	    if(flag === 0){
		$('sbox-content').getFirst().setStyle('display', 'none');
		$('thinking').removeClass('hidden').inject('sbox-content', 'top');
		flag = 1;
	    }
	},
	hide: function(){
	    if(flag === 1){
		$('thinking').addClass('hidden').inject('webFonts', 'bottom');
		$('sbox-content').getFirst().setStyle('display', 'block'); 
		flag = 0;
	    }
	}
	
    };

    return that;

};

window.wfspinner = buildSpinner();

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
		SqueezeBox.open($(id), { handler: 'clone', size: {x: 550, y: 200}, onClose : window.wfspinner.hide });
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

    var form = $('adminForm');

    enterOrClick('keyword', 'keywordClick', function(){ form.submit(); } );

});

var addFont = function(fid){
    window.wfspinner.show();
    fontAction(fid, 'google.addFont');
};

var removeFont = function(fid){
    window.wfspinner.show();
    fontAction(fid, 'google.removeFont');
};

var fontAction = function(fid, action){
    $('fontFormTask').setProperty('value', action);
    $('fid').setProperty('value', fid);
    $('adminForm').setProperty('method', 'POST');
    $('adminForm').submit();
};