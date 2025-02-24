<?php
require_once DIR_APPLICATION . 'controller/product/mobile_detect.php';

class ControllerProductBlanktButton extends Controller
{
	private $mobile_detect;
	private $is_mobile;
	private static $DEBUG = false;
	private $image_extension = '.png';

	public function getImageByProductSerial(string $product_serial)
	{
		$sql = "SELECT image FROM `" . DB_PREFIX . "customer_product_blankt` WHERE product_serial = '" . $product_serial . "' LIMIT 1";

		$row = $this->db->query($sql)->row;

		return empty($row['image']) ? '' : $row['image'];
	}

	// check and return errors if not than add products to cart
	public function setupCartAddPagePostValues($args)
	{

		// if (!empty($args['json'])) {
		// 	return;
		// }

		$productSerial = empty($this->request->post['productSerial']) ? '' : $this->request->post['productSerial'];

		if (!$productSerial) return;

		$option_id = 0;
		// $option_id = '543'; // replace value of this option

		if (!empty($this->request->post['option']) && $productSerial) {
			foreach ($this->request->post['option'] as $key => $value) {
				if ($value == $this->request->post['productSerial']) {
					$option_id = $key;
				}
			}
		}

		if (!empty($productSerial) && !empty($this->request->post['blankt_custom_option'])) {
			$this->request->post['option'] = [];
			foreach ($this->request->post['blankt_custom_option'] as $key => $row) {
				$option = [];
				foreach ($row as $key_in => $value_in) {
					$option[$key_in] = $value_in;
				}
				$this->request->post['quantity'] = !empty($this->request->post['blankt_custom_option_quantity'][$key]) ? (int) $this->request->post['blankt_custom_option_quantity'][$key] : 1;
				if (empty($this->request->post['quantity'])) {
					$this->request->post['quantity'] = 1;
				}

				$option[$option_id] = $productSerial;

				$this->request->post['option'] = $option;
				unset($this->request->post['blankt_custom_option'][$key]);
				break;
			}
		}
	}

	// check and return errors if not than add products to cart
	public function removeFromCartFromCartPage($args)
	{

		if (!empty($args['json'])) {
			return;
		}

		$productSerial = $this->request->post['productSerial'];
		$option_id = 0;
		// $option_id = '543'; // replace value of this option

		if (!empty($this->request->post['option']) && !empty($this->request->post['productSerial'])) {
			foreach ($this->request->post['option'] as $key => $value) {
				if ($value == $this->request->post['productSerial']) {
					$option_id = $key;
				}
				// code...
			}
		}

		if (!$productSerial) return;

		foreach ($this->session->data['cart'] as $cart_old_key => $q) {
			$product = unserialize(base64_decode($cart_old_key));
			foreach ($product['option'] as $option_key => $option_value) {
				if ($option_value == $productSerial) {
					$this->cart->remove($cart_old_key);
				}
			}
		}

		// remove this
		// if (!empty($this->request->post['cart_key_delete'])) {
		// 	$this->cart->remove($this->request->post['cart_key_delete']);
		// }
	}

	// check and return errors if not than add products to cart
	public function addToCartFromCartPage($args)
	{

		if (!empty($args['json'])) {
			return;
		}

		$productSerial = $this->request->post['productSerial'];
		$option_id = 0;
		// $option_id = '543'; // replace value of this option

		if (!empty($this->request->post['option']) && !empty($this->request->post['productSerial'])) {
			foreach ($this->request->post['option'] as $key => $value) {
				if ($value == $this->request->post['productSerial']) {
					$option_id = $key;
				}
				// code...
			}
		}

		if (!$productSerial) return;

		if (!empty($this->request->post['blankt_custom_option'])) {

			$option = $args['option'];
			$recurring_id = $args['recurring_id'];
			$product_id = $this->request->post['product_id'];

			foreach ($this->request->post['blankt_custom_option'] as $index => $blankt_custom_option) {
				if (isset($option[$option_id])) {
					$blankt_custom_option[$option_id] = (string) $option[$option_id];
				}

				$quantity = (int) $this->request->post['blankt_custom_option_quantity'][$index];
				if ($quantity && isset($blankt_custom_option[$option_id])) {
					$this->cart->add($product_id, $quantity, $blankt_custom_option, $recurring_id);
				}
				// print_r($this->request->post['product_id']);
				// print_r($this->request->post['blankt_custom_option_quantity']);
				// print_r($this->request->post['blankt_custom_option']);
			}
		}
	}

	private function getCustomerId()
	{
		return $this->customer->getId() ? $this->customer->getId() : 0;
	}

