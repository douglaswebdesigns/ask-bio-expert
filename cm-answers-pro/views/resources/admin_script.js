(function($) {

	$('.cma_tables a.delete').on('click', function(e) {
		var $this;
		$this = $(this);
		if (confirm('Are you sure? You will lose all your Questions, Answers and Settings!')) {
			return true;
		} else {
			e.preventDefault();
			return false;
		}
	});
	
	$('#cma_categorychecklist, #cma_categorychecklist-pop').each(function() {
		var container = $(this);
		container.find('input:checkbox').change(function() {
			var obj = $(this);
			if (obj.is(':checked')) {
//				console.log($(this).attr('value'));
				var other = container.find('input:checkbox:checked[value!="'+ $(this).attr('value') +'"]');
//				console.log(other);
				other.click();
			}
		});
	});
	
	
	$('.cma-user-related-questions .cma-remove-button').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		$(this).parents('tr').first().remove();
	});
	

})(jQuery);
