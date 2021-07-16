<?php
class ModelCatalogMedia extends Model {

    public function getPublicDownload($download_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE d.download_id = '" . (int)$download_id . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	//
	// fetch cloudflare videos
	//
	public function getVideos($params = null) {

		// cloudflare id
		$account_id = $this->config->get('media_cloudflare_account_id');

		// cloudflare token
		$token = $this->config->get('media_cloudflare_token');

		$query = '?kweel=1';
		
		if($params !== null) {
			if(isset($params['search'])) {
				$query = $query . "&search=" . $params['search'];
			}

			if(isset($params['limit'])) {
				$query = $query . "&limit=" . $params['limit'];
			}

			if(isset($params['status'])) {
				$query = $query . "&status=" . $params['status'];
			}
		}	

		$url = "https://api.cloudflare.com/client/v4/accounts/$account_id/stream" . $query;
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_POSTFIELDS => "",
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer $token"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$this->error['error'] = 'failed to fetch videos'; 
		} else {
			$video_list = array();

			if($response !== null) {
				$ret = json_decode($response, true);

				if($ret['result'] != null) {
					foreach($ret['result'] as $file) {
						$video_list[] = $this->formatVideosResponse($file);
					}
				}
				
			}

			return $video_list;
		}

	}

	public function getVideo($video_id) {

		// cloudflare id
		$account_id = $this->config->get('media_cloudflare_account_id');

		// cloudflare token
		$token = $this->config->get('media_cloudflare_token');

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.cloudflare.com/client/v4/accounts/$account_id/stream/$video_id",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_POSTFIELDS => "",
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer $token"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$this->error['error'] = 'failed to fetch videos'; 
		} else {
			if($response !== null) {
				$ret = json_decode($response, true);
				return $this->formatVideosResponse($ret['result']);
			}
		}

	}


	public function formatVideosResponse($file) {
		return array(
			'video_id'     			=> $file['uid'],
			'thumbnail'				=> $file['thumbnail'],
			'status_state' 			=> $file['status']['state'],
			'status_percentage' 	=> (isset($file['status']) ? $file['status']['pctComplete'] : 0),
			'duration' 				=> $file['duration'],
			'created'				=> $file['created'],
			'name'					=> $file['meta']['name'],
		);
	}

	public function getProductVideos($product_id) {
		$product_video_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_video WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_video_data[] = $result['video_id'];
		}

		return $product_video_data;
	}

}