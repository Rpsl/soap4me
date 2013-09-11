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

	$pid_file = realpath( __DIR__ ) . '/download.pid';
	$pid      = getmypid();

	if( !$pid )
	{
		// @todo что тут делать ?
		die();
	}

	// Проверяем что у нас не просто есть пид, а что такой процесс действительно живет.
	if( file_exists( $pid_file ) )
	{
		$check_pid = trim( file_get_contents( $pid_file ) );

		// @todo В windows рабоатть не будеь
		if( !file_exists( "/proc/$check_pid" ) )
		{
			@unlink( $pid_file );
		}
		else
		{
			die();
		}

	}

	shell_exec( 'touch ' . $pid_file );
	shell_exec( "echo '$pid' > " . $pid_file );


	require_once realpath( __DIR__ ) . '/config.php';

	try
	{
		l('Start');

		$Soap = new Soap4me();
		$Soap->downloadNew();

		shell_exec( 'chmod -R 0777 ' . SomeShit::$config[ 'download_dir' ] );

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