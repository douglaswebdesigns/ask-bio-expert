CMA_tags_init = function($) {
	$('.cma-form-tags:not(.cma-tags-handler)').each(function() {
		
		var container = $(this);
		container.addClass('cma-tags-handler');
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
