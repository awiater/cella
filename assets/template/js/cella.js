function ConfirmDialog(message,yesfunction,nofunction=null) {
  		$('<div></div>').appendTo('body')
    .html('<div><h6>' + message + '?</h6></div>')
    .dialog({
      modal: true,
      title: '',
      zIndex: 10000,
      autoOpen: true,
      width: 'auto',
      resizable: false,
      buttons: {
        Yes: function() {
        	if (yesfunction!=null && $.isFunction(yesfunction)){
          		yesfunction();
          	}
		  $(this).dialog("close");
        },
        No: function() {
        	if (nofunction!=null && $.isFunction(nofunction)){
          		nofunction();
          	}
          $(this).dialog("close");
        }
      },
      close: function(event, ui) {
        $(this).remove();
      }
    });
    $(".ui-dialog-titlebar").addClass('bg-warning h-25 p-0');
    $(".ui-dialog-titlebar-close").addClass('d-none');
   }
   
function Dialog(message,type='info') {
  		$('<div></div>').appendTo('body')
    .html('<div><h6>' + message + '</h6></div>')
    .dialog({
      modal: true,
      title: '',
      zIndex: 10000,
      autoOpen: true,
      width: 'auto',
      resizable: false,
      buttons: {
        Ok: function() {
          $(this).dialog("close");
        }
      },
      close: function(event, ui) {
        $(this).remove();
      }
    });
    $(".ui-dialog-titlebar").addClass('bg-'+type+' h-25 p-0');
    $(".ui-dialog-titlebar-close").addClass('d-none');
}


function errorDialog(message){
	Dialog(message,'danger');
}

function toggleFullscreen(id){
	$("#"+id).toggleClass('fullscreen');
}

function requestSetGet(key,value){
	var queryParams = new URLSearchParams(window.location.search);
  	queryParams.set(key, value);
  	history.replaceState(null, null, "?"+queryParams.toString());
}

function requestGet(key=null){
		var $_GET = {};

		document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
    		function decode(s) {
        		return decodeURIComponent(s.split("+").join(" "));
    		}

    		$_GET[decode(arguments[1])] = decode(arguments[2]);
		});
		if (key!=null){
			if (key in $_GET){
				return $_GET[key];
			}else{
				return null;
			}
		}
		return $_GET;
}

function setActiveTabFromUrl(key='tab',patern='#tabs-@-tab'){
		var tab=requestGet(key);
		if (tab!=null){
			patern=patern.replace('@',tab);
			$(patern).tab('show');
		}
}

function setActiveTabToUrl(type='pill'){
		$('a[data-toggle="'+type+'"]').on('shown.bs.tab', function (event) {
  			var id=$(this).attr('id');
  			id=id.replace('tabs-','').replace('-tab','');
  			requestSetGet('tab',id);
		});
}


