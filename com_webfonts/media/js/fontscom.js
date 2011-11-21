window.addEvent('domready', function(){  

    var initCreateAccount = function(){
	$('createAccountButton').addEvent('click', function(){
	    $('preexistingAccount').addClass('hidden');
	    $('newAccount').removeClass('hidden');
	});
	$('newAccountCancel').addEvent('click', function(){
	    $('preexistingAccount').removeClass('hidden');
	    $('newAccount').addClass('hidden');
	});
    }

    if($('createAccountButton')) {
	initCreateAccount();
    }

    var initDomainList = function(){

	var li = $('cloneDomain');

	var focus = function(){
	    if(this.value === 'www.anotherdomain.com'){
		this.value = '';
		this.removeClass('greyed');
	    }
	};

	var blur = function(){
	    if(this.value === ''){
		this.value = 'www.anotherdomain.com';
		this.addClass('greyed');
	    }
	};

	li.getElements('input[type="text"]').addEvent('focus', focus).addEvent('blur', blur);

	li.getElements('input[type="button"]').addEvent('click', function(){
	    var cl = li.clone([true, false]);
	    li.getElements('input[type="text"]').setProperty('value', 'www.anotherdomain.com').addClass('greyed');
	    cl.getElements('input[type="text"]').addEvent('focus', focus).addEvent('blur', blur);
	    cl.getElements('input[type="button"]').dispose();
	    var rem = $('cloneRemove').clone([true, false]);
	    rem.removeClass('hidden').
		addEvent('click', function(){ rem.getParent().dispose(); }).
		inject(cl, 'bottom');
	    cl.inject(li, 'before');
	});
	
    }();

    var selectTab = function(fName, tab, index){
	if($$('.tab-setup.open').length === 0){
	    window.setTimeout(fName, 500);
	} else {
	    $$('.tab-setup.open').removeClass('open').addClass('closed');
	    $$(tab).addClass('open');
	    $$('.current dd')[0].setStyle('display', 'none');
	    $$('.current dd')[index].setStyle('display', 'block');
	}
    }

    var selectProject = function(){
	return selectTab(selectProject,'.tab-project',1);
    }

    var selectFonts = function(){
	return selectTab(selectFonts,'.tab-fonts',2);
    }


    if(Window.wfEditingFonts === true){
	selectFonts();
    } else {
	if(Window.wfKeySaved === true){
	    selectProject();
	}
    }

    var updateProjectOnChange = function(){
	$('currentProject').addEvent('change', function(){
	    $('projectTask').setProperty('value', 'fontscom.setProject');
	    $('manageProjects').submit();
	});
    }();
    
    var removables = function(){
	$$('.removable').each(function(el){
	    el.addEvent('click', function(){
		el.getParent().dispose();
	    });
	});
    }();

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
		SqueezeBox.open($(id), { handler: 'clone', size: {x: 550, y: 270} });
	    });
	});
    }();

    var fixListLimit = function(){
	var l = $('limit');
	if(l){ 
	    l.dispose();
	    $$('.limit')[0].dispose();
	}
    }();

    var addFilterEvents = function(){
	['freeorpaid','alpha', 'foundry', 'language', 'classification', 'designer'].each(function(el){
	    var item = $(el);
	    item && item.addEvent('change', function(){
		var limit = $$('input[name="limitstart"]')[0];
		limit && limit.setProperty('value', '0');
		$('fontForm').submit();
	    });
	});

	var r = $('resetForm');
	r && r.addEvent('click', function(){
	    $('filters').dispose();
	    var limit = $$('input[name="limitstart"]')[0];
	    limit && limit.setProperty('value', '0');
	    $('fontForm').submit();
	});
    }();


});

var validateNew = function(f){

     if(!document.formvalidator.isValid(f)){
	 $('createEmailError').removeClass('hidden');
	 return false;
     }
};

var addFont = function(wfsfid){
    fontAction(wfsfid, 'fontscom.addFont');
};

var removeFont = function(wfsfid){
    fontAction(wfsfid, 'fontscom.removeFont');
};

var fontAction = function(wfsfid, action){
    $('fontFormTask').setProperty('value', action);
    $('wfsfid').setProperty('value', wfsfid);
    $('fontForm').submit();
};