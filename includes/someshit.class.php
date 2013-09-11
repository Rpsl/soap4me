<?php

	class SomeShit
	{
		static public $config;

		static public function curl( $url, $post = null )
		{

			sleep( mt_rand( 3, 6 ) );

			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_NOBODY, FALSE );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_REFERER,      'https://soap4.me/' );
			curl_setopt( $ch, CURLOPT_USERAGENT,    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36' );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

			curl_setopt( $ch, CURLOPT_COOKIEJAR,    self::$config[ 'cookie_file' ] );
			curl_setopt( $ch, CURLOPT_COOKIEFILE,   self::$config[ 'cookie_file' ] );
			curl_setopt( $ch, CURLOPT_COOKIE,       self::_makeCookie() );

			if( !empty( $post ) )
			{
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
				curl_setopt( $ch, CURLOPT_HTTPHEADER,
					array(
						'X-Requested-With' => 'XMLHttpRequest',
						'Content-Type'     => 'application/x-www-form-urlencoded'
					)
				);
			}

			$data     = curl_exec( $ch );
			$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			$error    = curl_error( $ch );

			curl_close( $ch );

			if( $httpCode == 200 )
			{
				return $data;
			}
			else
			{
				l( 'not normal http respoce code / ' . $url . ' / ' . $httpCode . ' / ' . $error );
			}

			return FALSE;
		}

		/**
		 * Я че-то не знаю других способов получить куку из файлов этого формата.
		 * @return string
		 */
		static private function _makeCookie()
		{
			if( !file_exists( self::$config['cookie_file'] ) )
			{
				return FALSE;
			}

			$data = file_get_contents( self::$config['cookie_file'] );

			preg_match_all( '~PHPSESSID	([a-z0-9]+)~', $data, $found );

			if( !empty( $found[1][0] ) )
			{
				return $found[1][0];
			}
			else
			{
				l( 'cookie values not found' );

				return FALSE;
			}
		}

	}
