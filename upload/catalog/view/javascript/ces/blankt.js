$(document).ready(function () {
	const old_func = cart.add
	cart.add = function (product_id, quantity) {
		console.log('Blankt: Extending cart.add')
		if (window.blankt_products.includes(String(product_id))) {
			return blanktAdd(product_id, quantity)
		}
		return old_func(product_id, quantity)
	}

	const old_func_remove = cart.remove
	cart.remove = function (cart_key) {
		console.log('Blankt: Extending cart.remove')
		const blankt_product_serial = $('[name="quantity[' + cart_key + ']"]').parent().parent().parent().find('[data-blankt_product_serial]').data('blankt_product_serial')
		if (blankt_product_serial) {
			swal({
				title: $('#blankt-cart-messages').data('title'),
				text: $('#blankt-cart-messages').data('text'),
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
				.then((willDelete) => {
					if (willDelete) {
						blanktDelete(blankt_product_serial, cart_key)
					}
				});
		} else {
			return old_func_remove(cart_key)
		}
	}

	$(document).on('click', '.button_blankt_edit_url', function () {
		const cart_key = $(this).data('cart_key')
		const blankt_product_serial = $(this).data('blankt_product_serial')

		blanktEdit(cart_key, blankt_product_serial)
	})

	$(document).on('click', '.blankt_terms_and_condtions', function () {
		const html = $('#blankt_temp_terms_and_conditions').html()
		swal({
			className: 'blankt_temp_terms_and_conditions_modal',
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
				document.querySelector('.blankt_temp_terms_and_conditions_modal').scrollIntoView('top')
			});
	})

	function blanktDelete(blankt_product_serial, cart_key, is_update_cart_page = false) {
		$.ajax({
			url: 'index.php?route=product/blankt_button/delete',
			type: 'post',
			data: 'blankt_product_serial=' + blankt_product_serial,
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

	function blanktEdit(cart_key, blankt_product_serial) {
		$.ajax({
			url: 'index.php?route=product/blankt_button/edit',
			type: 'post',
			data: 'cart_key=' + cart_key + '&productSerial=' + blankt_product_serial + '&blankt_product_page_redirect=1',
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

	function blanktAdd(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=product/blankt_button/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof (quantity) != 'undefined' ? quantity : 1) + '&blankt_product_page_redirect=1',
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

					blanktCopyrightNotice()
				}
			}
		});
	}

	// cart page only
	blanktCopyrightNotice()

	$(document).on('click', '.button_blankt_edit_option_url', function () {
		const cart_key = $(this).data('cart_key')
		const blankt_product_serial = $(this).data('blankt_product_serial')
		blanktEditProductOptions(cart_key, blankt_product_serial)

		const html = ''
		swal({
			className: 'blankt_temp_option_modal',
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

	function blanktEditProductOptions(cart_key, blankt_product_serial) {
		$.ajax({
			url: 'index.php?route=product/blankt_button/cartProductPageAjax',
			type: 'post',
			data: 'cart_key=' + cart_key + '&productSerial=' + blankt_product_serial,
			dataType: 'json',
			beforeSend: function () {
				$('#cart > button').button('loading');
			},
			success: function (json) {
				$('.alert, .text-danger').remove();

				$('#cart > button').button('reset');

				const all_products = json.all_products
				const html = json.blankt_cart_product_page_html + json.blankt_add_product_option_html
				swal({
					closeOnClickOutside: false,
					closeOnEsc: false,
					// allowOutsideClick: false,
					className: 'blankt_temp_option_modal',
					content: {
						element: 'div',
						attributes: {
							innerHTML: html,
						},
					},
					buttons: false,					 
					dangerMode: false,
				});

				$('.swal-blankt-confirm-button').data('cart_key', cart_key)
				$('.swal-blankt-confirm-button').data('blankt_product_serial', blankt_product_serial)

				window.all_products = all_products		

				for (let i = 0; i < all_products.length; i++) {
					const quantity = all_products[i].quantity
					let listing_index = i

					$('#button-blankt-add-product-option').trigger('click')
					listing_index = $('#table-blankt_custom_option tbody tr:last-child').data('index')
					if ($(`[name="blankt_custom_option_quantity[${listing_index}]"]`).length) {
						$(`[name="blankt_custom_option_quantity[${listing_index}]"]`).val(quantity)
					}

					for (let option_id in all_products[i]['options']) {
						const option_value = all_products[i]['options'][option_id]
						if ($(`[name="blankt_custom_option[${listing_index}][${option_id}]"]`).length) {
							$(`[name="blankt_custom_option[${listing_index}][${option_id}]"]`).val(option_value)
						}
					}
				}
				$('.main-blankt-options-template').hide()
			}
		});
	}

	// blankt add more button function
	let quantityBoxId = 1

	function createBlanktTemplate() {
		const Header1 = document.querySelector('.main-blankt-options-template select').previousElementSibling.innerHTML
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
		return html
	}

	$(document).on('click', '.blankt_custom_option-delete', function () {
		$(this).parents('tr').remove()

		if (!$('#table-blankt_custom_option tbody tr').length) {
			$('#table-blankt_custom_option').hide()
		}
	}).on('click', '#button-blankt-add-product-option', function () {
		if (!$('#table-blankt_custom_option').length) {
			$('#button-blankt-add-product-option').parent().before(createBlanktTemplate())
		}

		$('#table-blankt_custom_option').show()
		
		let html1 = document.querySelector('#product .form-group select').outerHTML
		html1 = html1.replace(/name="option/g, 'name="blankt_custom_option[' + quantityBoxId + ']')
			.replace(/-option/g, '-blankt_custom_option' + quantityBoxId + '-')
			.replace(/class="/g, ' class="blankt_custom_option_select ')

		let html2 = document.querySelector('#product #input-quantity').outerHTML
		html2 = html2.replace(/name="quantity"/g, 'name="blankt_custom_option_quantity[' + quantityBoxId + ']"')
		html2 = html2.replace(/input-quantity/g, 'input-quantity-' + quantityBoxId)
		$('.product-action').css('position', 'relative')

		$('#table-blankt_custom_option tbody').append(`<tr data-index="${quantityBoxId}">
			<td>${html1}</td>
			<td style="width: 100px">${html2}</td>
			<td><button class="btn btn-danger blankt_custom_option-delete"><i class="fa fa-trash-o"></i></button></td>
		</tr>`)
		$('#input-quantity-' + quantityBoxId).attr('type', 'number')
		$('#input-quantity-' + quantityBoxId).attr('min', $('#input-quantity-' + quantityBoxId).val())

		++quantityBoxId
	})
})

function blanktCopyrightNotice() {
	if ($('#blankt_copyright_notice').length) {
		if ($('#blankt_copyright_notice').is(':checked')) {
			$('#blankt-checkout').removeAttr('disabled')
		} else {
			$('#blankt-checkout').attr('disabled', 'disabled')
		}
	} else {
		if ($('#blankt-checkout').length) {
			$('#blankt-checkout').removeAttr('disabled')
		}
	}
}
$(document).on('change', '#blankt_copyright_notice', function () {
	blanktCopyrightNotice()
}).on('click', '.swal-blankt-confirm-button', function () {
	const blankt_product_serial = $(this).data('blankt_product_serial')
	const cart_key = $(this).data('cart_key')

	if (!$('.blankt_custom_option_select').length && blankt_product_serial) {
		swal({
			title: $('#blankt-cart-messages').data('title'),
			text: $('#blankt-cart-messages').data('text'),
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
			.then((willDelete) => {
				if (willDelete) {					
					blanktDeleteAllBlanktSerialProducts(blankt_product_serial)
				}
			});
	} else {
		blanktAddToCart(cart_key, blankt_product_serial)
	}

}).on('click', '.blankt_modal_close_icon', function () {
	swal.close()
}).on('click', '[data-has_blankt_image="1"]', function (e) {
	e.preventDefault()	
	const src = $(this).data('src')
	$('#myBlanktImage').modal('show')
	$('#myBlanktImage').find('img').attr('src', src)
})

function blanktDeleteAllBlanktSerialProducts(productSerial) {
	$.ajax({
		url: 'index.php?route=checkout/cart/removeAllBlankt',
		type: 'post',
		data: {
			productSerial: productSerial
		},
		dataType: 'json',
		beforeSend: function () {
			$('.swal-blankt-confirm-button').button('loading');
		},
		complete: function () {
			$('.swal-blankt-confirm-button').button('reset');
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

function blanktAddToCart(cart_key, blankt_product_serial) {
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
	$('#product').append('<input type="hidden" name="productSerial" value="' + blankt_product_serial + '" />');
	$('#product').append('<input type="hidden" name="cart_key_delete" value="' + cart_key + '" />');

	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: $('#product input[type=\'number\'], #product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea, #table-blankt_custom_option select'),
		dataType: 'json',
		beforeSend: function () {
			$('.swal-blankt-confirm-button').button('loading');
		},
		complete: function () {
			$('.swal-blankt-confirm-button').button('reset');
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
					if ($('link[href$="default/stylesheet/ces/blankt.css"]').length) {
						$('#cart-total').text($('#cart-total').text())
					} else {
						$('#cart-total').text(parseInt($('#cart-total').text()))
					}

					blanktCopyrightNotice()
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
