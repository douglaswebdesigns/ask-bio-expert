CMA_Utils = {};

CMA_Utils.addSingleHandler = function(handlerName, selector, action, func) {
	jQuery(selector).each(function() {
		var obj = jQuery(this);
		if (obj.data(handlerName) != '1') {
			obj.data(handlerName, '1');
			obj.on(action, func);
		}
	});
};


CMA_script_init = function ($) {
	
	$.limitify = function() {
		if (typeof CMA_LIMITIFY == 'undefined') return;
		$('*[data-limitify]').filter(function() {
			return ($(this).attr('data-limitify') > 0 && !this.limitifyWorking);
		}).each(function() {
			this.limitifyWorking = true;
			var obj = $(this);
			var limit = obj.data('limitify');
			var tooltip = $(document.createElement('div'));
			tooltip.addClass('cma-limitify');
			obj.after(tooltip);
			
			var update = function() {
				var len = obj.val().length;
				if (len > limit) {
					obj.val(obj.val().substr(0, limit));
					len = limit;
				}
				tooltip.text(len +"/"+ limit);
			};
			update();
			obj.keyup(update);
			
			return this;
		});
		
	};
	
	
	if (CMA_Variables.navBarAutoSubmit=="1") {
		CMA_Utils.addSingleHandler('navBarAutoSubmitHandler', 'form.cma-filter select', 'change', function() {
			var form = $(this).parents('form').first();
			form.submit();
		});
	}
	
	
	CMA_Utils.addSingleHandler('categoryCustomFields', '.cma-question-form-container select[name=thread_category]', 'change', function() {
		var obj = $(this);
		var target = obj.parents('.cma-question-form-container').find('.cma-category-custom-fields');
		target.html($('<img />', {src: CMA_Variables.loaderBarUrl}));
		var data = {categoryId: obj.val(), action: 'cma_load_category_custom_fields'};
		$.post(CMA_Variables.ajaxUrl, data, function(response) {
			target.html(response);
		});
	});
	
	
	CMA_Utils.addSingleHandler('bestAnswerHandler', '.cma-mark-best-answer a, .cma-unmark-best-answer a', 'click', function() {
		var link = $(this);
		var answer = link.parents('.cma-answer').first();
		var answersTable = link.parents('.cma-answers-list').first();
		var answerId = answer.data('answerId');
		var url = link.parents('[data-permalink]').first().data('permalink');
		var removeOther = (link.parents('.cma-mark-best-answer').length > 0
				&& (CMA_Variables.bestAnswerRemoveOther == "1" && confirm(CMA_Variables.bestAnswerRemoveOtherLabel)));
		var data = {'cma-action': 'mark-best-answer', 'cma-answer-id': answerId, 'nonce': answersTable.data('bestAnswerNonce'), 'remove-other': removeOther};
		$.post(url, data, function (response) {
			if (response.success == 1) {
				if (removeOther && $('body').hasClass('single-cma_thread')) {
					location.reload();
				}
				answersTable.find('.cma-answer[data-best-answer=1]').attr('data-best-answer', 0);
				answersTable.attr('data-best-answer-defined', response.marked ? 1 : 0);
				if (response.marked == 1) {
					answer.attr('data-best-answer', 1);
				}
				$().toastmessage('showSuccessToast', response.message);
			} else {
				$().toastmessage('showErrorToast', response.message);
			}
		});
		return false;
	});
	
	
	CMA_Utils.addSingleHandler('favoriteQuestionHandler', '.cma-question-favorite-link', 'click', function() {
		var obj = $(this);
		obj.blur();
		var question = obj.parents('.cma-question-table').first();
		var nonce = question.data('favoriteNonce');
		var url = obj.parents('[data-permalink]').first().data('permalink');
		var data = {'cma-action': 'favorite', 'nonce': nonce};
		$.post(url, data, function (data) {
			if (data.success == 1) {
				question.attr('data-favorite', data.favorite ? 1 : 0);
				obj.attr('title', data.title);
				obj.find('.number').text(data.number);
				$().toastmessage('showSuccessToast', data.message);
			} else {
				$().toastmessage('showErrorToast', data.message);
			}
		});
		return false;
	});
	

		
	$.limitify();
	
	CMA_Utils.addSingleHandler('voteHandler', '.cma-thumbs-up, .cma-thumbs-down', 'click', function() {
		var link = $(this);
		var questionTable = link.parents('.cma-questions-widget, .cma-wrapper, .cma-thread-wrapper').first().find('.cma-question-table');
		var threadId = questionTable.data('questionId');
		var answerRow = link.parents('tr.cma-answer').first();
		var answerId = answerRow.data('answerId');
		var recordContainer = (answerId ? answerRow : questionTable);
		var nonce = recordContainer.data('ratingNonce');
		
		// Show loader
		var loader = $('<div/>', {"class":"cma-rating-loading"});
		recordContainer.find('.cma-rating').append(loader);
		
		// Request
		$.post(CMA_Variables.ajaxUrl, {
			action: "cma_vote",
			threadId: threadId,
			answerId: answerId,
			value: (link.hasClass('cma-thumbs-up') ? 'up' : 'down'),
			nonce: nonce
		}, function(data) {
			loader.remove();
			if (data.success == 1) {
				recordContainer.attr('data-rating', data.rating);
				recordContainer.find('.cma-rating-count').text(data.rating);
				$().toastmessage('showSuccessToast', data.message);
			} else {
				$().toastmessage('showErrorToast', data.message);
			}
		});
		
	});
	
	CMA_Utils.addSingleHandler('cmaFilterHandler', 'form.cma-filter', 'submit', function(ev) {
		// Set form action to the chosen category (or subcategory) URL
		var form = $(this);
		var secondaryCategoryUrl = form.find('.cma-filter-category-secondary').find(":selected").data('url');
		if (secondaryCategoryUrl) {
			form.attr('action', secondaryCategoryUrl);
		} else {
			var primaryCategoryUrl = form.find('.cma-filter-category-primary').find(":selected").data('url');
			if (primaryCategoryUrl) {
				form.attr('action', primaryCategoryUrl);
			} else {
				var categoryUrl = form.find('.cma-filter-category').find(':selected').data('url');
				if (categoryUrl) {
					form.attr('action', categoryUrl);
				}
			}
		}
	});
	
	CMA_Utils.addSingleHandler('filterCategoryPrimaryHandler', '.cma-filter-category-primary', 'change', function() {
		// Load subcategories of chosen primary category.
		var selectBox = $(this);
		var option = this.options[this.selectedIndex];
		var subcategoriesBox = selectBox.parents('form').find('.cma-filter-category-secondary').first();
		var createOption = function(parent, value, content, url) {
			var option = document.createElement('option');
			parent.append(option);
			$(option).attr('value', value).data('url', url).html(content);
		};
		$.post(selectBox.parents('form').attr('action'), {'cma-action': 'load-subcategories', 'cma-category-id': option.value}, function(categories) {
			subcategoriesBox.find('option').remove();
			for (var i=0; i<categories.length; i++) {
				createOption(subcategoriesBox, categories[i].id, categories[i].name, categories[i].url);
			}
		});
	});
	
	CMA_Utils.addSingleHandler('threadFollowHandler', '.cma-follow-link, .cma-unfollow-link', 'click', function() {
		var link = $(this);
		var categoryId = link.data('categoryId');
		var data = {'cma-action': 'follow', 'categoryId' : categoryId, 'nonce' : link.data('nonce')};
		$.post(link.attr('href'), data, function (response) {
			if (response.success) {
				if (categoryId) {
					link.parents('tr').first().attr('data-is-follower', response.isFollower ? 1 : 0);
				} else {
					link.parents('.cma-question-table').attr('data-is-follower', response.isFollower ? 1 : 0);
				}
				$().toastmessage('showSuccessToast', response.message);
			} else {
				$().toastmessage('showErrorToast', response.message);
			}
		}, 'JSON');
		return false;
	});
	
	
	CMA_Utils.addSingleHandler('reportSpamHandler', '.cma-report-spam', 'click', function() {
		var link = $(this);
		var nonce = link.parents('.cma-answers-list, .cma-question-table').first().data('spamNonce');
		var answerId = link.parents('.cma-answer').first().data('answerId');
		var data = {'cma-action': 'report-spam', nonce: nonce, answerId: answerId};
		$.post(link.attr('href'), data, function (response) {
			if (response.success) {
				link.parents('.cma-answer, .cma-question-table').first().attr('data-spam', 1);
				$().toastmessage('showSuccessToast', response.message);
			} else {
				$().toastmessage('showErrorToast', response.message);
			}
		}, 'JSON');
		return false;
	});
	
	CMA_Utils.addSingleHandler('unmarkSpamHandler', '.cma-unmark-spam', 'click', function() {
		var link = $(this);
		var nonce = link.parents('.cma-answers-list, .cma-question-table').first().data('spamNonce');
		var answerId = link.parents('.cma-answer').first().data('answerId');
		var data = {'cma-action': 'unmark-spam', nonce: nonce, answerId: answerId};
		$.post(link.attr('href'), data, function (response) {
			if (response.success) {
				link.parents('.cma-answer, .cma-question-table').first().attr('data-spam', 0);
				$().toastmessage('showSuccessToast', response.message);
			} else {
				$().toastmessage('showErrorToast', response.message);
			}
		}, 'JSON');
		return false;
	});
	
	CMA_Utils.addSingleHandler('deleteThreadHandler', '.cma-thread-delete-link', 'click', function() {
		var isNotAjax = ($(this).parents('.cma-widget-ajax').length == 0);
		if (isNotAjax && !confirm(CMA_Variables.confirmThreadDelete)) {
			return false;
		}
	});
	
	var answersWidgetPaginationHandler = function() {
		var link = $(this);
		var container = link.parents('.cma-answers-widget');
		container.addClass('cma-loading');
		container.append($('<div/>', {"class":"cma-loader"}));
		$.ajax({
			url: this.href,
			success: function(response) {
				var html = $(response);
				container.find('.cma-loader').remove();
				container.html(html.find('.cma-answers-widget').html());
				container.find('.cma-pagination a').click(answersWidgetPaginationHandler);
			}
		});
		return false;
	};
	CMA_Utils.addSingleHandler('answersWidgetPaginationHandler', '.cma-answers-widget[data-ajax=1] .cma-pagination a', 'click', answersWidgetPaginationHandler);
	
	
	var dragCounter = 0;
	$('.cma-file-upload').parents('form').on('dragenter', function(e) {
		e.stopPropagation();
		e.preventDefault();
		$(this).addClass('cma-dragover');
		dragCounter++;
	});
	
	$('.cma-file-upload').parents('form').on('dragleave', function(e) {
		e.stopPropagation();
		e.preventDefault();
		dragCounter--;
		if (dragCounter == 0) {
			$(this).removeClass('cma-dragover');
		}
	});
	
	$('.cma-file-upload').parents('form').on('dragover', function(e) {
//		e.stopPropagation();
		e.preventDefault();
//		$(this).addClass('cma-dragover');
	});
	
	function readFiles(container, files) {
		if (typeof FormData == 'undefined' || typeof FileReader == 'undefined' || !'draggable' in document.createElement('span')) return false;
		if (container.data('progress') == 1) return true;
		container.data('progress', 1);
		var disabledElements = container.find('input[type=file]');
		disabledElements.attr('disabled', 'disabled');
		var list = container.find('.cma-file-upload-list');
		var formData = new FormData();
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			formData.append('cma-file[]', file);
			var item = document.createElement('li');
			item.appendChild(document.createTextNode(file.name));
			item.setAttribute('class', 'ajax progress');
			item.setAttribute('data-file-name', file.name);
			list.append(item);
		}
		formData.append('cma-action', 'upload');
		var xhr = new XMLHttpRequest();
//		console.log(container.parents('form').attr('action'));
		xhr.open('POST', container.parents('form').attr('action'));
		xhr.onload = function(e) {
			var response;
			try {
				response = $.parseJSON(e.target.response);
			} catch (e) {}
			if (e.target.status == 200 && typeof(response) == 'object') {
				for (var i=0; i<response.length; i++) {
					var fileResult = response[i];
					var item = list.find('li[data-file-name="'+ fileResult.name +'"]');
					item.removeClass('progress');
					if (fileResult.status == 'OK') {
						var hidden = document.createElement('input');
						hidden.setAttribute('type', 'hidden');
						hidden.setAttribute('name', 'attached[]');
						hidden.setAttribute('value', fileResult.id);
						item.append(hidden);
					} else {
						item.addClass('error');
						item.append('<span>' + (fileResult.msg ? fileResult.msg : 'error') + '</span>');
					}
				}
			}
			list.find('li.progress').remove();
			disabledElements.removeAttr('disabled');
			container.data('progress', 0);
			container.trigger('cma:uploadSuccess');
		};
		if ("upload" in new XMLHttpRequest) {
			xhr.upload.onprogress = function (event) {
				if (event.lengthComputable) {
					var complete = (event.loaded / event.total * 100 | 0);
					//progress.value = progress.innerHTML = complete;
				}
			};
		}
		xhr.send(formData);
		return true;
	};
	$('.cma-file-upload').parents('form').on('drop', function(e) {
		var obj = $(this);
		e.stopPropagation();
		e.preventDefault();
		$(this).removeClass('cma-dragover');
		if (e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files) {
			readFiles(obj.find('.cma-file-upload'), e.originalEvent.dataTransfer.files);
		}
	});
	
	$('.cma-file-select').click(function(e) {
		e.stopPropagation();
		e.preventDefault();
		var file = $(this).parent().find('input[type=file]');
		file.click();
	});
	$('.cma-file-upload input[type=file]').on('change', function(e) {
		var container = $(this).parents('.cma-file-upload');
		if (readFiles(container, this.files)) {
			$(this).removeAttr('name');
		} else {
			var list = container.find('.cma-file-upload-list');
			list.find('li.input').remove();
			for (var i=0; i<this.files.length; i++) {
				var file = this.files[i];
				var item = document.createElement('li');
				item.appendChild(document.createTextNode(file.name));
				item.setAttribute('class', 'input');
				list.append(item);
			}
		}
	});
	
	$('.cma-question-table:not(.cma-count-view-sent)').each(function() {
		var obj = $(this);
		obj.addClass('cma-count-view-sent');
		$.post(obj.data('permalink'), {"cma-action":"count-view"}, function() {
//			console.log('count-view-ok');
		});
	});
	

	
};



jQuery(CMA_script_init);


// -------------------------------------------------------------------------------------------------------------

jQuery(function() {
	if (typeof CMA_social_box_enabled == 'boolean' && CMA_social_box_enabled) {
	
		(function () {
			var po = document.createElement('script');
			po.type = 'text/javascript';
			po.async = true;
			po.src = 'https://apis.google.com/js/plusone.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(po, s);
		})();
		
		(function (d, s, id) {
			var fbAsyncInitCMA = function () {
				// Don't init the FB as it needs API_ID just parse the likebox
				FB.XFBML.parse();
			};
			if (typeof(window.fbAsyncInit) == 'function') {
				var fbAsyncInitOld = window.fbAsyncInit;
				window.fbAsyncInit = function() {
					fbAsyncInitOld();
					fbAsyncInitCMA();
				};
			} else {
				window.fbAsyncInit = fbAsyncInitCMA;
			}
		
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id))
				return;
			js = d.createElement(s);
			js.id = id;
			js.src = "//connect.facebook.net/en_US/all.js";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
		
	}
});
