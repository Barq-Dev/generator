<?php

return [
	/*
  * pengaturan untuk mengatur format response ketika
  * selesai melakukan create, update atau delete
	*/
	'message_format' => [
		'true'  => ':Title berhasil :action',
		'false' => ':Title gagal :action',
	],
	/*
 * mengatur pesan akhiran dari setiap tindakan yang dilakukan
 */
	'translate_action_method' => [
		'create'  => 'ditambah',
		'update'  => 'diubah',
		'delete'  => 'dihapus',
		'default' => 'dilakukan',
	],
	'view' => [
		'search' => [
			'text' => 'Cari',
		],
	],
];
