<?php

	setlocale( LC_CTYPE, "en_US.UTF-8" );

	error_reporting( E_ALL );
	ini_set( 'display_startup_errors', 1 );
	ini_set( 'display_errors', 1 );

	date_default_timezone_set( 'Europe/Moscow' );

	spl_autoload_register( function ( $class_name )
	{
		if( file_exists( realpath( __DIR__ ) . '/includes/' . strtolower( $class_name ) . '.class.php' ) )
		{
			require_once( realpath( __DIR__ ) . '/includes/' . strtolower( $class_name ) . '.class.php' );
		}
	});

	require_once realpath( __DIR__ ) . '/config.php';

	try
	{
		l('Start');

		$Soap = new Soap4me();
		$Soap->downloadNew();
	}
	catch ( Exception $e )
	{
		l( $e->getMessage() );
	}

	function l( $string )
	{
		echo $string . "\n";

		file_put_contents( SomeShit::$config['log_file'], '[' . date( 'Y/m/d H:i:s' ) . '] '."\n" . $string, FILE_APPEND  );
	}

	class SoapAuthExeption extends Exception{};