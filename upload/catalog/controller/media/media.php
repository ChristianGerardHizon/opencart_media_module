<?php

class ControllerMediaMedia extends Controller {
    
    public function audio() {

		$this->load->model('media/media');

		if (isset($this->request->get['id'])) {
			$download_id = $this->request->get['id'];
		} else {
			$download_id = 0;
		}

		$download_info = $this->model_media_media->getPublicDownload($download_id);

		if ($download_info) {
			$file = DIR_DOWNLOAD . $download_info['filename'];
			$mask = basename($download_info['mask']);

			if (!headers_sent()) {
				if (file_exists($file)) {
					$mime_type = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
					header('Content-type: {$mime_type}');
					header("Content-Transfer-Encoding: chunked");
					header('Accept-Ranges: bytes');
					header('Content-length: ' . filesize($file));
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('X-Pad: avoid browser bug');
					header('Cache-Control: no-cache');
					readfile($file);
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			var_dump('file not found');
		}
	}
}