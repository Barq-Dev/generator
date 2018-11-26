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
 			'submit' => [
 				'icon' => 'fa fa-search',
 			],
 			'slip-input'    => 3,
 			'modal'         => 'components.widget.card',
 			'select2-class' => 'select2-default',
 			'view_path'     => [
 			],
 			'title_format' => 'Pencarian %s',
 		],
 	],
 ];
