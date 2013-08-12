<?php?><html>
<head><title>Mark's wake-on-lan and ping interface</title></head>
<body>
<h1>Wake-on-lan</h1>
<link rel="stylesheet" href="style.css"/>
<?
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: 0");

	$machines =
		array(
			"oven_rpi"	=> "5404063CBC14",
		);

	$param = "";
	$params = "";
	$pmac = "";
	$pname = "";

#
#	TODO:
#		Implement UPnP,
#		"PING" command,
#		input sanitisation,
#		external password,
#		secure authentication
#
#	if (isset($_REQUEST["action"]) && $) {

		if (isset($_REQUEST["machine"])) {
			$param = $_REQUEST["machine"];
			$params = split(";", $param);
			$pname = $params[0];
			$pwho = $params[1];
		}

	if (count($params) == 2) {
		if ($_REQUEST["password"] == "password-here") {
			$message = "Command dispatched to $pname ($pwho)";
			$mac = $pwho;
			$packet = "";
			$port = 7;
			/* Packet geometry */
			$headersize = 6;
			$macsize = 6;
			$macrepeat = 16;
			/* Header */
			$packet .= str_repeat("FF", $headersize);
			/* Payload */
			$packet .= str_repeat($mac, $macrepeat);
			/* Send */
			$data = hex2bin($packet);
			$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
			socket_sendto($sock, $data, strlen($data), 0, '255.255.255.255', $port);
		}
		else {
			$message = "Incorrect password";
		}
	}
	else {
		$message = "";
	}

?>
<?
	if (strlen($message)) {
?><pre>Message: <?=htmlspecialchars($message)?></pre>
<?
	}
?><form action="/" method="post">
<input type="hidden" name="action" value="wake"/>
<table>
<tr>
	<td>Machine:</td>
	<td>
		<select name="machine" style="width:100%;">
			<optgroup label="Fixed targets"/>
<?
		foreach ($machines as $name_ => $mac_) {
			$mac = strtoupper(str_replace(':', '', htmlspecialchars($mac_)));
			$name = htmlspecialchars($name_);
?>			<option value="<?=$name.';'.$mac?>"<?=($pname==$name)?" selected=\"yes\"":""?>><?=$name?> (<?=$mac?>)</option>
<?
		}
?>			<optgroup label="Detected targets"/>
<?
$macs = fopen('/etc/macs/macs.db', 'r');
if ($macs) {
	while (($data = fgetcsv($macs, 0, "\t")) !== false)
		if (count($data) >= 2) {
			$mac = strtoupper(str_replace(':', '', htmlspecialchars($data[1])));
			$name = htmlspecialchars($data[2]);
?>			<option value="<?=$name.';'.$mac?>"><?=$name?> (<?=$mac?>)</option>
<?
		}
	fclose($macs);
}
?>		</secect>
	</td>
</tr>
<tr>
	<td>Password:</td><td><input type="password" name="password" value=""/></td>
</tr>
<tr>
	<td>&nbsp;</td><td><input type="submit" value="Wake"/></td>
</tr>
</table>
</form>
<h1>Ping</h1>
<form action="/" method="post">
<input type="hidden" name="action" value="ping"/>
<table>
<tr>
	<td>Host:</td>
	<td>
		<select>
<?
$macs = fopen('/etc/macs/macs.db', 'r');
if ($macs) {
	while (($data = fgetcsv($macs, 0, "\t")) !== false)
		if (count($data) >= 2) {
			$mac = strtoupper(str_replace(':', '', htmlspecialchars($data[1])));
			$name = htmlspecialchars($data[2]);
			$addr = htmlspecialchars($data[0]);
?>			<option value="<?=$name.';'.$addr?>"<?=($pname==$name)?" selected=\"yes\"":""?>><?=$name?> (<?=$addr?>)</option>
<?
		}
	fclose($macs);
}
?>		</select>
	</td>
</tr>
<tr>
	<td>&nbsp;</td><td><input type="submit" value="Ping"/></td>
</tr>
</table>
</form>
</body>
</html>
