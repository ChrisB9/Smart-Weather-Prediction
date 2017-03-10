<?php

namespace cbenco\Routes;

class RoutesHelper
{
	public static function getHttpFormData() {
		$put_data = (object)[];
		$input = file_get_contents('php://input');
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
		if (false !== strpos($input, "WebKitFormBoundary")) {
			$input = self::parseFormData($input);
		}
		if ($matches) {
			$boundary = $matches[1];
			$a_blocks = preg_split("/-+$boundary/", $input);
			array_pop($a_blocks);
		} else {
			parse_str($input, $a_blocks);
		}
		foreach ($a_blocks as $id => $block) {
			if (empty($block)) continue;
			if (strpos($block, 'application/octet-stream') !== FALSE) {
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			}
			else {
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
			}
			if ($matches) {
			    $put_data->{$matches[1]} = $matches[2];
			} else {
			    $put_data->{$id} = $block;
			}
		}
		return $put_data;
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