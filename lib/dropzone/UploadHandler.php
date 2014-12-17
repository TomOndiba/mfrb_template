<?php

class UploadHandler {

	/**
	 * Stores normalized values from the $_FILES global
	 * @var array
	 */
	private static $uploads;

	/**
	 * Stores file entities that were created from uploaded files
	 * @var array
	 */
	private static $files;

	/**
	 * Additional attributes to be used when creating file entities from uploaded files
	 * Can specify such information as subtype, owner_guid, container_guid as well as any other metadata to attach
	 * @var array
	 */
	static $attributes;

	/**
	 * Config information to be used
	 * @uses $config['icon_sizes']  Icon sizes to create after upload
	 * @uses $config['filestore_prefix']		Prefix on the filestore. Default is 'file/'
	 * @uses $config['icon_filestore_prefix'] Icon prefix on the filestore. Default is 'icons/'. Entity guid is appended automatically
	 * @var array
	 */
	static $config;

	/**
	 * Normalize the $_FILES global when class is constructed
	 */
	function __construct() {
		if (!isset(self::$uploads)) {
			self::uploadFactory($_FILES);
		}
	}

	/**
	 * Create new file entities
	 *
	 * @param string $input			Name of the file input
	 * @param array $attributes		Key value pairs, such as subtype, owner_guid, metadata.
	 * @param array $config			Additional config
	 * @return array	An array of file entities created
	 */
	public function makeFiles($input, array $attributes = array(), array $config = array()) {
		if (!isset(self::$files)) {
			self::$files = array();
		}
		if (!array_key_exists($input, self::$files)) {
			self::$attributes = $attributes;
			self::$config = $config;
			self::$files[$input] = self::entityFactory($input);
		}

		return self::$files[$input];
	}

	/**
	 * Static counterpart of makeFiles, but returns data for processed uploads
	 *
	 * @param string $input			Name of the file input
	 * @param array $attributes		Key value pairs, such as subtype, owner_guid, metadata.
	 * @param array $config			Additional config
	 * @return array	An array of file entities created
	 */
	public static function handle($input, array $attributes = array(), array $config = array()) {

		$handler = new UploadHandler;
		$handler->makeFiles($input, $attributes, $config);

		return self::$uploads[$input];
	}

	/**
	 * Convert file uploads into an object and get any errors
	 * @param array $_files Normalized $_FILES global
	 * @return array
	 */
	protected static function uploadFactory($_files = array()) {

		$_files = self::normalize($_files);

		foreach ($_files as $input => $uploads) {
			foreach ($uploads as $upload) {
				$object = new stdClass();
				if (is_array($upload)) {
					foreach ($upload as $key => $value) {
						$object->$key = $value;
					}

					$object->error = self::getError($object->error);
					if (!$object->error) {
						$object->filesize = $upload['size'];
						$object->mimetype = self::detectMimeType($upload);
						$object->simpletype = self::parseSimpletype($object->mimetype);
						$object->path = $upload['tmp_name'];
					}
				}
				self::$uploads[$input][] = $object;
			}
		}

		return self::$uploads;
	}

