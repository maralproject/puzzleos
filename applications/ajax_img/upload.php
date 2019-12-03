<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$l = new Language;

if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) die();

	if ($_FILES["file"]["size"] > php_max_upload_size()) {
		json_out([
			"success" => false,
			"reason" => $l->get("TOO_BIG")
		]);
	}

	try {
		$compressedImage = ImageUploader::compressImage($_FILES['file']['tmp_name']);
	} catch (Exception $e) {
		json_out([
			"success" => false,
			"reason" => $l->get("NOT_VALID")
		]);
	}

	$key = $_POST["key"];
	$id = "$key." . session_id();
	$_SESSION["ImageUploader"][$key] = $id;

	if (UserData::store($id, $compressedImage, "thumb")) {
		json_out([
			"success" => true
		]);
	} else {
		json_out([
			"success" => false,
			"reason" => $l->get("ERROR_UPLOAD")
		]);
	}

	json_out([
		"success" => false,
		"reason" => $l->get("ERROR_UNKNOWN")
	]);
}

return false;
