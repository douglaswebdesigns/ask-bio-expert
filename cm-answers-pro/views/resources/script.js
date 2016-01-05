
jQuery(function($) {
	
	var performRequest = function(container, url, data) {
//		data.widget = 1;
		if (url.indexOf('widgetCacheId') == -1) {
			data.widgetCacheId = container.data('widgetCacheId');
		}
		container.addClass('cma-loading');
		container.append($('<div/>', {"class":"cma-loader"}));
		$.ajax({
			method: "GET",
			url: url,
			data: data,
			success: function(response) {
				var code = $('<div>' + response + '</div>');
//				var newContainer = code.find('.cma-questions-widget').clone();
//				console.log(newContainer);
				container.html('');
				container.append(code.find('.cma-questions-widget.cma-main-query'));
				//container = newContainer;
				//container.find('.cma-backlink').remove();
				container.find('.cma-loader').remove();
				container.removeClass('cma-loading');
				initHandlers(container);
				initTinyMCE();
			}
		});
	};
	
	
	var initHandlers = function(container) {
		if (typeof CMA_script_init == 'function') CMA_script_init($);
		navBarHandlerInit(container);
		paginationHandlerInit(container);
		loadThreadHandlerInit(container);
		backlinkHandlerInit(container);
		postQuestionHandlerInit(container);
		postAnswerHandlerInit(container);
		categoryHandlerInit(container);
		tagsHandlerInit(container);
		deleteQuestionHandlerInit(container);
		editQuestionHandlerInit(container);
		initTinyMCE(container);
		jQuery(document).trigger('glossaryTooltipReady');
	};
	
	
	var initTinyMCE = function(container) {
		if (typeof tinyMCE == 'undefined') return;
		$(container).find('.cma-form-content[data-tinymce=1]').each(function() {
			var obj = $(this);
			tinyMCE.init({
				mode : "textareas",
				selector: '#' + this.id,
				menubar: false,
				setup : function(ed) {
					ed.onChange.add(function(ed, l) {
						obj.val(tinyMCE.activeEditor.getContent());
					});
				}
			});
		});
	};
	
	
	
	var paginationHandlerInit = function(container) {
		$('.cma-pagination a', container).click(function() {
			link = $(this);
			performRequest(container, link.attr('href'), {});
			return false;
		});
	};
	
	
	
	var navBarHandlerInit = function(container) {
		$('form.cma-filter', container).submit(function() {
			var form = $(this);
			var data = {backlink: container.find('.cma-questions-widget, .cma-question-table').data('backlink')};
			form.find(':input[name]').each(function() {
				data[this.name] = this.value;
			});
			// Choose a proper category URL
			var url = form.find('select.cma-filter-category-secondary option:selected').data('url');
			if (!url) {
				url = form.find('select.cma-filter-category-primary option:selected').data('url');
				if (!url) {
					url = form.find('select.cma-filter-category option:selected').data('url');
					if (!url) {
						url = form.attr('action');
					}
				}
			}
			performRequest(
				container,
				url,
				data
			);
			return false;
		});
	};
	
	
	
	var backlinkHandlerInit = function(container) {
		$('.cma-backlink', container).click(function() {
			var link = $(this);
			
			performRequest(container, link.attr('href'), {});
			return false;
			
			var widgetContainer = link.parents('.cma-questions-widget');
			var data = {};
			data.widgetCacheId = container.data('widgetCacheId');
			container.addClass('cma-loading');
			container.append($('<div/>', {"class":"cma-loader"}));
			$.ajax({
				method: "GET",
				url: link.attr('href'),
				data: data,
				success: function(response) {
					var code = $('<div>' + response +'</div>');
					widgetContainer.replaceWith(code.find('#content .cma-questions-widget'));
					container.removeClass('cma-loading');
					initHandlers(container);
				}
			});
			return false;
		});
	};
	
	
	var loadThreadHandlerInit = function(container) {
		$('.cma-thread-title a, .cma-thread-orderby a', container).click(function(e) {
			
			// Allow to use middle-button to open thread in a new tab:
			if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;
			
			e.preventDefault();
			e.stopPropagation();
			
			var link = $(this);
			var data = {};
			var commentsContainer = link.parents('#comments');
			if (commentsContainer.length > 0) {
				data.post_id = commentsContainer.data('postId');
			}
			loadThread(container, link.attr('href'), data);
			return false; // prevent default
		});
	};
	
	
	var loadThread = function(container, url, data) {
		data.widgetCacheId = container.data('widgetCacheId');
		data.backlink = container.find('.cma-questions-widget, .cma-question-table').data('backlink');
		container.addClass('cma-loading');
		container.append($('<div/>', {"class":"cma-loader"}));
		$.ajax({
			method: "GET",
			url: url,
			data: data,
			success: function(response) {
				container.html(response);
				initHandlers(container);
				container.removeClass('cma-loading');
				$('html, body').animate({
					scrollTop: container.offset().top
				}, 1000);
			}
		});
	};
	
	
	var postQuestionHandlerInit = function(container) {
		$('form.cma-thread-add', container).submit(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			var form = $(this);
			var url = form.attr('action') + '?widgetCacheId=' + container.data('widgetCacheId');
			container.addClass('cma-loading');
			var loader = $('<div/>', {"class":"cma-loader"});
			container.append(loader);
			setTimeout(function() { // Added timeout because the rich-editor content file is empty before submitting.
				$.post(url, form.serialize(), function(response) {
					loader.remove();
					container.removeClass('cma-loading');
					$().toastmessage(response.success ? 'showSuccessToast' : 'showErrorToast', response.messages.join("<br />"));
					if (response.success) {
						performRequest(container, container.find('.cma-questions-widget').data('permalink'), {});
					}
				});
			} , 100);
		});
	};
	
	
	var deleteQuestionHandlerInit = function(container) {
		$('.cma-thread-delete-link', container).click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			if (confirm(CMA_Variables.confirmThreadDelete)) {
				performRequest(container, this.href, {});
			}
		});
	};
	
	
	var editQuestionHandlerInit = function(container) {
		$('.cma-question-edit-link', container).click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			
			var dialog = $('<div/>', {
			    id: 'cma-edit-question-modal',
			    style: 'display:none'
			}).appendTo('body')
			.dialog({
			      autoOpen: true,
			      minHeight: 300,
			      width: "50%",
			      modal: true,
			});
			
			// Append loader
			$('<div/>', {
			    'class': 'loader',
			}).appendTo(dialog);
			
			$.ajax({
				url: this.href,
				success: function(response) {
					var doc = $(response);
					dialog.html(doc.find('.cma-question-form-container').first());
					var header = dialog.find('form h3');
					dialog.dialog('option', 'title', header.text());
					header.remove();
					CMA_script_init($);
					initTinyMCE(dialog);
					CMA_tags_init($);
					dialog.find('form').submit(function(ev) {
						ev.preventDefault();
						ev.stopPropagation();
						var form = $(this);
						var url = form.attr('action') + '&widgetCacheId=' + container.data('widgetCacheId');
						dialog.addClass('cma-loading');
						var loader = $('<div/>', {"class":"cma-loader"});
						dialog.append(loader);
						setTimeout(function() { // Added timeout because the rich-editor content file is empty before submitting.
							$.post(url, form.serialize(), function(response) {
								loader.remove();
								$().toastmessage(response.success ? 'showSuccessToast' : 'showErrorToast', response.messages.join("<br />"));
								if (response.success) {
									dialog.dialog('close');
									dialog.html('');
									dialog.removeClass('cma-loading');
//									performRequest(container, container.find('.cma-question-table').data('permalink'), {});
									loadThread(container, container.find('.cma-question-table').data('permalink'), {});
								}
							});
						} , 100);
					});
				}
			});
			
		});
	};
	
	
	var postAnswerHandlerInit = function(container) {
		$('.cma-answer-form-container form', container).submit(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			var form = $(this);
			form.addClass('.cma-loading');
			form.append($('<div/>', {'class':'cma-loader'}));
			var url = form.attr('action') + '?widgetCacheId=' + container.data('widgetCacheId')
				+'&backlink=' + encodeURIComponent(container.find('.cma-question-table').data('backlink'));
			$.post(url, form.serialize(), function(response) {
//				response = $(response);
//				var content = response.find('article.cma_thread, #content .cma-questions-widget, #content .cma-thread-wrapper');
				container.html(response);
				initHandlers(container);
				form.removeClass('cma-loading');
				$('html,body').animate({scrollTop: container.find('.cma-messages').offset().top - 100});
			});
		});
	};
	
	
	var categoryHandlerInit = function(container) {
		$('a.cma-category-link, .cma-breadcrumbs a:first-of-type', container).click(function(e) {
				
			// Allow to use middle-button to open thread in a new tab:
			if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;
			
			var link = $(this);
			var data = {widgetCacheId: container.data('widgetCacheId')};
			performRequest(
				container,
				this.href,
				data
			);
			
			return false;
			
		});
	};
	
	
	var tagsHandlerInit = function(container) {
		$('.cma-tags-list a', container).click(function(e) {

			// Allow to use middle-button to open thread in a new tab:
			if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;
			
			var link = $(this);
			var data = {
				widgetCacheId: container.data('widgetCacheId'),
				backlink: container.find('.cma-questions-widget, .cma-question-table').data('backlink')
			};
			performRequest(
				container,
				this.href,
				data
			);
			
			return false;
			
		});
	};
	
	
	$('.cma-widget-ajax').each(function() {
		initHandlers($(this));
	});
	
	
});


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
				if (removeOther && $('body').hasClass('single-cma_thread')) location.reload();
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
	
	
	CMA_Utils.addSingleHandler('newThreadsFollowHandler', '.cma-follow-new-threads-link, .cma-unfollow-new-threads-link', 'click', function() {
		var link = $(this);
		var data = {'action': 'cma-follow-new-threads', 'nonce' : link.data('nonce')};
		$.post(CMA_Variables.ajaxUrl, data, function (response) {
			if (response.success) {
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
				container.removeClass('cma-loading');
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


jQuery(function($) {
	

	$('.suggest-user').each(function() {

		var removeHandler = function() {
			$(this).parents('li').remove();
			return false;
		};
		
		var obj = $(this);
		var input = obj.find('input[type=text]');
		var addHandler = function() {
			var login = input.val();
			if (obj.find('li[data-user-login="'+ login +'"]').length > 0) return;
			$.ajax({
				url: location.pathname + '?page=CMA_settings_search_users_get_item&fieldName='+ obj.data('fieldName') +'&q=' + login,
				success: function(html) {
					if (html.length > 0) {
						obj.find('ul').append(html);
						obj.find('.btn-list-remove').click(removeHandler);
					}
				},
				responseType: "html"
			});
			return false;
		};
		var inputFocus = false;
		input.suggest(location.pathname + '?page=CMA_settings_search_users', {delay: 500});
		input.focus(function() { inputFocus = true; });
		input.blur(function() { inputFocus = false; });
		input.parents('form').submit(function() {
			if (inputFocus) {
				addHandler();
				return false;
			}
		});
		obj.find('input[type=button]').click(addHandler);
		obj.find('.btn-list-remove').click(removeHandler);
		
	});
	
});


CMA_tags_init = function($) {
	$('.cma-form-tags').each(function() {
		
		var container = $(this);
		var list = $(document.createElement('ul')).addClass('cma-tags-list');
		container.append(list);
		var hidden = container.find('input[type=hidden]');
		var input = container.find('input[type=text]');
		var form = input.parents('form');
		var addButton = container.find('input[type=button]');
		
		var updateTagsHidden = function() {
			hidden.val('');
			list.find('li').each(function() {
				var val = hidden.val();
				hidden.val((val.length > 0 ? val + "," : "") + $(this).find('span').text());
			});
		};
		
		var addTagItem = function(tag) {
			var item = $(document.createElement('li')).append($(document.createElement('span')).text(tag));
			var remove = $(document.createElement('a')).addClass('remove').html('&times;');
			remove.click(function() {
				$(this).parents('li').remove();
				updateTagsHidden();
			});
			item.append(remove);
			list.append(item);
		};
		
		// Add current tags
		var tags = container.find('input[name=thread_tags]').val().split(',');
		for (var i=0; i<tags.length; i++) {
			var tag = tags[i].replace(/^\s+/, '').replace(/\s+$/, '');
			if (tag.length > 0) addTagItem(tag);
		}
		
		
		// --------------------------------------------------------------------------------------------------
		// Add tags from input
		

		var addTags = function(tags) {
			tags = tags.split(',');
			for (var i=0; i<tags.length; i++) {
				tag = tags[i].replace(/^\s+/, '').replace(/\s+$/, '');
				existingTags = hidden.val().split(',');
				if (tag.length > 0 && existingTags.indexOf(tag) == -1) {
					addTagItem(tag);
				}
			}
			updateTagsHidden();
		};
		
		var addTagFlag = false;
		input.focus(function() {
			addTagFlag = true;
		});
		input.blur(function() {
			addTagFlag = false;
		});
		
		form.submit(function(e) {
			if (addTagFlag) {
				e.preventDefault();
				e.stopPropagation();
				addTags(input.val());
				input.val('');
			}
		});
		
		addButton.click(function() {
			addTags(input.val());
			input.val('');
		});
		
		
		// --------------------------------------------------------------------------------------------------
		// Autocomplete
		
		input.suggest('/wp-admin/admin-ajax.php?action=cma_tag_autocomplete&tax=post_tag', {delay: 500});
		
	});
};