	/**
	 * Create new entities from file uploads
	 * @param string $input  Name of the file input
	 */
	protected static function entityFactory($input) {

		if (!isset(self::$config['filestore_prefix'])) {
			$prefix = "file/";
		}

		$uploads = self::$uploads[$input];
		$handled_uploads = array();
		$entities = array();

		foreach ($uploads as $key => $upload) {
			if ($upload->error) {
				$handled_uploads[] = $upload;
				continue;
			}


			$filehandler = new ElggFile();
			if (is_array(self::$attributes)) {
				foreach (self::$attributes as $key => $value) {
					$filehandler->$key = $value;
				}
			}

			$filestorename = elgg_strtolower(time() . $upload->name);
			$filehandler->setFilename($prefix . $filestorename);

			$filehandler->title = $upload->name;
			$filehandler->originalfilename = $upload->name;
			$filehandler->filesize = $upload->size;
			$filehandler->mimetype = $upload->mimetype;
			$filehandler->simpletype = $upload->simpletype;

			$filehandler->open('write');
			$filehandler->close();

			move_uploaded_file($upload->path, $filehandler->getFilenameOnFilestore());

			if ($filehandler->save()) {
				$upload->guid = $filehandler->getGUID();
				$upload->file = $filehandler;
				$upload->not_attached = true;

				/*if ($filehandler->simpletype == "image") {
					require_once(dirname(__FILE__) . '/IconHandler.php');
					IconHandler::makeIcons($filehandler);
				}*/
				// if image, we need to create thumbnails (this should be moved into a function)
				if ($upload->guid && $filehandler->simpletype == "image") {
					self::setImageThumbnails($filehandler);
				} else if ($upload->guid && $filehandler->mimetype == "application/pdf") {
					self::setPdfThumbnails($filehandler);

				} else if ($filehandler->icontime) {
					// if it is not an image, we do not need thumbnails
					unset($filehandler->icontime);

					$thumb = new ElggFile();

					$thumb->setFilename($prefix . "thumb" . $filestorename);
					$thumb->delete();
					unset($filehandler->thumbnail);

					$thumb->setFilename($prefix . "smallthumb" . $filestorename);
					$thumb->delete();
					unset($filehandler->smallthumb);

					$thumb->setFilename($prefix . "largethumb" . $filestorename);
					$thumb->delete();
					unset($filehandler->largethumb);
				}


				$entities[] = $filehandler;
			} else {
				$upload->error = elgg_echo('upload:error:unknown');
			}

			$handled_uploads[] = $upload;
		}

		self::$uploads[$input] = $handled_uploads;
		self::$files[$input] = $entities;
	}

