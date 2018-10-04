<?php
 return [
 	/*
	* semua class exception yang ada di list ini ketika terjadi error maka dia tidaka
	* akan diambil pesan messagenya melaikan di ganti dengan pesan error defaultnya
	*/
 	'dont_report_exception' => [
 	],
 	'view' => [
 		'namespace' => 't-component',
 		'search'    => [
 			'modal'     => 'components.widget.card',
 			'view_path' => [
 			],
 			'title_format' => 'Pencarian %s',
 		],
 	],
 ];
