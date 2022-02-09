<?php
 return [
 	/*
	* semua class exception yang ada di list ini ketika terjadi error maka dia tidaka
	* akan diambil pesan messagenya melaikan di ganti dengan pesan error defaultnya
	*/
 	'dont_report_exception' => [
 	],
 	/*
  * pengaturan untuk mengatur format response ketika
  * selesai melakukan create, update atau delete
	*/
 	'message_format' => [
 		'true'  => '%s berhasil %s',
 		'false' => '%s gagal %s',
 	],
 	/*
	* mengatur pesan akhiran dari setiap tindakan yang dilakukan
	*/
 	'translate_action_method' => [
 		'create' => 'ditambah',
 		'update' => 'diubah',
 		'delete' => 'dihapus',
 	],
 	'url' => 'routeName',
 ];
