<?php
class ControllerExtensionModuleMedia extends Controller
{

	/*
	INSERT INTO `dev_opencart_3.3`.`oc_setting` (`code`, `key`) VALUES ('module_media', 'media_cloudflare_account_id');
	INSERT INTO `dev_opencart_3.3`.`oc_setting` (`code`, `key`) VALUES ('module_media', 'media_cloudflare_token');
	INSERT INTO `dev_opencart_3.3`.`oc_setting` (`code`, `key`, `value`) VALUES ('module_media', 'module_media_status', '0');
	*/

    private $error = array();


    // Index
    //  
    public function index()
	{
        $this->load->language('extension/module/media');
        $this->load->model('setting/setting');

		$url = '';
	
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			if (isset($this->request->post['media_cloudflare_account_id'])) {			
				$this->model_setting_setting->editSettingValue('module_media', 'media_cloudflare_account_id',$this->request->post['media_cloudflare_account_id']);
			}

			if (isset($this->request->post['media_cloudflare_token'])) {
				$this->model_setting_setting->editSettingValue('module_media', 'media_cloudflare_token',$this->request->post['media_cloudflare_token']);

			}

			if (isset($this->request->post['module_media_status'])) {
				$this->model_setting_setting->editSettingValue('module_media', 'module_media_status',$this->request->post['module_media_status']);

			}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		// add user token
		$data['user_token'] = $this->session->data['user_token'];

		$data['add'] = $this->url->link('extension/module/media/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/media/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		// add cancel button
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);


		// breadcrumbs
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/media', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/media', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}


		// warnings and errors
        if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

        
		
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/media', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/media', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		// status
		if (isset($this->request->post['module_media_status'])) {
			$data['module_media_status'] = $this->request->post['module_media_status'];
		} else {
			$data['module_media_status'] = $this->config->get('module_media_status');
		}

		// cloudflare id
		if (isset($this->request->post['media_cloudflare_id'])) {
			$data['media_cloudflare_account_id'] = $this->request->post['media_cloudflare_id'];
		} else {
			$data['media_cloudflare_account_id'] = $this->config->get('media_cloudflare_account_id');
		}

		// cloudflare token
		if (isset($this->request->post['media_cloudflare_token'])) {
			$data['media_cloudflare_token'] = $this->request->post['media_cloudflare_token'];
		} else {
			$data['media_cloudflare_token'] = $this->config->get('media_cloudflare_token');
		}
		
		$this->load->model('catalog/media');
		$data['videos'] = $this->model_catalog_media->getVideos();

        // add heading title
        $this->document->setTitle($this->language->get('heading_title'));

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/media', $data));

        
    }


    //
    // Validate
    // 
    protected function validate()
	{


        if (!$this->user->hasPermission('modify', 'extension/module/media')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		return !$this->error;

    }

    // 
    // install
    // 
    public function install()
	{
		if(!$this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'audio_id'")->rows) {
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product ADD COLUMN `audio_id` INT(11) NULL AFTER `date_modified`");
		}

		if(!$this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'single_purchase'")->rows) {
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product ADD COLUMN `single_purchase` INT NULL DEFAULT '0' AFTER `audio_id`");
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`) VALUES ('module_media', 'media_cloudflare_account_id')");
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`) VALUES ('module_media', 'media_cloudflare_token')");
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`, `value`) VALUES ('module_media', 'module_media_status', '0')");
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "product_to_video (`product_id` int(11) DEFAULT NULL, `video_id` text DEFAULT NULL)");
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "subscription ( `customer_id` int(11) NOT NULL,  `end_date` datetime NOT NULL,  PRIMARY KEY (`customer_id`) )");
		

	}

    
    //
    // uninstall
    // 
	public function uninstall()
	{
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_media');

		$query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'audio_id'");
		if($query->rows) {
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product DROP COLUMN `audio_id`");
		}

		$query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'single_purchase'");
		if($query->rows) {
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product DROP COLUMN `single_purchase`");
		}
	}


	//
	public function add() {
		$this->load->language('extension/module/media');
        $this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title_add'));

		$url = '';
		// cancel button url
		$data['cancel'] = $this->url->link('extension/module/media', 'user_token=' . $this->session->data['user_token'] . $url, true);

		// add user token
		$data['user_token'] = $this->session->data['user_token'];

		$data['action'] = $this->url->link('extension/module/media/add', 'user_token=' . $this->session->data['user_token'], true);


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateAdd()) {


			

			

		}

		// warnings and errors
        if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		$this->load->model('catalog/manufacturer');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/media_form', $data));
	}

	public function validateAdd() {

		if (!$this->user->hasPermission('modify', 'extension/module/media')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// check if media id is present in post request
		if (!isset($this->request->post['media_id']) || $this->request->post['media_id'] == '') {			
			$this->error['warning'] = $this->language->get('error_file_missing'); 
		}

		return !$this->error;

	}

	public function mediaAutocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/download');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_download->getDownloads($filter_data);

			foreach ($results as $result) {
				$token = "." . substr($result['filename'], strrpos($result['filename'], ".") + 1);
				$original_file = str_replace($token, '', $result['filename']);
				$file_type = substr($original_file, strrpos($original_file, ".") + 1);
				$allowed_types = array('mp4');

				foreach ($allowed_types as $type ) {
					if(strpos($original_file, $type) !== false) {
						$json[] = array(
							'manufacturer_id' => $result['download_id'],
							'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
						);
					}
				}

			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function uploadUrl() {
        $this->load->language('extension/module/media');
        $this->load->model('setting/setting');
		$json = array();


		if (!isset($this->request->post['id']) && !isset($this->request->post['name'])) {

			$json['error'] = 'id or name is missing';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$store_id = 0;
		$store_url = '';
		if ($store_id === 0) {
			$store_url = basename(DIR_TEMPLATE) == 'template' ? HTTPS_CATALOG : HTTPS_SERVER;
		} else {
			$store_url = $this->model_setting_setting->getSettingValue('config_ssl', $store_id);
		}

		$url = '';
		$url = $store_url . 'index.php?route=catalog/media/video&id=' . $this->request->post['id'];
		

		// cloudflare id
		$account_id = $this->config->get('media_cloudflare_account_id');

		// cloudflare token
		$token = $this->config->get('media_cloudflare_token');

		$data = array(
			'url' => $url
		);
		$data['meta'] = array(
			'name' => ($this->request->post['name'])
		);

		$curl = curl_init();

		curl_setopt_array($curl, [
		  CURLOPT_URL => "https://api.cloudflare.com/client/v4/accounts/$account_id/stream/copy",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_encode($data),
		  CURLOPT_COOKIE => "__cflb=0H28vgHxwvgAQtjUGU4vq74ZFe3sNVUZLnJNUqcYdkq; __cfruid=9e7dd8bf2910ccf3bafd8b120e8f3900c13a912a-1625728483",
		  CURLOPT_HTTPHEADER => [
			"Authorization: Bearer $token",
			"Content-Type: application/json"
		  ],
		]);
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		curl_close($curl);
		
		if ($err) {
			$json['error'] = 'failed to fetch videos'; 
		} else {
			$json = json_decode(json_encode($response),true);
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}