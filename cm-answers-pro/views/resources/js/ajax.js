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
								dialog.removeClass('cma-loading');
								$().toastmessage(response.success ? 'showSuccessToast' : 'showErrorToast', response.messages.join("<br />"));
								if (response.success) {
									dialog.dialog('close');
									dialog.html('');
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
				form.removeClass('.cma-loading');
				initHandlers(container);
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
