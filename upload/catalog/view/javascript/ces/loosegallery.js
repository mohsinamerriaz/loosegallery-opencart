$(document).ready(function () {
	const old_func = cart.add
	cart.add = function (product_id, quantity) {
		console.log('loosegallery: Extending cart.add')
		if (window.loosegallery_products.includes(String(product_id))) {
			return loosegalleryAdd(product_id, quantity)
		}
		return old_func(product_id, quantity)
	}

	const old_func_remove = cart.remove
	cart.remove = function (cart_key) {
		console.log('loosegallery: Extending cart.remove')
		const loosegallery_product_serial = $('[name="quantity[' + cart_key + ']"]').parent().parent().parent().find('[data-loosegallery_product_serial]').data('loosegallery_product_serial')
		if (loosegallery_product_serial) {
			swal({
				title: $('#loosegallery-cart-messages').data('title'),
				text: $('#loosegallery-cart-messages').data('text'),
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
				.then((willDelete) => {
					if (willDelete) {
						loosegalleryDelete(loosegallery_product_serial, cart_key)
					}
				});
		} else {
			return old_func_remove(cart_key)
		}
	}

	$(document).on('click', '.button_loosegallery_edit_url', function () {
		const cart_key = $(this).data('cart_key')
		const loosegallery_product_serial = $(this).data('loosegallery_product_serial')

		loosegalleryEdit(cart_key, loosegallery_product_serial)
	})

	$(document).on('click', '.loosegallery_terms_and_condtions', function () {
		const html = $('#loosegallery_temp_terms_and_conditions').html()
		swal({
			className: 'loosegallery_temp_terms_and_conditions_modal',
			content: {
				element: 'div',
				attributes: {
					innerHTML: html,
				},
			},
			buttons: false,
			dangerMode: false,
		})
			.then(() => {
				document.querySelector('.loosegallery_temp_terms_and_conditions_modal').scrollIntoView('top')
			});
	})

	function loosegalleryDelete(loosegallery_product_serial, cart_key, is_update_cart_page = false) {
		$.ajax({
			url: 'index.php?route=product/loosegallery_button/delete',
			type: 'post',
			data: 'loosegallery_product_serial=' + loosegallery_product_serial,
			dataType: 'json',
			beforeSend: function () {
				$('#cart > button').button('loading');
			},
			success: function (json) {
				$('.alert, .text-danger').remove();

				$('#cart > button').button('reset');

				if (json['success']) {
					old_func_remove(cart_key)
				}
			}
		});
	}

	function loosegalleryEdit(cart_key, loosegallery_product_serial) {
		$.ajax({
			url: 'index.php?route=product/loosegallery_button/edit',
			type: 'post',
			data: 'cart_key=' + cart_key + '&productSerial=' + loosegallery_product_serial + '&loosegallery_product_page_redirect=1',
			dataType: 'json',
			beforeSend: function () {
				$('#cart > button').button('loading');
			},
			success: function (json) {
				$('.alert, .text-danger').remove();

				$('#cart > button').button('reset');

				if (json['redirect_url']) {
					location = json['redirect_url']
					setTimeout(function(){document.location.href = json['redirect_url'];} ,100);
					return false;
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-info"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#cart-total').html(json['total']);

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			}
		});
	}

	function loosegalleryAdd(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=product/loosegallery_button/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof (quantity) != 'undefined' ? quantity : 1) + '&loosegallery_product_page_redirect=1',
			dataType: 'json',
			beforeSend: function () {
				$('#cart > button').button('loading');
			},
			success: function (json) {
				$('.alert, .text-danger').remove();

				$('#cart > button').button('reset');

				if (json['redirect_url']) {
					location = json['redirect_url']
					setTimeout(function(){document.location.href = json['redirect_url'];} ,100);

					return false;
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-info"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#cart-total').html(json['total']);

					$('html, body').animate({ scrollTop: 0 }, 'slow');

					$('#cart > ul').load('index.php?route=common/cart/info ul li');

					$('#cart-total').text(parseInt($('#cart-total').text()))

					loosegalleryCopyrightNotice()
				}
			}
		});
	}

	// cart page only
	loosegalleryCopyrightNotice()

	$(document).on('click', '.button_loosegallery_edit_option_url', function () {
		const cart_key = $(this).data('cart_key')
		const loosegallery_product_serial = $(this).data('loosegallery_product_serial')
		loosegalleryEditProductOptions(cart_key, loosegallery_product_serial)

		const html = ''
		swal({
			className: 'loosegallery_temp_option_modal',
			content: {
				element: 'div',
				attributes: {
					innerHTML: html,
				},
			},
			buttons: true,
			dangerMode: false,
		})
	})

	function loosegalleryEditProductOptions(cart_key, loosegallery_product_serial) {
		$.ajax({
			url: 'index.php?route=product/loosegallery_button/cartProductPageAjax',
			type: 'post',
			data: 'cart_key=' + cart_key + '&productSerial=' + loosegallery_product_serial,
			dataType: 'json',
			beforeSend: function () {
				$('#cart > button').button('loading');
			},
			success: function (json) {
				$('.alert, .text-danger').remove();

				$('#cart > button').button('reset');

				const all_products = json.all_products
				const html = json.loosegallery_cart_product_page_html + json.loosegallery_add_product_option_html
				swal({
					closeOnClickOutside: false,
					closeOnEsc: false,
					// allowOutsideClick: false,
					className: 'loosegallery_temp_option_modal',
					content: {
						element: 'div',
						attributes: {
							innerHTML: html,
						},
					},
					buttons: false,					 
					dangerMode: false,
				});

				$('.swal-loosegallery-confirm-button').data('cart_key', cart_key)
				$('.swal-loosegallery-confirm-button').data('loosegallery_product_serial', loosegallery_product_serial)

				window.all_products = all_products		

				for (let i = 0; i < all_products.length; i++) {
					const quantity = all_products[i].quantity
					let listing_index = i

					$('#button-loosegallery-add-product-option').trigger('click')
					listing_index = $('#table-loosegallery_custom_option tbody tr:last-child').data('index')
					if ($(`[name="loosegallery_custom_option_quantity[${listing_index}]"]`).length) {
						$(`[name="loosegallery_custom_option_quantity[${listing_index}]"]`).val(quantity)
					}

					for (let option_id in all_products[i]['options']) {
						const option_value = all_products[i]['options'][option_id]
						if ($(`[name="loosegallery_custom_option[${listing_index}][${option_id}]"]`).length) {
							$(`[name="loosegallery_custom_option[${listing_index}][${option_id}]"]`).val(option_value)
						}
					}
				}
				$('.main-loosegallery-options-template').hide()
			}
		});
	}

	// loosegallery add more button function
	let quantityBoxId = 1

	function createloosegalleryTemplate() {
		const Header1 = document.querySelector('.main-loosegallery-options-template select').previousElementSibling.innerHTML
		const Header2 = document.querySelector('[for="input-quantity"]').innerHTML
		const html = `<table class="table table-bordered table-hover" id="table-loosegallery_custom_option">
		<thead>
			<th>${Header1}</th>
			<th>${Header2}</th>
			<th></th>
		</thead>
		<tbody>
		</tbody>
	</table>`
		return html
	}

	$(document).on('click', '.loosegallery_custom_option-delete', function () {
		$(this).parents('tr').remove()

		if (!$('#table-loosegallery_custom_option tbody tr').length) {
			$('#table-loosegallery_custom_option').hide()
		}
	}).on('click', '#button-loosegallery-add-product-option', function () {
		if (!$('#table-loosegallery_custom_option').length) {
			$('#button-loosegallery-add-product-option').parent().before(createloosegalleryTemplate())
		}

		$('#table-loosegallery_custom_option').show()
		
		let html1 = document.querySelector('#product .form-group select').outerHTML
		html1 = html1.replace(/name="option/g, 'name="loosegallery_custom_option[' + quantityBoxId + ']')
			.replace(/-option/g, '-loosegallery_custom_option' + quantityBoxId + '-')
			.replace(/class="/g, ' class="loosegallery_custom_option_select ')

		let html2 = document.querySelector('#product #input-quantity').outerHTML
		html2 = html2.replace(/name="quantity"/g, 'name="loosegallery_custom_option_quantity[' + quantityBoxId + ']"')
		html2 = html2.replace(/input-quantity/g, 'input-quantity-' + quantityBoxId)
		$('.product-action').css('position', 'relative')

		$('#table-loosegallery_custom_option tbody').append(`<tr data-index="${quantityBoxId}">
			<td>${html1}</td>
			<td style="width: 100px">${html2}</td>
			<td><button class="btn btn-danger loosegallery_custom_option-delete"><i class="fa fa-trash-o"></i></button></td>
		</tr>`)
		$('#input-quantity-' + quantityBoxId).attr('type', 'number')
		$('#input-quantity-' + quantityBoxId).attr('min', $('#input-quantity-' + quantityBoxId).val())

		++quantityBoxId
	})
})

function loosegalleryCopyrightNotice() {
	if ($('#loosegallery_copyright_notice').length) {
		if ($('#loosegallery_copyright_notice').is(':checked')) {
			$('#loosegallery-checkout').removeAttr('disabled')
		} else {
			$('#loosegallery-checkout').attr('disabled', 'disabled')
		}
	} else {
		if ($('#loosegallery-checkout').length) {
			$('#loosegallery-checkout').removeAttr('disabled')
		}
	}
}
$(document).on('change', '#loosegallery_copyright_notice', function () {
	loosegalleryCopyrightNotice()
}).on('click', '.swal-loosegallery-confirm-button', function () {
	const loosegallery_product_serial = $(this).data('loosegallery_product_serial')
	const cart_key = $(this).data('cart_key')

	if (!$('.loosegallery_custom_option_select').length && loosegallery_product_serial) {
		swal({
			title: $('#loosegallery-cart-messages').data('title'),
			text: $('#loosegallery-cart-messages').data('text'),
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
			.then((willDelete) => {
				if (willDelete) {					
					loosegalleryDeleteAllloosegallerySerialProducts(loosegallery_product_serial)
				}
			});
	} else {
		loosegalleryAddToCart(cart_key, loosegallery_product_serial)
	}

}).on('click', '.loosegallery_modal_close_icon', function () {
	swal.close()
}).on('click', '[data-has_loosegallery_image="1"]', function (e) {
	e.preventDefault()	
	const src = $(this).data('src')
	$('#myloosegalleryImage').modal('show')
	$('#myloosegalleryImage').find('img').attr('src', src)
})

function loosegalleryDeleteAllloosegallerySerialProducts(productSerial) {
	$.ajax({
		url: 'index.php?route=checkout/cart/removeAllloosegallery',
		type: 'post',
		data: {
			productSerial: productSerial
		},
		dataType: 'json',
		beforeSend: function () {
			$('.swal-loosegallery-confirm-button').button('loading');
		},
		complete: function () {
			$('.swal-loosegallery-confirm-button').button('reset');
		},
		success: function (json) {
			try {
				window.location.reload()
			} catch (error) {
				swal.fire({
					icon: 'error',
					html: '<h5>Error!</h5>'
				});
				return false;
			}
		}
	});
}

function loosegalleryAddToCart(cart_key, loosegallery_product_serial) {
	let valid = true
	$('.swal-modal select').each(function () {
		if (!this.value && $(this).attr('id').indexOf('input-option') === -1) {
			valid = false
		}
	})

	$('.text_select_required_fields').remove()

	if (!valid) {
		alert($('[data-text_select_required_fields]').data('text_select_required_fields'))		
		return true
	}

	if ($('[name="cart_key_delete"]').length) {
		$('[name="cart_key_delete"]').remove()
	}
	$('#product').append('<input type="hidden" name="productSerial" value="' + loosegallery_product_serial + '" />');
	$('#product').append('<input type="hidden" name="cart_key_delete" value="' + cart_key + '" />');

	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: $('#product input[type=\'number\'], #product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea, #table-loosegallery_custom_option select'),
		dataType: 'json',
		beforeSend: function () {
			$('.swal-loosegallery-confirm-button').button('loading');
		},
		complete: function () {
			$('.swal-loosegallery-confirm-button').button('reset');
		},
		success: function (json) {
			try {
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
					swal.close()

					$('html, body').animate({ scrollTop: 0 }, 'slow');

					$('#cart-total').html(json['total']);				
					
					if ($('#content .well').length) {
						$('#content').load('index.php?route=checkout/cart/index #content .well');
					} else {
						$('#content').load('index.php?route=checkout/cart/index #content > *');
					}

					$('#cart > ul').load('index.php?route=common/cart/info ul li');

					// for default theme
					if ($('link[href$="default/stylesheet/ces/loosegallery.css"]').length) {
						$('#cart-total').text($('#cart-total').text())
					} else {
						$('#cart-total').text(parseInt($('#cart-total').text()))
					}

					loosegalleryCopyrightNotice()
				}
			} catch (error) {
				swal.fire({
					icon: 'error',
					html: '<h5>Error!</h5>'
				});
				return false;
			}
		}
	});
}
