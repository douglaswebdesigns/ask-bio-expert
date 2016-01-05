CMA_private_question_init = function($) {
	
	var sendQuestion = function(e) {
    	
		e.preventDefault();
		e.stopPropagation();
		
    	var data = dialog.serialize();
    	
    	// Add loader
    	$('<div/>', {
		    'class': 'loader',
		}).appendTo(dialog);
    	// Clear errors
    	dialog.find('.ui-state-error').removeClass('ui-state-error');
    	var buttonPane = dialog.parent().find('.ui-dialog-buttonpane');
    	buttonPane.find('.error-msg').remove();
    	
    	// Send POST
    	$.post(CMA_Variables.CMA_URL, data, function(response) {
    		if (response.success) {
    			dialog.dialog('close');
    			$().toastmessage('showSuccessToast', response.msg);
    		} else {
    			dialog.find('.loader').remove();
    			if (response.errors) $.each(response.errors, function(name, error) {
    				if (name == 'title' || name == 'question') {
    					dialog.find('*[name='+ name +']').addClass('ui-state-error');
    					
    				}
    				buttonPane.append($('<div/>', {"class":'error-msg'}).text(error));
    			})
    		}
    	});
    	
    };
	
	var dialog = $('<form/>', {
	    id: 'cma-private-question-form',
	    style: 'display:none'
	}).appendTo('body')
	.dialog({
	      autoOpen: false,
	      minHeight: 300,
	      width: "50%",
	      modal: true,
	      buttons: {
	        "Send": sendQuestion,
	        Cancel: function() {
	        	dialog.dialog( "close" );
	        }
	      },
	      close: function() {
	    	  dialog.dialog('option', 'title', '');
	    	  dialog.html('');
	      }
	}).submit(sendQuestion);
	
	
	CMA_Utils.addSingleHandler('privateQuestionHandler', '.cma-private-question-icon', 'click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('<div/>', {
		    'class': 'loader',
		}).appendTo(dialog);
		var link = $(this);
		dialog.dialog('open');
		$.post(CMA_Variables.CMA_URL, {"cma-action":"display-private-question-form",user:link.data('userId')}, function(response) {
			// Load form
			dialog.html(response);
			// Set title
			dialog.dialog('option', 'title', dialog.find('fieldset').data('title'));
			// Set buttons labels
			$('#top .ui-dialog-buttonset button:eq(0)').find('span').text(dialog.find('fieldset').data('labelSend'));
			$('#top .ui-dialog-buttonset button:eq(1)').find('span').text(dialog.find('fieldset').data('labelCancel'));
		});
	});
		
};
