<?php
class ControllerModuleloosegallery extends Controller {
	private $error = array();
	private $config_keys = array(
		'status',
		'api_key',
		'loosegallery_to_website_redirect_url',
		'website_to_loosegallery_redirect_url',
		'product_option_id',
		'product_ids',
		'terms_and_condtions',
	);
	private $status = false;
	private $loosegallery_to_website_redirect_url = '';
	private $website_to_loosegallery_redirect_url = '';
	private $api_key = '';
	private $product_option_id = '';
	private $product_ids = '[]';
	private $terms_and_condtions = '';

	public function install() {
		$this->load->model('setting/setting');

		$this->product_option_id = $this->addProductOption();
		$data = [];
		foreach ($this->config_keys as $key) {
			if(isset($this->{$key})) {
				$data['loosegallery_' . $key] = $this->$key;
			}
		}

		$this->model_setting_setting->editSetting('loosegallery', $data);
		$this->installTables();
	}

public function uninstall() {
    $this->uninstallTables();
    $this->removeSerialOptionFromProducts();
    $this->removeSerialOption();
}

private function removeSerialOptionFromProducts() {
    $query = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_description WHERE name = 'Serial'");
    if ($query->num_rows) {
        foreach ($query->rows as $row) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$row['option_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE option_id = '" . (int)$row['option_id'] . "'");
        }
    }
}

private function removeSerialOption() {
    $this->load->model('catalog/option');
    $query = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_description WHERE name = 'Serial'");
    if ($query->num_rows) {
        foreach ($query->rows as $row) {
            $this->model_catalog_option->deleteOption($row['option_id']);
        }
    }
}

	private function addProductOption() {

		$this->load->model('catalog/option');
		$this->load->model('localisation/language');
		if ($this->config->get('loosegallery_product_option_id')) {
			$option = $this->model_catalog_option->getOption($this->config->get('loosegallery_product_option_id'));
			if(!empty($option)) {
				return $this->config->get('loosegallery_product_option_id');
			}
		}

		$option_description = [];

		$languages = $this->model_localisation_language->getLanguages();
		foreach ($languages as $language) {
			$option_description[$language['language_id']] = array(
                'name' => 'Serial'
            );
		}

		$data = array(
		    'option_description' => $option_description,
		    'type' => 'text',
		    'sort_order' => ''
		);

		$option_id = $this->model_catalog_option->addOption($data);

		return $option_id;
	}

	private function isAlreadyAddedOption($product_id, $product_option) {
		$sql = "SELECT count(product_option_id) as total FROM `" . DB_PREFIX . "product_option` WHERE  product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'";
		return $found = $this->db->query($sql)->row['total'];
	}

	private function addOptionInProduct($product_id, $product_options) {
		foreach ($product_options as $product_option) {
			if (!$this->isAlreadyAddedOption($product_id, $product_option)) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
			}
		}
	}

	private function addProductOptionInProducts($products, $loosegallery_product_option_id) {
		$product_options = [
			[
				'product_option_id' => '',
				'option_id' => $loosegallery_product_option_id,
				'type' => 'text',
				'required' => '0',
				'value' => '',
			]
		];

		foreach ($products as $product_id) {
			$this->addOptionInProduct($product_id, $product_options);
		}
	}

	public function index() {

		$this->load->language('module/loosegallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			file_put_contents(DIR_APPLICATION . '../catalog/view/javascript/ces/loosegallery_products.js', 'window.loosegallery_products = ' . json_encode($this->config->get('loosegallery_product_ids'), true));

			$this->request->post['loosegallery_product_option_id'] = $this->addProductOption();
			$this->addProductOptionInProducts($this->request->post['loosegallery_product_ids'], $this->request->post['loosegallery_product_option_id']);

			$this->model_setting_setting->editSetting('loosegallery', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['help_loosegallery_terms_and_condtions'] = $this->language->get('help_loosegallery_terms_and_condtions');
		$data['entry_loosegallery_terms_and_condtions'] = $this->language->get('entry_loosegallery_terms_and_condtions');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_api_key'] = $this->language->get('entry_api_key');
		$data['entry_integration_url'] = $this->language->get('entry_integration_url');
		$data['entry_product_ids'] = $this->language->get('entry_product_ids');
		$data['entry_loosegallery_to_website_redirect_url'] = $this->language->get('entry_loosegallery_to_website_redirect_url');
		$data['entry_website_to_loosegallery_redirect_url'] = $this->language->get('entry_website_to_loosegallery_redirect_url');

		$data['help_product_ids'] = $this->language->get('help_product_ids');
		$data['help_warning'] = $this->language->get('help_warning');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/loosegallery', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('module/loosegallery', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		foreach ($this->config_keys as $key) {
			$key = 'loosegallery_' . $key;
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
		}

		if (isset($this->request->post['loosegallery_product_ids'])) {
			$product_product_ids = $this->request->post['loosegallery_product_ids'];
		} elseif (!empty($this->config->get('loosegallery_product_ids'))) {
			$product_product_ids = $this->config->get('loosegallery_product_ids');
		} else {
			$product_product_ids = array();
		}
		
		if (gettype($product_product_ids) == 'string' && $product_product_ids == '[]') {
			$product_product_ids = [];
		}
		$data['product_product_ids'] = array();

		if (!empty($product_product_ids)) {
			
			foreach ($product_product_ids as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);
	
				if ($product_info) {
					$data['product_product_ids'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					);
				}
			}
		}

		$data['token'] = $this->session->data['token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/loosegallery.tpl', $data));
	}

	private function isValidURL($url)
	{
		return true;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/loosegallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->isValidURL($this->request->post['loosegallery_loosegallery_to_website_redirect_url'])) {
			$this->error['error_loosegallery_loosegallery_to_website_redirect_url'] = $this->language->get('error_incorrect_loosegallery_to_website_redirect_url');
		}

		if (!$this->isValidURL($this->request->post['loosegallery_website_to_loosegallery_redirect_url'])) {
			$this->error['error_loosegallery_website_to_loosegallery_redirect_url'] = $this->language->get('error_incorrect_website_to_loosegallery_redirect_url');
		}

		return !$this->error;
	}

	private function uninstallTables() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_product_loosegallery`");
	}

	private function installTables() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_product_loosegallery` (
			  `customer_id` int(11) NOT NULL ,
			  `product_id`  int(11) NOT NULL ,
			  `product_serial`  varchar(200) NOT NULL ,
			  `image`  varchar(1000) DEFAULT '' ,
			  `cart_key`  varchar(1000) DEFAULT '',
			  `date_add` datetime DEFAULT current_timestamp(),
			  `date_upd` datetime ON UPDATE current_timestamp(),
			  PRIMARY KEY (`product_serial`)
		) DEFAULT COLLATE=utf8_general_ci;");
	}
}