	/**
	 * create Thumbnails for an image
	 * @param object $file
	 */
	protected static function setImageThumbnails($file) {

		$file->icontime = time();
		$prefix = "file/";

		// use same filename on the disk - ensures thumbnails are overwritten
		$filestorename = $file->getFilename();
		$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));

		$thumb = new ElggFile();
		$thumb->setMimeType($file->mimetype);

		$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
		if ($thumbnail) {
			$thumb->setFilename($prefix."thumb".$filestorename);
			$thumb->open('write');
			$thumb->write($thumbnail);
			$thumb->close();
			$file->thumbnail = $prefix."thumb".$filestorename;
			unset($thumbnail);
		}

		$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
		if ($thumbsmall) {
			$thumb->setFilename($prefix."smallthumb".$filestorename);
			$thumb->open('write');
			$thumb->write($thumbsmall);
			$thumb->close();
			$file->smallthumb = $prefix."smallthumb".$filestorename;
			unset($thumbsmall);
		}

		$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
		if ($thumblarge) {
			$thumb->setFilename($prefix."largethumb".$filestorename);
			$thumb->open('write');
			$thumb->write($thumblarge);
			$thumb->close();
			$file->largethumb = $prefix."largethumb".$filestorename;
			unset($thumblarge);
		}
	}

	/**
	 * create Thumbnails for an image
	 * @param object $file
	 */
	protected static function setPdfThumbnails($file) {

		if (is_readable($file->getFilenameOnFilestore())) {
			$file->icontime = time();
			$prefix = "file/";

			// use same filename on the disk - ensures thumbnails are overwritten
			$filestorename = $file->getFilename();
			$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));

			$thumb = new ElggFile();
			$thumb->setMimeType('image/jpeg');

			$im = new Imagick();
			$im->setResolution(300, 300);
			$im->readImage($file->getFilenameOnFilestore().'[0]');
			$im->setCompression(Imagick::COMPRESSION_JPEG);
			$im->setCompressionQuality(90);
			$im->setBackgroundColor('white');
			$im = $im->flattenImages();
			$im->setImageFormat('jpeg');
			$im->thumbnailImage(600, 0);

			if ($im) {

				$thumb->setFilename($prefix."largethumb".$filestorename.'.jpg');
				$thumb->open('write');
				$thumb->write($im);
				$thumbs_path = $thumb->getFilenameOnFilestore();
				$thumb->close();
				$file->largethumb = $prefix."largethumb".$filestorename.'.jpg';
				$im->clear();
				$im->destroy();
				unset($im);

				$thumbnail = get_resized_image_from_existing_file($thumbs_path, 60, 60, true);
				if ($thumbnail) {
					$thumb->setFilename($prefix."thumb".$filestorename.'.jpg');
					$thumb->open('write');
					$thumb->write($thumbnail);
					$thumb->close();
					$file->thumbnail = $prefix."thumb".$filestorename.'.jpg';
					unset($thumbnail);
				}

				$thumbsmall = get_resized_image_from_existing_file($thumbs_path, 153, 153, true);
				if ($thumbsmall) {
					$thumb->setFilename($prefix."smallthumb".$filestorename.'.jpg');
					$thumb->open('write');
					$thumb->write($thumbsmall);
					$thumb->close();
					$file->smallthumb = $prefix."smallthumb".$filestorename.'.jpg';
					unset($thumbsmall);
				}
			}

		}
	}

	/**
	 * Nomalizes $_FILES global
	 * @param array $_files
	 * @param boolean $top
	 * @return array
	 */
	protected static function normalize(array $_files = array(), $top = true) {

		$files = array();
		foreach ($_files as $name => $file) {
			if ($top) {
				$sub_name = $file['name'];
			} else {
				$sub_name = $name;
			}
			if (is_array($sub_name)) {
				foreach (array_keys($sub_name) as $key) {
					$files[$name][$key] = array(
						'name' => $file['name'][$key],
						'type' => $file['type'][$key],
						'tmp_name' => $file['tmp_name'][$key],
						'error' => $file['error'][$key],
						'size' => $file['size'][$key],
					);
					$files[$name] = self::normalize($files[$name], FALSE);
				}
			} else {
				$files[$name] = $file;
			}
		}

		return $files;
	}

	/**
	 * Get readable error status from $_FILES global
	 * @param int $code
	 * @return boolean
	 */
	protected static function getError($code) {

		switch ($code) {
			case UPLOAD_ERR_OK:
				return false;
			case UPLOAD_ERR_NO_FILE:
				return elgg_echo('upload:error:no_file');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return elgg_echo('upload:error:file_size');
			default:
				return elgg_echo('upload:error:unknown');
		}
	}

	/**
	 * Detect mimetype of the uploaded file
	 * @param object $file
	 * @return string
	 */
	protected static function detectMimeType($file) {
		$mimetype = ElggFile::detectMimeType($file['tmp_name'], $file['type']);

		// Hack for Microsoft zipped formats
		$info = pathinfo($file['name']);
		$office_formats = array('docx', 'xlsx', 'pptx');
		if ($mimetype == "application/zip" && in_array($info['extension'], $office_formats)) {
			switch ($info['extension']) {
				case 'docx':
					$mimetype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
					break;
				case 'xlsx':
					$mimetype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
					break;
				case 'pptx':
					$mimetype = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
					break;
			}
		}

		// Check for bad ppt detection
		if ($mimetype == "application/vnd.ms-office" && $info['extension'] == "ppt") {
			$mimetype = "application/vnd.ms-powerpoint";
		}

		return $mimetype;
	}

	/**
	 * Get a simple type such as 'image'
	 * @param string $mimetype
	 * @return string
	 */
	protected static function parseSimpleType($mimetype) {

		switch ($mimetype) {
			case "application/msword":
			case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
				return "document";
			case "application/pdf":
				return "document";
			case "application/ogg":
				return "audio";
		}

		if (substr_count($mimetype, 'text/')) {
			return "document";
		}

		if (substr_count($mimetype, 'audio/')) {
			return "audio";
		}

		if (substr_count($mimetype, 'image/')) {
			return "image";
		}

		if (substr_count($mimetype, 'video/')) {
			return "video";
		}

		if (substr_count($mimetype, 'opendocument')) {
			return "document";
		}

		return "general";
	}

}
