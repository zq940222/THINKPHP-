<?php
	echo json_encode(array("status"=>0, "info"=> "系统错误:".strip_tags($e['message']),"data"=>array() ));