<button type="button" id="button-blankt" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary btn-lg btn-block"><?php echo $button_blankt_designer; ?></button>

<script type="text/javascript"><!--
$(document).ready(function () {
	if ($('#button-blankt').length) {
		$('[name="quantity"]').hide()
		$('[for="input-quantity"]').hide()
	}
})
$('#button-blankt').on('click', function() {

	if($('[name="productSerial"]').length) {
		$('[name="productSerial"]').remove()
	}
	$('#product').append('<input type="hidden" name="productSerial" value="' + getURLVar('productSerial') + '" />');

	$.ajax({
		url: 'index.php?route=product/blankt_button/add',
		type: 'post',
		data: $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-blankt').button('loading');
		},
		complete: function() {
			$('#button-blankt').button('reset');
		},
		success: function(json) {
			$('.alert, .text-danger').remove();
			$('.form-group').removeClass('has-error');

			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {
						var element = $('#input-option' + i.replace('_', '-'));

						if (element.parent().hasClass('input-group')) {
							element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
						} else {
							element.after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
						}
					}
				}

				if (json['error']['recurring']) {
					$('select[name=\'recurring_id\']').after('<div class="text-danger">' + json['error']['recurring'] + '</div>');
				}

				// Highlight any found errors
				$('.text-danger').parent().addClass('has-error');
			}

			if (json['success']) {
				$('html, body').animate({ scrollTop: 0 }, 'slow');
				window.location.href = json['redirect_url']
			}
		}
	});
});
//--></script>