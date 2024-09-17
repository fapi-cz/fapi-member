<?php declare(strict_types=1);

namespace FapiMember\Utils;

final class Debugger
{
	public static function consoleLog(mixed $data): void
	{
		$json = json_encode($data);

		if ($json === false) {
			$json =  'JSON encoding error: ' . json_last_error_msg();
		}

		echo "<script>
		console.log(" . $json . ")
		</script>";
	}

}
