CMA_comments_init = function($) {
	
	
	var setupAddForm = function(form) {
		var addLink = form.parents('.cma-comments').find('.cma-comment-add-link');
		addLink.click(addLinkHandler);
		form.submit(addHandler);
		form.find('.cma-comment-form-cancel').click(function(e) {
			createCancelHandler(addLink, form)(e);
		});
	};
	
	
	var addLinkHandler = function(e) {
		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}
		var link = $(this);
		link.hide();
		var form = link.parents('.cma-comments').find('.cma-comments-add form');
		form.show();
		form.find('textarea').focus();
	};
	
	var createFormLoader = function(form) {
		var loader = $(document.createElement('div'));
		loader.addClass('loader');
		form.append(loader);
		return loader;
	};
	

	var editHandler = function(e) {
		e.preventDefault();
		e.stopPropagation();
		var comment = $(this).parents('div.cma-single-comment');
		var inner = comment.find('.cma-comment-inner');
		inner.hide();
		var form = comment.parents('.cma-comments').find('.cma-comments-add form').clone();
		var formContainer = $(document.createElement('div'));
		formContainer.addClass('cma-comment-form-container');
		formContainer.append(form);
		comment.append(formContainer);
		var submit = form.find('input[type=submit]');
		submit.val(submit.data('labelEdit'));
		form.find('.cma-comment-form-cancel').click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			var container = $(this).parents('div.cma-single-comment');
			createCancelHandler(container.find('.cma-comment-inner'), null, container.find('form'))(e);
		});
		form.find('input[name=cma-action]').val('comment-edit');
		form.append($(document.createElement('input')).attr('type', 'hidden').attr('name', 'cma-comment-id').val(comment.data('commentId')));
		form.find('textarea').val($.trim(comment.find('.cma-comment-content').text()));
		form.submit(function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var loader = createFormLoader(form);
			$.post(form.attr('action'), form.serialize(), function(response) {
				var event = document.createEvent('Event');
				event.initEvent('cma_comment_edit_ajax_response', true, true);
				event.ajaxResponse = response;
				form[0].dispatchEvent(event);
				loader.remove();
				if (response.success) {
					formContainer.remove();
					inner.show();
					if (response.hasOwnProperty('html') && response.html) { // replace comment content if approved
						comment.find('.cma-comment-content').html(response.html);
					}
				}
				$().toastmessage(response.success ? 'showSuccessToast' : 'showErrorToast', response.msg);
			});
		});
		
		form.show();
		form.find('textarea').focus();
		
	};
	$('.cma-comment-edit-link').click(editHandler);
	

	var deleteHandler = function(e) {
		e.preventDefault();
		e.stopPropagation();
		var comment = $(this).parents('div.cma-single-comment');
		$.post(location.href, {"cma-action": "comment-delete", "cma-comment-id" : comment.data('commentId'), "nonce" : $(this).data('nonce')}, function(response) {
			if (response.success) {
				comment.remove();
			}
			$().toastmessage(response.success ? 'showSuccessToast' : 'showErrorToast', response.msg);
		});
	};
	$('.cma-comment-delete-link').click(deleteHandler);
	
	
	var createCancelHandler = function(toShow, toHide, toRemove) {
		return function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (toHide) toHide.hide();
			if (toRemove) toRemove.remove();
			if (toShow) toShow.show();
		};
	};
	
	
	$('.cma-comments-add .cma-comment-form-cancel').click(function(e) {
		var container = $(this).parents('.cma-comments');
		createCancelHandler(container.find('.cma-comment-add-link'), container.find('.cma-comments-add form'))(e);
	});
	
	var addHandler = function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var form = $(this);
		var loader = createFormLoader(form);
		
		$.post(form.attr('action'), form.serialize(), function(response) {
			var event = document.createEvent('Event');
			event.initEvent('cma_comment_add_ajax_response', true, true);
			event.ajaxResponse = response;
			form[0].dispatchEvent(event);
			loader.remove();
			if (response.success) {
				form.hide();
				form.find('textarea').val(''); // erase textarea
				form.find('textarea').keyup(); // update limity
				form.parents('.cma-comments').find('.cma-comment-add-link').show(); // show add link
				if (response.hasOwnProperty('html') && response.html) { // append comment html if approved
					var comment = $(response.html);
					form.parents('.cma-comments').find('.cma-comments-list').append(comment);
					comment.find('.cma-comment-edit-link').click(editHandler);
					comment.find('.cma-comment-delete-link').click(deleteHandler);
				}
			}
			$().toastmessage(response.success ? 'showSuccessToast' : 'showErrorToast', response.msg);
		});
		
	};
	
	// Setup
	setupAddForm($('.cma-comments-add form'));
	
	
};
