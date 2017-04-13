<?php

namespace cbenco\Routes;

class RoutesHelper
{
	protected $phpInput;
	protected $contentType;
	private $formData;

	public function __construct() {
		$this->phpInput = $this->getPHPInput();
		$this->contentType = $this->getServerContentType();
		$this->formData = $this->getHttpFormData();
	}

	public function getPHPInput() {
		return file_get_contents('php://input');
	}

	public function getServerContentType() {
		return $_SERVER['CONTENT_TYPE'];
	}

	public function setPHPInput(string $phpInput) {
		$this->phpInput = $phpInput;
	}

	public function setServerContentType(string $contentType) {
		$this->contentType = $contentType;
	}

	public function getFormDataArray(array $inputKeys) : array {
		$result = [];
		foreach ($inputKeys as $key) {
    		if (isset($this->formData->{$key})) {
    			$result[$key] = $this->formData->{$key};
    		}
    	}
    	return $result;
	}

	public function getHttpFormData() {
		$putData = (object)[];
		preg_match('/boundary=(.*)$/', $this->contentType, $matches);
		if (false !== strpos($this->phpInput, "WebKitFormBoundary")) {
			$this->phpInput = $this->parseFormData($this->phpInput);
		}
		if ($matches) {
			$boundary = $matches[1];
			$aBlocks = preg_split("/-+$boundary/", $this->phpInput);
			array_pop($aBlocks);
		} else {
			parse_str($this->phpInput, $aBlocks);
		}
		foreach ($aBlocks as $id => $block) {
			if (empty($block)) continue;
			if (strpos($block, 'application/octet-stream') !== FALSE) {
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			}
			else {
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
			}
			if ($matches) {
			    $putData->{$matches[1]} = $matches[2];
			} else {
			    $putData->{$id} = $block;
			}
		}
		return $putData;
	}

	private static function parseFormData(string $input): string {
		$input = preg_replace(
			"/-{6}WebKitFormBoundary[a-zA-Z0-9]+|\sContent-Disposition:\sform-data;\sname=|[\s\v]/",
			" ",
			$input
		);
		$output = [];
		$result = [];
		preg_match_all("/(\"[a-zA-Z0-9\.]+\"[\s]*[a-zA-Z0-9\.]+)|(\"[a-zA-Z0-9\.]+\"[\s]*\{(.*)})/", $input, $output);
		foreach($output[0] as $line) {
			$line = preg_replace("/\"/", "", $line, 2);
			$line = preg_replace("/[\s]+/", "=", $line, 1);
			array_push($result, $line);
		}
		return implode("&", $result);
	}
}