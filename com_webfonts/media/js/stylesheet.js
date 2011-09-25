window.addEvent('domready', function(){

    var selectorBasedLayout = function(){
	var task = function(task){
	    $('task').setProperty('value', task);
	}
	
	$('addSelector').addEvent('click', function(){
	    task('stylesheet.addSelector');
	    $('adminForm').submit();
	});
	
	$('selector').addEvent('keypress', function(evt){
	    if(evt.key === 'enter') {
		evt.stop();
		task('stylesheet.addSelector');
		$('adminForm').submit();
	    }
	});
	
	$('fontSelector').addEvent('change', function(){
	    task('stylesheet.assignFont');
	});
	
	$('saveChanges').addEvent('click', function(){
	    task('stylesheet.updateSelectors');
	});
    };

    var fontBasedLayout = function(){

	function addSelector(fontId, value, handler){
	    $('fontId').setProperty('value', fontId);
	    $('selector').setProperty('value', value);
	    $('vendor').setProperty('value', handler);
	    $('task').setProperty('value', 'stylesheet.addSelector');
	    fontId && value && $('adminForm').submit();
	}
	
	$$('.addSelectors').each(function(sel){ 
	    sel.addEvent('keypress', function(evt){
		if(evt.key === 'enter') {
		    addSelector(sel.getProperty('font'), sel.getProperty('value'), sel.getProperty('handler'));
		    evt.stop();
		}
	    });
	});
	
	$$('.addSelector').each(function(sel){ 
	    sel.addEvent('click', function(evt){
		var fontId = sel.getProperty('font');
		var value = $(fontId).getProperty('value');
		var handler = $(fontId).getProperty('handler');
		addSelector(fontId, value, handler);
	    });
	});

	function addFallBack(fontId, value, handler){
	    $('fontId').setProperty('value', fontId);
	    $('fallBack').setProperty('value', value);
	    $('vendor').setProperty('value', handler);
	    $('task').setProperty('value', 'stylesheet.updateFallBack');
	    fontId && value && $('adminForm').submit();
	}

	$$('.fallBack').each(function(fb){ 
	    fb.addEvent('keypress', function(evt){
		if(evt.key === 'enter') {
		    addFallBack(fb.getProperty('font'), fb.getProperty('value'), fb.getProperty('handler'));
		    evt.stop();
		}
	    });
	});

	$$('.addFallBack').each(function(fb){
	    fb.addEvent('click', function(evt){
		var fontId = fb.getProperty('font');
		var text = 'fallBack' + fontId;
		var value = $(text).getProperty('value');
		var handler = $(text).getProperty('handler');
		addFallBack(fontId, value, handler);
	    });	    
	});

    };
    
    var selectorBased = $('addSelector');
    
    if(selectorBased) {
	selectorBasedLayout();
    } else {
	fontBasedLayout();
    }


    
});

function removeSelector(id){
    $('sid').setProperty('value', id);
    $('task').setProperty('value', 'stylesheet.removeSelector');
    $('adminForm').submit();
}