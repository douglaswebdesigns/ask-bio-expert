<?php if (!empty($term)) $template = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cma_custom_fields">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template = <<<HTML
	<div class="form-field">
		<label for="cma_custom_fields">%s</label>
		%s
	</div>
HTML;


$items = '';
for ($i=0; $i<CMA_Category::CUSTOM_FIELDS_NUMBER; $i++) {
	$items .= sprintf('<label>Custom field %d: <input type="text" name="%s[%d]" value="%s" /></label><br />',
				$i+1, CMA_CategoryController::FIELD_CUSTOM_FIELDS, $i, $fields[$i]);
}


$content = <<<HTML
	<div class="cma_custom_fields">
		%s
		<input type="hidden" name="%s" value="%s" />
	</div>
HTML;

$content = sprintf($content,
	$items,
	CMA_CategoryController::NONCE_CUSTOM_FIELDS,
	wp_create_nonce(CMA_CategoryController::NONCE_CUSTOM_FIELDS)
);

printf($template, CMA::__('Custom fields for new questions'), $content);
