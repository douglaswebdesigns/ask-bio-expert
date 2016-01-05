<?php if (!empty($term)) $template = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cma_access">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template = <<<HTML
	<div class="form-field">
		<label for="cma_access">%s</label>
		%s
	</div>
HTML;


$options = '';
foreach ($roles as $roleName => $roleInfo):
	$options .= sprintf('<option value="%s"%s>%s</option>',
		esc_attr($roleName),
		(in_array($roleName, $categoryRoles) ? ' selected="selected"' : ''),
		esc_html($roleName)
	);
endforeach;

$label = CMA_Settings::__('Restrict access to chosen roles');
$content = <<<HTML
	<fieldset class="cma-access-restriction-enable">
		<label><input type="radio" name="cma_roles_enable" value="0"%s> %s</label>
		<label><input type="radio" name="cma_roles_enable" value="1"%s> %s</label>
	</fieldset>
	<div class="roles">
		<select name="cma_roles[]" multiple="multiple" style="min-width:200px">%s</select>
		<p class="description">%s</p>
	</div>
HTML;


printf($template, $label, sprintf($content,
	checked(0, intval(!empty($categoryRoles)), false),
	CMA_Settings::__('No'),
	checked(1, intval(!empty($categoryRoles)), false),
	CMA_Settings::__('Yes'),
	$options,
	CMA_Settings::__('Choose roles which will have access to the category. If no roles selected, then every user can view it.')
));

?>

<style type="text/css">
.cma-access-restriction-enable input {width: auto !important;;}
</style>

<script type="text/javascript">
jQuery(function($) {

	function rolesVisible(obj) {
		if (obj.val() == '1') {
			obj.parents('.form-field').find('.roles').show();
		} else {
			obj.parents('.form-field').find('.roles').hide();
		}
	}

	rolesVisible($('input[name=cma_roles_enable]:checked'));
	$('input[name=cma_roles_enable]').change(function() {
		rolesVisible($(this));
	});
	
});
</script>