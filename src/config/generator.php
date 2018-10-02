<?php
 return [
 	/*
	* semua class exception yang ada di list ini ketika terjadi error maka dia tidaka
	* akan diambil pesan messagenya melaikan di ganti dengan pesan error defaultnya
	*/
 	'dont_report_exception' => [
 	],
 	'message_format' => [
 		'true'  => '% berhasil %',
 		'false' => '% gagal %',
 	],
 ];
