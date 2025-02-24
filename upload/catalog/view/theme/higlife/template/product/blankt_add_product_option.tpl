<button type="button" id="button-blankt-add-product-option" data-loading-text="<?php echo $text_loading; ?>" class="btn-success swal-button"><?php echo $button_blankt_add_product_option; ?></button>

<script type="text/javascript"><!--
const all_product_options = JSON.parse("{{ all_product_options|json_encode }}")
window.all_product_options = all_product_options
let quantityBoxId = 1
let createBlanktTemplateDone = 0

function createBlanktTemplate() {
	const Header1 = document.querySelector('[for="input-option543"]').innerHTML
	const Header2 = document.querySelector('[for="input-quantity"]').innerHTML
	const html = `<table class="table table-bordered table-hover" id="table-blankt_custom_option">
		<thead>
			<th>${Header1}</th>
			<th>${Header2}</th>
			<th></th>
		</thead>
		<tbody>
		</tbody>
	</table>`
	console.log(html)
	return html
}
$(document).on('click', '.blankt_custom_option-delete', function() {
	$(this).parents('tr').remove()

	if (!$('#table-blankt_custom_option tbody tr').length) {
		$('#table-blankt_custom_option').hide()
	}
})

$('#button-blankt-add-product-option').on('click', function() {
	if (!createBlanktTemplateDone) {
		createBlanktTemplateDone = 1
		$('#button-blankt-add-product-option').parent().before(createBlanktTemplate())
	}

	$('#table-blankt_custom_option').show()
	/*
	const html3 = document.querySelector('[for="input-quantity"]').outerHTML
	$('#button-blankt-add-product-option').parent().before(`<div>${html1}</div>`)

	$($('[id*="input-blankt_custom_option"]')[$('[id*="input-blankt_custom_option"]').length - 1]).after(`<div style="margin-top: 5px">${html3} ${html2}</div>`)
	$('#input-quantity-' + quantityBoxId).val('')
	*/

	let html1 = document.querySelector('#product .form-group.required select').outerHTML
	html1 = html1.replace(/name="option/g, 'name="blankt_custom_option[' + quantityBoxId + ']')
	.replace(/-option/g, '-blankt_custom_option' + quantityBoxId + '-')

	let html2 = document.querySelector('#product #input-quantity').outerHTML
	html2 = html2.replace(/name="quantity"/g, 'name="blankt_custom_option_quantity[' + quantityBoxId + ']"')
	html2 = html2.replace(/input-quantity/g, 'input-quantity-' + quantityBoxId)
	$('.product-action').css('position', 'relative')

	$('#table-blankt_custom_option tbody').append(`<tr>
		<td>${html1}</td>
		<td>${html2}</td>
		<td><button class="btn btn-danger blankt_custom_option-delete"><i class="fa fa-trash-o"></i></button></td>
	</tr>`)
	$('#input-quantity-' + quantityBoxId).val('')

	++quantityBoxId
})
//--></script>