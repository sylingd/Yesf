<?php
use yesf\Constant;
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Error</title>
	<style>
	body {
		margin: 0;
	}
	.base {
		background: #edecea;
		padding: 100px 80px;
		margin-bottom: 20px;
	}
	.base .message {
		font-weight: 300;
		font-size: 35px;
		line-height: 43px;
		margin-bottom: 15px;
	}
	.base .description {
		font-weight: 300;
		font-size: 18px;
	}
	.request {
      	padding: 50px 80px;
    }
    .request .title {
      	text-transform: uppercase;
      	font-size: 18px;
      	letter-spacing: 1px;
      	padding: 0 5px 5px 5px;
      	margin-bottom: 15px;
    }
    .request table {
      	width: 100%;
      	border-collapse: collapse;
      	margin-bottom: 80px;
    }
    .request table td {
      	padding: 8px 6px;
      	font-size: 13px;
      	color: #455275;
      	border-bottom: 1px solid #e8e8e8;
      	word-break: break-word;
    }
    .request table td.name {
      	font-weight: 600;
      	color: #999;
      	width: 30%;
      	text-transform: uppercase;
    }
	</style>
</head>
<body>
	<div class="base">
		<div class="message">404 Not Found</div>
		<div class="description">
			<?php
			switch ($code) {
				case Constant::ROUTER_ERR_ACTION:
					echo 'ROUTER_ERR_ACTION';
					break;
				case Constant::ROUTER_ERR_CONTROLLER:
					echo 'ROUTER_ERR_CONTROLLER';
					break;
				case Constant::ROUTER_ERR_MODULE:
					echo 'ROUTER_ERR_MODULE';
					break;
			}
			?>
		</div>
	</div>
	<div class="request">
		<div class="title">Request</div>
		<table>
			<tr><td class="name">module</td><td><?=htmlspecialchars($module)?></td></tr>
			<tr><td class="name">controller</td><td><?=htmlspecialchars($controller)?></td></tr>
			<tr><td class="name">action</td><td><?=htmlspecialchars($action)?></td></tr>
			<tr><td class="name">request_uri</td><td><?=htmlspecialchars($req->request_uri)?></td></tr>
			<tr><td class="name">extension</td><td><?=htmlspecialchars($req->extension)?></td></tr>
			<tr><td class="name">param</td><td><?=htmlspecialchars(var_export($req->param, TRUE))?></td></tr>
		</table>
		<div class="title">Server</div>
		<table>
			<?php foreach ($req->server as $k => $v) { ?>
			<tr><td class="name"><?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
			<?php } ?>
		</table>
		<div class="title">Header</div>
		<table>
			<?php foreach ($req->header as $k => $v) { ?>
			<tr><td class="name"><?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
			<?php } ?>
		</table>
		<?php if (is_array($req->get)) { ?>
		<div class="title">Get</div>
		<table>
			<?php foreach ($req->get as $k => $v) { ?>
			<tr><td class="name"><?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
			<?php } ?>
		</table>
		<?php } ?>
		<?php if (is_array($req->post)) { ?>
		<div class="title">Post</div>
		<table>
			<?php foreach ($req->post as $k => $v) { ?>
			<tr><td class="name"><?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
			<?php } ?>
		</table>
		<?php } ?>
		<?php if (is_array($req->cookie)) { ?>
		<div class="title">Cookie</div>
		<table>
			<?php foreach ($req->cookie as $k => $v) { ?>
			<tr><td class="name"><?=htmlspecialchars($k)?></td><td><?=htmlspecialchars($v)?></td></tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>
</body>
</html>