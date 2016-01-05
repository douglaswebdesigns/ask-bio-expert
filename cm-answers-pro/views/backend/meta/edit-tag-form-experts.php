<?php if (!empty($term)) $template = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cma_experts">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template = <<<HTML
	<div class="form-field">
		<label for="cma_experts">%s</label>
		%s
	</div>
HTML;


$listItem =  <<<HTML
<li data-user-id="%d" data-user-login="%s">
	<a href="%s">%s</a> <a href="" class="btn-list-remove">&times;</a>
	<input type="hidden" name="%s[]" value="%d" />
</li>
HTML;

$list = '';
foreach ($experts as $userId) {
	if ($user = get_userdata($userId)) {
		$list .= sprintf($listItem,
			intval($userId),
			$user->user_login,
			esc_attr(get_edit_user_link($userId)),
			esc_html($user->user_login),
			'cma_experts',
			intval($userId)
		);
	}
}


$content = <<<HTML
	<div class="cma_experts">
		<div class="suggest-user" data-field-name="%s">
			<ul>%s</ul>
			<div><span>%s:</span><input type="text" /> <input type="button" value="%s" /></div>
			<input type="hidden" name="cma_experts_nonce" value="%s" />
		</div>
		<p class="description">%s</p>
	</div>
HTML;

$content = sprintf($content,
	'cma_experts',
	$list,
	CMA::__('Find user'),
	esc_attr(CMA::__('Add')),
	wp_create_nonce('cma_experts_nonce'),
	CMA::__('Experts will receive notifications about new questions.')
);

printf($template, CMA::__('Select experts'), $content);

