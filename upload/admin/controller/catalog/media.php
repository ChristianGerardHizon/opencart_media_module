<?php

class ControllerCatalogMedia extends Controller {
    
    public function autocomplete() {
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
				$allowed_types = array('mp3');

				foreach ($allowed_types as $type ) {
					if(strpos($original_file, $type) !== false) {
						$json[] = array(
							'download_id' => $result['download_id'],
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

	public function test() {
		$json = array();
		$this->load->model('catalog/media');
		$json['results']= $this->model_catalog_media->getVideo($video_id);
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function cloudflareAutocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/media');

			$filter_data = array(
				'search'       => $this->request->get['filter_name'],
				'limit'        => 5,
				'status'       => 'ready'
			);

			$results = $this->model_catalog_media->getVideos($filter_data);

			$json = $results;
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}