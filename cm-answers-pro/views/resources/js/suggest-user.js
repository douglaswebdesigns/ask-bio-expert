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