	public function getImageFromSession($product)
	{
		$original = $image = '';

		foreach ($product['option'] as $option) {
			$blank_product_option_id = $this->config->get('blankt_product_option_id') ? $this->config->get('blankt_product_option_id') : false;

			if ($option['option_id'] == $blank_product_option_id && $option['value']) {
				$blankt_product_serial = $option['value'];

				if ($this->getImageByProductSerial($blankt_product_serial)) {
					if (file_exists(DIR_IMAGE . 'catalog/blankt/' . $blankt_product_serial . $this->image_extension)) {
						$image = $this->model_tool_image->resize('catalog/blankt/' . $blankt_product_serial . $this->image_extension, $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
						$original = HTTPS_SERVER . 'image/catalog/blankt/' . $blankt_product_serial . $this->image_extension;
					} else {
						$image = HTTPS_SERVER . 'image/catalog/blankt/logo.png';
						$original = HTTPS_SERVER . 'image/catalog/blankt/logo.png';
					}
				}

				break;
			}
		}

		// if ($this->getImageByProductSerial()) {
		// } elseif (!empty($this->session->data['blankt_redirect_product_images'])) {
		// 	$this->load->model('tool/image');
		// 	foreach ($this->session->data['blankt_redirect_product_images'] as $blankt_redirect_product_image) {

		// 		foreach ($product['option'] as $option_value) {
		// 			if ($option_value['product_option_id'] == $blankt_redirect_product_image['product_option_id'] && $blankt_redirect_product_image['productSerial'] == $option_value['value']) {
		// 				$image = $blankt_redirect_product_image['image'];
		// 				copy($image, DIR_IMAGE . 'catalog/blankt/' . $blankt_redirect_product_image['productSerial'] . $this->image_extension);
		// 				if (file_exists(DIR_IMAGE . 'catalog/blankt/' . $blankt_redirect_product_image['productSerial'] . $this->image_extension)) {
		// 					$image = $this->model_tool_image->resize('catalog/blankt/' . $blankt_redirect_product_image['productSerial'] . $this->image_extension, $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
		// 				} else {
		// 					$image = HTTPS_SERVER . 'image/catalog/blankt/logo.png';
		// 					// $image = $this->model_tool_image->resize('catalog/blankt/logo.png', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
		// 				}
		// 				break 2;
		// 			}
		// 		}
		// 	}
		// }

		return [
			'small' => $image,
			'original' => $original,
		];
	}

	public function redirectToProductPage()
	{
		if (self::$DEBUG) {
			echo '<pre>';
			print_r(DIR_IMAGE . 'catalog/blankt/logo.png');
		}
		$this->load->model('catalog/product');
		$this->load->model('extension/extension');

		//remove
		// $this->session->data['blankt_cart_update_productSerial'] = 'NkGCm50dcNnx_ZS6blugL';
		// $image

		if (!empty($this->session->data['blankt_cart_update_key']) && !empty($this->request->get['productSerial'])) {

			foreach ($this->session->data['cart'] as $cart_old_key => $q) {
				//remove
				// $this->session->data['blankt_cart_update_key'] = $cart_old_key;
				// if ($cart_old_key == $this->session->data['blankt_cart_update_key']) {
				$product = unserialize(base64_decode($cart_old_key));
				foreach ($product['option'] as $option_key => $option_value) {
					if ($option_value == $this->session->data['blankt_cart_update_productSerial']) {
						$product['option'][$option_key] = $this->request->get['productSerial'];
						$cart_new_key = base64_encode(serialize($product));
						$cart_old_value = $this->session->data['cart'][$cart_old_key];
						// unset($this->session->data['cart'][$cart_old_key]);
						$this->cart->remove($cart_old_key);
						$this->session->data['cart'][$cart_new_key] = $cart_old_value;

						// $product = unserialize(base64_decode($cart_new_key));
						// http://localhost/oc/2011/index.php?route=checkout/cart&productSerial=NkGCm50dcNnx_ZS6blugL
						$image = '';
						$this->addCustomerDesignForProductInTable($this->getCustomerId(), $product['product_id'], $product['option'][$option_key], $cart_new_key, $image);

						if (!empty($this->request->get['productSerial'])) {
							$main_response = $this->getProductRequest($this->request->get['productSerial']);
							$save_image = $main_response['data']['getProduct']['imageUrl'];
							$this->copyImagesToCurrentWebsite($this->request->get['productSerial'], $save_image);
							if (file_exists(DIR_IMAGE . 'catalog/blankt/' . $this->request->get['productSerial'] . $this->image_extension)) {
								$this->updateBlanktImageToOpencart($this->request->get['productSerial'], 'catalog/blankt/' . $this->request->get['productSerial'] . $this->image_extension);
							}
						}
						// $this->response->redirect($this->url->link('checkout/cart', '', 'SSL'));
						// break 2;
					}
				}
				// break;
				// }
			}

			$cart_key = $this->session->data['blankt_cart_update_key'];

			// NkGCm50dcNnu9z7HtI
			// if (isset($this->session->data['cart'][$cart_key]) && $this->session->data['cart'][$cart_key] == $cart_key) {
			// 	// $this->session->data['cart'][$key] = ;
			// 	$json['redirect_url'] = str_replace('&amp;', '&', $this->url->link('checkout/cart', '', true));
			// }
		}

		if (!empty($this->request->get['productSerial'])) {
			$main_response = $this->getProductRequest($this->request->get['productSerial']);
			$save_image = $main_response['data']['getProduct']['imageUrl'];
			$this->copyImagesToCurrentWebsite($this->request->get['productSerial'], $save_image);
			// if ($this->getImageByProductSerial($blankt_product_serial)) {
			// 	// $this->addCustomerDesignForProductInTable($this->getCustomerId(), $this->request->post['product_id'], $this->request->get['productSerial'], '', $image);
			// }
		}

		// .png", ".jpg", ".jpeg", ".pdf
		if (empty($this->session->data['blankt_redirect_product'])) {
			return;
		}

		// http://localhost/oc/2011/index.php?route=checkout/cart&productSerial=NkGCm50dcNnslGQtEDtmo
		// $image_width = 50;
		// $image_height = 50;

		// $this->createImageRequest($this->request->get['productSerial'], $image_width, $image_height);
		// $this->getImageRequest($this->request->get['productSerial'], $image_width, $image_height);

		$this->request->post = json_decode($this->session->data['blankt_redirect_product'], true);

		if (!empty($this->request->post['option'])) {
			$all_product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
			$blankt_product_option_id_for_product = 0;

			foreach ($all_product_options as $key => $value) {
				if ($value['option_id'] == $this->config->get('blankt_product_option_id')) {
					$blankt_product_option_id_for_product = $value['product_option_id'];
				}
			}

			foreach ($this->request->post['option'] as $product_option_id => $value) {
				if ($blankt_product_option_id_for_product == $product_option_id) {
					$this->request->post['option'][$product_option_id] = $this->request->get['productSerial'];
					break;
				}
			}

			// if (empty($this->session->data['blankt_redirect_product_images'])) {
			// 	$this->session->data['blankt_redirect_product_images'] = [];
			// }

			// is session ko table m save karna h
			// $this->session->data['blankt_redirect_product_images'][] = [
			// 	'productSerial' => $this->request->get['productSerial'],
			// 	'product_id' => $this->request->post['product_id'],
			// 	'product_option_id' => $blankt_product_option_id_for_product,
			// 	'image' => $image,
			// ];

			if (!empty($this->request->get['productSerial'])) {

				$main_response = $this->getProductRequest($this->request->get['productSerial']);
				$save_image = $main_response['data']['getProduct']['imageUrl'];

				$this->copyImagesToCurrentWebsite($save_image, $this->request->get['productSerial']);

				$image_path = HTTPS_SERVER . 'image/catalog/blankt/logo.png';
				if (file_exists(DIR_IMAGE . 'catalog/blankt/' . $this->request->get['productSerial'] . $this->image_extension)) {
					$image_path = 'catalog/blankt/' . $this->request->get['productSerial'] . $this->image_extension;
				}

				$this->addCustomerDesignForProductInTable($this->getCustomerId(), $this->request->post['product_id'], $this->request->get['productSerial'], '', $image_path);

				$this->cart->add($this->request->post['product_id'], $this->request->post['quantity'], $this->request->post['option'], 0);
			}
		}

		// uncomment remove this
		unset($this->session->data['blankt_redirect_product']);

		if (self::$DEBUG) {
			echo '</pre>';
		}

		$this->response->redirect($this->url->link('checkout/cart', '', 'SSL'));
		// $this->response->redirect($this->url->link('product/product', 'product_id=212&productSerial=' . $this->request->get['productSerial'], 'SSL'));
	}

	private function copyImagesToCurrentWebsite(string $product_serial, string $image)
	{
		if ($image && !file_exists(DIR_IMAGE . 'catalog/blankt/' . $product_serial . $this->image_extension)) {
			copy($image, DIR_IMAGE . 'catalog/blankt/' . $product_serial . $this->image_extension);

			$this->updateBlanktImageToOpencart($this->request->get['productSerial'], 'catalog/blankt/' . $product_serial . $this->image_extension);
		}

		if (file_exists(DIR_IMAGE . 'catalog/blankt/' . $product_serial . $this->image_extension)) {
			$this->load->model('tool/image');
			$image = $this->model_tool_image->resize('catalog/blankt/' . $product_serial . $this->image_extension, $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
		} else {
			$image = HTTPS_SERVER . 'image/catalog/blankt/logo.png';
			// $image = $this->model_tool_image->resize('catalog/blankt/logo.png', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
		}

		return $image;
	}

	private function deleteCustomerDesignForProductInTable(int $customer_id, string $product_serial)
	{
		if (!($customer_id && $product_serial)) return;

		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_product_blankt` WHERE customer_id = '" . $customer_id . "', product_serial = '" . $product_serial . "'");
	}


	// removes cart page products with blankt product serial
	public function removeAllBlanktSerialProductFromCart($args)
	{
		$productSerial = $this->request->post['productSerial'];

		if (!$productSerial) return;

		foreach ($this->session->data['cart'] as $cart_old_key => $q) {
			$product = unserialize(base64_decode($cart_old_key));
			foreach ($product['option'] as $option_key => $option_value) {
				if ($option_value == $productSerial) {
					$this->cart->remove($cart_old_key);

					unset($this->session->data['vouchers'][$cart_old_key]);

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
					unset($this->session->data['reward']);
				}
			}
		}

		$this->deleteCustomerDesignForProductInTable($this->getCustomerId(), $productSerial);
	}

	private function updateBlanktImageToOpencart(string $product_serial, string $image)
	{
		if (!($image && $product_serial)) return;

		$sql = "UPDATE `" . DB_PREFIX . "customer_product_blankt` SET image = '" . (string) $image . "' WHERE product_serial = '" . $product_serial . "'";

		$this->db->query($sql);
	}

	private function addCustomerDesignForProductInTable(int $customer_id, int $product_id, string $product_serial, string $cart_key, string $image)
	{
		// $customer_id &&
		if (!($product_id && $product_serial)) return;
		$sql = "REPLACE INTO `" . DB_PREFIX . "customer_product_blankt` SET customer_id = '" . $customer_id . "', product_id = '" . $product_id . "', product_serial = '" . $product_serial . "', cart_key = '" . $cart_key . "', image = '" . $image . "'";
		// REPLACE
		$this->db->query($sql);
	}

	private function getCustomerDesignForProductInTable(int $customer_id, int $product_id)
	{
		if (!($customer_id && $product_id)) return;

		$row = $this->db->query("SELECT product_serial FROM `" . DB_PREFIX . "customer_product_blankt` WHERE customer_id = '" . $customer_id . "' AND product_id = '" . $product_id . "'")->row;

		return empty($row) ? '' : $row['product_serial'];
	}

	public function checkoutCheckoutPage($data)
	{
		$products = $this->cart->getProducts();
		$is_redirect = false;
		if ($this->config->get('blankt_status')) {
			foreach ($products as $product) {
				if (in_array((int) $product['product_id'], $this->config->get('blankt_product_ids')) && (count($product['option']) == 1)) {
					$is_redirect = true;
					break;
				}
			}

			if ($is_redirect) {
				$this->session->data['error'] = $this->load->language('product/blankt_button')['error_choose_size_format_to_continue'];
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}
	}

	public function checkoutCartPage($data)
	{
		$this->load->language('product/blankt_button');
		$data['data']['text_select_required_fields'] = $this->language->get('text_select_required_fields');
		$data['data']['column_actions'] = $this->language->get('column_actions');
		$data['data']['blankt_copyright_notice'] = $this->language->get('blankt_copyright_notice');
		$data['data']['button_edit_your_design'] = $this->language->get('button_edit_your_design');
		$data['data']['button_edit_your_design_option'] = $this->language->get('button_edit_your_design_option');
		$data['data']['website_to_blankt_redirect_url'] = $this->config->get('blankt_website_to_blankt_redirect_url');
		$data['data']['blank_product_option_id'] = $this->config->get('blankt_status') ? $this->config->get('blankt_product_option_id') : false;
		$data['data']['blankt_terms_and_condtions'] = $this->config->get('blankt_status') && !empty($this->config->get('blankt_terms_and_condtions')) ? html_entity_decode($this->config->get('blankt_terms_and_condtions')) : '';
		$data['data']['is_mobile'] = $this->isMobile();
	}

	public function productProductPage($data)
	{
		$this->load->language('product/blankt_button');
		$data['data']['column_actions'] = $this->language->get('column_actions');
		$data['data']['button_blankt_designer'] = $this->language->get('button_blankt_designer');
		$data['data']['button_blankt_designer_add_to_cart'] = $this->language->get('button_blankt_designer_add_to_cart');
		$data['data']['button_blankt_designer_add_to_cart_below'] = $this->language->get('button_blankt_designer_add_to_cart_below');
		$data['data']['button_blankt_add_product_option'] = $this->language->get('button_blankt_add_product_option');
		$data['data']['blank_product_option_id'] = $this->config->get('blankt_status') ? $this->config->get('blankt_product_option_id') : false;
		$data['data']['blankt_product_status'] = $this->config->get('blankt_status') && in_array((int) $this->request->get['product_id'], $this->config->get('blankt_product_ids'));
		$data['data']['website_to_blankt_redirect_url'] = $this->config->get('blankt_website_to_blankt_redirect_url');
		$data['data']['blankt_designer_html'] = '';

		$productSerial = empty($this->request->get['productSerial']) ? '' : $this->request->get['productSerial'];
		$data['data']['productSerial'] = empty($productSerial) ? '' : $productSerial;

		$data['data']['button_blankt_designer_add_to_cart_below'] = '';
		// if productSerial is present it means it needs to be added to cart page
		if ($productSerial) {
			// caveat
			$data['data']['blankt_product_status'] = false;
			$data['data']['button_cart'] = $this->language->get('button_blankt_designer_add_to_cart');
			$data['data']['button_blankt_designer_add_to_cart_below'] = $this->language->get('button_blankt_designer_add_to_cart_below');
		}

		if ($data['data']['blankt_product_status']) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/blankt_button.tpl')) {
				$data['data']['blankt_designer_html'] = $this->load->view($this->config->get('config_template') . '/template/product/blankt_button.tpl', $data['data']);
			} else {
				$data['data']['blankt_designer_html'] = $this->load->view('default/template/product/blankt_button.tpl', $data['data']);
			}
		} elseif ($productSerial) {
			if ($this->getImageByProductSerial($productSerial)) {
				if (file_exists(DIR_IMAGE . 'catalog/blankt/' . $productSerial . $this->image_extension)) {
					$data['data']['thumb'] = $this->model_tool_image->resize('catalog/blankt/' . $productSerial . $this->image_extension, $this->config->get('config_image_thumb_width') - 130, $this->config->get('config_image_thumb_height'));
					// added -130 to fix image in product page
				}

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/blankt_add_product_option.tpl')) {
					$data['data']['blankt_add_product_option_html'] = $this->load->view($this->config->get('config_template') . '/template/product/blankt_add_product_option.tpl', $data['data']);
				} else {
					$data['data']['blankt_add_product_option_html'] = $this->load->view('default/template/product/blankt_add_product_option.tpl', $data['data']);
				}
			}
		}

		// $productSerial = $this->getCustomerDesignForProductInTable($this->getCustomerId(), $this->request->get['product_id']);
	}

	// ajax
	public function cartProductPageAjax($data)
	{
		$json = [];

		$this->load->language('product/product');
		$this->load->language('product/blankt_button');
		// $this->load->model('catalog/category');
		$json = [
			// 'redirect_url' => '',
			'success' => '',
		];
		$data = [];
		$data['all_products'] = [];
		// $this->session->data['blankt_cart_update_key'] = '';
		// $this->session->data['blankt_cart_update_productSerial'] = '';
		$cart_key = empty($this->request->post['cart_key']) ? '' : $this->request->post['cart_key'];
		$productSerial = !empty($this->request->post['productSerial']) ? $this->request->post['productSerial'] : '';
		$this->request->get['productSerial'] = $productSerial;

		$product_id = 0;

		if (!empty($cart_key)) {

			foreach ($this->session->data['cart'] as $cart_old_key => $q) {
				$product = unserialize(base64_decode($cart_old_key));
				foreach ($product['option'] as $option_key => $option_value) {
					if ($option_value == $productSerial) {
						$product_id = $product['product_id'];
						$options = $product['option'];
						$qty = $product['qty'];
						$data['all_products'][] = [
							'options' => $options,
							'quantity' => $q,
						];
					}
				}
			}
		}

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$data['product_id'] = $this->request->get['product_id'] = $this->request->post['product_id'] = $product_id;
		$product_info = $this->model_catalog_product->getProduct($product_id);
		if (!empty($product_info)) {

			$data['text_loading'] = $this->language->get('text_loading');
			$json['text_cancel'] = $this->language->get('text_cancel');
			$json['text_update_button_cart'] = $this->language->get('text_update_button_cart');
			$data['button_blankt_add_product_option'] = $this->language->get('button_blankt_add_product_option');

			$data['text_select'] = $this->language->get('text_select');
			$data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$data['text_model'] = $this->language->get('text_model');
			$data['text_reward'] = $this->language->get('text_reward');
			$data['text_points'] = $this->language->get('text_points');
			$data['text_stock'] = $this->language->get('text_stock');
			$data['text_discount'] = $this->language->get('text_discount');
			$data['text_tax'] = $this->language->get('text_tax');
			$data['text_option'] = $this->language->get('text_option');
			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_write'] = $this->language->get('text_write');
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
			$data['text_note'] = $this->language->get('text_note');
			$data['text_tags'] = $this->language->get('text_tags');
			$data['text_related'] = $this->language->get('text_related');
			$data['text_loading'] = $this->language->get('text_loading');

			$data['entry_qty'] = $this->language->get('entry_qty');
			$data['entry_name'] = $this->language->get('entry_name');
			$data['entry_review'] = $this->language->get('entry_review');
			$data['entry_rating'] = $this->language->get('entry_rating');
			$data['entry_good'] = $this->language->get('entry_good');
			$data['entry_bad'] = $this->language->get('entry_bad');
			$data['entry_captcha'] = $this->language->get('entry_captcha');

			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['button_upload'] = $this->language->get('button_upload');
			$data['button_continue'] = $this->language->get('button_continue');

			$data['minimum'] = $product_info['minimum'];

			$data['options'] = array();


			foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false));
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$this->productProductPage(['data' => &$data]);
				if ($data['blank_product_option_id'] == $option['option_id']) {
					$option['value'] = $data['productSerial'];
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}
		}

		// $this->load->language('product/blankt_button');
		// $data['data']['column_actions'] = $this->language->get('column_actions');
		// $data['data']['button_blankt_designer'] = $this->language->get('button_blankt_designer');
		// $data['data']['button_blankt_designer_add_to_cart'] = $this->language->get('button_blankt_designer_add_to_cart');
		// $data['data']['button_blankt_designer_add_to_cart_below'] = $this->language->get('button_blankt_designer_add_to_cart_below');
		// $data['data']['button_blankt_add_product_option'] = $this->language->get('button_blankt_add_product_option');
		// $data['data']['blank_product_option_id'] = $this->config->get('blankt_status') ? $this->config->get('blankt_product_option_id') : false;
		// $data['data']['blankt_product_status'] = $this->config->get('blankt_status') && in_array((int) $this->request->get['product_id'], $this->config->get('blankt_product_ids'));
		// $data['data']['website_to_blankt_redirect_url'] = $this->config->get('blankt_website_to_blankt_redirect_url');
		// $data['data']['blankt_designer_html'] = '';

		// $productSerial = empty($this->request->get['productSerial']) ? '' : $this->request->get['productSerial'];
		// $data['data']['productSerial'] = empty($productSerial) ? '' : $productSerial;

		// $data['data']['button_blankt_designer_add_to_cart_below'] = '';
		// // if productSerial is present it means it needs to be added to cart page
		// if ($productSerial) {
		// 	// caveat
		// 	$data['data']['blankt_product_status'] = false;
		// 	$data['data']['button_cart'] = $this->language->get('button_blankt_designer_add_to_cart');
		// 	$data['data']['button_blankt_designer_add_to_cart_below'] = $this->language->get('button_blankt_designer_add_to_cart_below');
		// }

		// if ($data['data']['blankt_product_status']) {
		// 	if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/blankt_button.tpl')) {
		// 		$data['data']['blankt_designer_html'] = $this->load->view($this->config->get('config_template') . '/template/product/blankt_button.tpl', $data['data']);
		// 	} else {
		// 		$data['data']['blankt_designer_html'] = $this->load->view('default/template/product/blankt_button.tpl', $data['data']);
		// 	}

		// $data['blank_product_option_id'] = $this->config->get('blankt_product_option_id') ? $this->config->get('blankt_product_option_id') : false;

		if ($productSerial) {

			// if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/blankt_add_product_option.tpl')) {
			// 	$json['blankt_add_product_option_html'] = $this->load->view($this->config->get('config_template') . '/template/product/blankt_add_product_option.tpl', $data);
			// } else {
			// 	$json['blankt_add_product_option_html'] = $this->load->view('default/template/product/blankt_add_product_option.tpl', $data);
			// }

			$json['blankt_add_product_option_html'] = '';
			$data['text_update_button_cart'] = $this->language->get('text_update_button_cart');
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/blankt_cart_product_page.tpl')) {
				$json['blankt_cart_product_page_html'] = $this->load->view($this->config->get('config_template') . '/template/product/blankt_cart_product_page.tpl', $data);
			} else {
				$json['blankt_cart_product_page_html'] = $this->load->view('default/template/product/blankt_cart_product_page.tpl', $data);
			}

			$json['all_products'] = $data['all_products'];
		}

		// $json['html'] = $data;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete()
	{
		$this->load->language('product/blankt_button');

		$json = [
			'success' => false,
		];

		$this->session->data['blankt_cart_update_key'] = '';
		$this->session->data['blankt_cart_update_productSerial'] = '';
		$blankt_product_serial = empty($this->request->post['blankt_product_serial']) ? '' : $this->request->post['blankt_product_serial'];

		if ($blankt_product_serial) {
			$this->deleteCustomerDesignForProductInTable($this->getCustomerId(), $blankt_product_serial);
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit()
	{
		// $this->load->language('product/product');
		$this->load->language('product/blankt_button');
		// $this->load->model('catalog/category');

		$json = [
			'redirect_url' => '',
			'success' => '',
		];

		$this->session->data['blankt_cart_update_key'] = '';
		$this->session->data['blankt_cart_update_productSerial'] = '';
		$cart_key = empty($this->request->post['cart_key']) ? '' : $this->request->post['cart_key'];
		$productSerial = !empty($this->request->post['productSerial']) ? $this->request->post['productSerial'] : '';
		$suffic_url = !empty($productSerial) ? '&p=' . $productSerial : '';

		if ($cart_key) {
			$this->session->data['blankt_cart_update_key'] = $cart_key;
			$this->session->data['blankt_cart_update_productSerial'] = $productSerial;
			$json['redirect_url'] = $this->config->get('blankt_website_to_blankt_redirect_url') . $suffic_url;
			$json['success'] = $this->language->get('redirecting_to_blankt');
		}

		// if (isset($this->session->data['cart'][$cart_key]) && $this->session->data['cart'][$cart_key] == $cart_key) {
		// 	$this->session->data['cart'][$key] = (int)$qty;
		// 	$json['redirect_url'] = str_replace('&amp;', '&', $this->url->link('checkout/cart', '', true));
		// }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function add()
	{
		$this->load->language('product/product');
		$this->load->language('product/blankt_button');

		$this->load->model('catalog/category');

		$product_id = 0;

		if (isset($this->request->post['product_id'])) {
			$product_id = (int) $this->request->post['product_id'];
		}

		if (isset($this->request->post['option']) && isset($this->request->post['option'][$this->config->get('blankt_product_option_id')])) {
			// $this->request->post['option'][$this->config->get('blankt_product_option_id')];
		}

		$suffic_url = !empty($this->request->post['productSerial']) ? '&p=' . urlencode($this->request->post['productSerial']) : '';

		$this->session->data['blankt_redirect_product'] = json_encode($this->request->post, true);

		$json = [];

		$json = [
			'redirect_url' => $this->config->get('blankt_website_to_blankt_redirect_url') . $suffic_url,
			'success' => $this->language->get('redirecting_to_blankt'),
		];

		// if (empty($json = $this->checkValidProductToAddInCart($product_id))) {
		// } elseif (!empty($this->request->post['blankt_product_page_redirect'])) {
		// 	$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
		// }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function createImageRequest(string $productSerial = '', $width = 6000, $height = 6000, $file_extension = '.png', $dpi = 300, $page = 0)
	{

		$url = 'https://api.blankt.com/graphql';
		$apiKey = $this->config->get('blankt_api_key');

		$data_json = '{"query": "mutation CreateImage { createImage(productSerial: \"' . $productSerial . '\", width: ' . $width . ', height: ' . $height . ', fileExtension: \"' . $file_extension . '\", dpi: ' . $dpi . ', page: ' . $page . ') }"}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/graphql',
			'Authorization: ' . $apiKey
		));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (self::$DEBUG) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		$response = curl_exec($ch);

		if (self::$DEBUG) {
			$info = curl_getinfo($ch);
			echo '<pre>';
			print_r($data_json);
			print_r($info);
			echo '</pre>';
		}

		if (curl_errno($ch)) {
			exit('Error: ' . curl_error($ch));
		}

		curl_close($ch);
	}

	private function getImageRequest(string $productSerial = '', $width = 6000, $height = 6000, $file_extension = '.png', $dpi = 300, $page = 0)
	{

		$url = 'https://api.blankt.com/graphql';
		$apiKey = $this->config->get('blankt_api_key');

		$data_json = '{"query": "query GetImage { getImage(productSerial: \"' . $productSerial . '\", width: ' . $width . ', height: ' . $height . ', fileExtension: \"' . $file_extension . '\", dpi: ' . $dpi . ', page: ' . $page . ') { imageUrl status createProgressPercentage } }"}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/graphql',
			'Authorization: ' . $apiKey
		));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (self::$DEBUG) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		if (self::$DEBUG) {
			echo '<pre>';
			print_r($data_json);
			print_r($info);
			print_r(json_decode($response, true));
			echo '</pre>';
		}

		if (curl_errno($ch)) {
			exit('Error: ' . curl_error($ch));
		}

		curl_close($ch);

		return json_decode($response, true);
	}

	private function getProductRequest(string $productSerial = '')
	{

		$url = 'https://api.blankt.com/graphql';
		$apiKey = $this->config->get('blankt_api_key');

		$data_json = '{"query": "query getProduct { getProduct(productSerial: \"' . $productSerial . '\") { imageUrl } }"}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/graphql',
			'Authorization: ' . $apiKey
		));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (self::$DEBUG) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		if (self::$DEBUG) {
			echo '<pre>';
			print_r($data_json);
			print_r($info);
			print_r(json_decode($response, true));
			echo '</pre>';
		}

		if (curl_errno($ch)) {
			exit('Error: ' . curl_error($ch));
		}

		curl_close($ch);

		$full_response = json_decode($response, true);
		// $image = $full_response['data']['getProduct']['imageUrl'];
		// $image = $this->copyImagesToCurrentWebsite($productSerial, $image);

		return $full_response;
	}

	// not in use
	// public function addToCartAfterGettingSerial()
	// {
	// 	if (self::$DEBUG) {
	// 		echo '<pre>';
	// 		print_r(DIR_IMAGE . 'catalog/blankt/logo.png');
	// 	}

	// 	$this->load->model('catalog/product');
	// 	$this->load->model('extension/extension');

	// 	// .png", ".jpg", ".jpeg", ".pdf
	// 	if (empty($this->session->data['blankt_redirect_product']) || empty($this->request->get['productSerial'])) {
	// 		return;
	// 	}

	// 	// $image_width = 50;
	// 	// $image_height = 50;
	// 	$main_response = $this->getProductRequest($this->request->get['productSerial']);
	// 	$image = $main_response['data']['getProduct']['imageUrl'];

	// 	// $this->createImageRequest($this->request->get['productSerial'], $image_width, $image_height);
	// 	// $this->getImageRequest($this->request->get['productSerial'], $image_width, $image_height);

	// 	$this->request->post = json_decode($this->session->data['blankt_redirect_product'], true);

	// 	if (!empty($this->request->post['option'])) {
	// 		$all_product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
	// 		$blankt_product_option_id_for_product = 0;

	// 		foreach ($all_product_options as $key => $value) {
	// 			if ($value['option_id'] == $this->config->get('blankt_product_option_id')) {
	// 				$blankt_product_option_id_for_product = $value['product_option_id'];
	// 			}
	// 		}

	// 		foreach ($this->request->post['option'] as $product_option_id => $value) {
	// 			if ($blankt_product_option_id_for_product == $product_option_id) {
	// 				$this->request->post['option'][$product_option_id] = $this->request->get['productSerial'];
	// 				break;
	// 			}
	// 		}

	// 		if (empty($this->session->data['blankt_redirect_product_images'])) {
	// 			$this->session->data['blankt_redirect_product_images'] = [];
	// 		}

	// 		$this->session->data['blankt_redirect_product_images'][] = [
	// 			'productSerial' => $this->request->get['productSerial'],
	// 			'product_id' => $this->request->post['product_id'],
	// 			'product_option_id' => $blankt_product_option_id_for_product,
	// 			'image' => $image,
	// 		];
	// 	}


	// 	$error = false;
	// 	if (isset($this->request->post['product_id'])) {
	// 		$product_id = (int)$this->request->post['product_id'];
	// 	} else {
	// 		$product_id = 0;
	// 	}

	// 	$product_info = $this->model_catalog_product->getProduct($product_id);

	// 	if (isset($this->request->post['quantity'])) {
	// 		$quantity = (int)$this->request->post['quantity'];
	// 	} else {
	// 		$quantity = 1;
	// 	}

	// 	if (isset($this->request->post['option'])) {
	// 		$option = array_filter($this->request->post['option']);
	// 	} else {
	// 		$option = array();
	// 	}

	// 	$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

	// 	foreach ($product_options as $product_option) {
	// 		if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
	// 			$error = sprintf($this->language->get('error_required'), $product_option['name']);
	// 		}
	// 	}

	// 	if (isset($this->request->post['recurring_id'])) {
	// 		$recurring_id = $this->request->post['recurring_id'];
	// 	} else {
	// 		$recurring_id = 0;
	// 	}

	// 	$recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

	// 	if ($recurrings) {
	// 		$recurring_ids = array();

	// 		foreach ($recurrings as $recurring) {
	// 			$recurring_ids[] = $recurring['recurring_id'];
	// 		}

	// 		if (!in_array($recurring_id, $recurring_ids)) {
	// 			$error = $this->language->get('error_recurring_required');
	// 		}
	// 	}

	// 	$this->cart->add($this->request->post['product_id'], $this->request->post['quantity'], $option, $recurring_id);

	// 	// uncomment
	// 	unset($this->session->data['blankt_redirect_product']);
	// 	unset($this->session->data['shipping_method']);
	// 	unset($this->session->data['shipping_methods']);
	// 	unset($this->session->data['payment_method']);
	// 	unset($this->session->data['payment_methods']);

	// 	// Totals

	// 	$total_data = array();
	// 	$total = 0;
	// 	$taxes = $this->cart->getTaxes();

	// 	// Display prices
	// 	if ($error === false && (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price'))) {
	// 		$sort_order = array();

	// 		$results = $this->model_extension_extension->getExtensions('total');

	// 		foreach ($results as $key => $value) {
	// 			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
	// 		}

	// 		array_multisort($sort_order, SORT_ASC, $results);

	// 		foreach ($results as $result) {
	// 			if ($this->config->get($result['code'] . '_status')) {
	// 				$this->load->model('total/' . $result['code']);

	// 				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
	// 			}
	// 		}

	// 		$sort_order = array();

	// 		foreach ($total_data as $key => $value) {
	// 			$sort_order[$key] = $value['sort_order'];
	// 		}

	// 		array_multisort($sort_order, SORT_ASC, $total_data);
	// 	}

	// 	if (self::$DEBUG) {
	// 		echo '</pre>';
	// 	}
	// }

	// public function checkValidProductToAddInCart($product_id)
	// {
	// 	$this->load->language('checkout/cart');

	// 	$json = array();

	// 	$this->load->model('catalog/product');

	// 	$product_info = $this->model_catalog_product->getProduct($product_id);

	// 	if ($product_info) {
	// 		if (isset($this->request->post['quantity'])) {
	// 			$quantity = (int)$this->request->post['quantity'];
	// 		} else {
	// 			$quantity = 1;
	// 		}

	// 		if (isset($this->request->post['option'])) {
	// 			$option = array_filter($this->request->post['option']);
	// 		} else {
	// 			$option = array();
	// 		}

	// 		$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

	// 		foreach ($product_options as $product_option) {
	// 			if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
	// 				$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
	// 			}
	// 		}

	// 		if (isset($this->request->post['recurring_id'])) {
	// 			$recurring_id = $this->request->post['recurring_id'];
	// 		} else {
	// 			$recurring_id = 0;
	// 		}

	// 		$recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

	// 		if ($recurrings) {
	// 			$recurring_ids = array();

	// 			foreach ($recurrings as $recurring) {
	// 				$recurring_ids[] = $recurring['recurring_id'];
	// 			}

	// 			if (!in_array($recurring_id, $recurring_ids)) {
	// 				$json['error']['recurring'] = $this->language->get('error_recurring_required');
	// 			}
	// 		}
	// 	}

	// 	return $json;
	// }


    /**
     * Sets Mobile_Detect tool object.
     *
     * @return Mobile_Detect
     */
    public function getMobileDetect()
    {
        if ($this->mobile_detect === null) {
            $this->mobile_detect = new Mobile_Detect();
        }

        return $this->mobile_detect;
    }

    /**
     * Checks if visitor's device is a mobile device.
     *
     * @return bool
     */
    public function isMobile()
    {
        if ($this->is_mobile === null) {
            $mobileDetect = $this->getMobileDetect();
            $this->is_mobile = $mobileDetect->isMobile();
        }

        return $this->is_mobile;
    }
}
