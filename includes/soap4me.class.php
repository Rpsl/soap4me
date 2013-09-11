<?php

	class Soap4me
	{
		public function __construct()
		{
			if( !$this->checkLogin() )
			{
				// @todo
				throw new SoapAuthExeption('Auth');
			}
		}

		public function downloadNew()
		{

			$this->findEpisodes();
		}

		private function findEpisodes()
		{
			$html = SomeShit::curl('https://soap4.me/new/my/unwatched/');

			$res = phpQuery::newDocumentHTML($html, $charset = 'utf-8');

			$token = $res->find('#token')->attr('data:token');

			$eps = $res->find('li.ep');

			$episodes = array();

			foreach( $eps as $ep )
			{
				$serial = trim( pq( $ep )->find('.soap')->text() );
				$number = trim( pq( $ep )->find('.nums')->text() );
				$name   = trim( pq( $ep )->find('.en')->text() );
				$quality= trim( pq( $ep )->find('.quality')->text() );
				$sound  = trim( pq( $ep )->find('.translate')->text() );
				$season = trim( pq( $ep )->find( '.play' )->attr( 'data:season' ) );


				$hash = trim( pq( $ep )->find( '.play' )->attr( 'data:hash' ) );
				$eid  = trim( pq( $ep )->find( '.play' )->attr( 'data:eid' ) );
				$sid  = trim( pq( $ep )->find( '.play' )->attr( 'data:sid' ) );

				$episodes[ $serial ][ $number ][ $quality ] = array(
					'serial'    => $serial,
					'season'    => $season,
					'number'    => $number,
					'name'      => $name,
					'quality'   => $quality,
					'sound'     => $sound,

					'eid'       => $eid,
					'hash'      => $hash,
					'sid'       => $sid,
					'token'     => $token,

					'path'      => SomeShit::$config['download_dir'] . '/' . $serial .'/Season '. $season.'/'. $number .' '.$name.'.mp4'
				);
			}

			$episodes = $this->filter( $episodes );

			$this->download( $episodes );

		}

		private function download( array $eps )
		{
			foreach( $eps as $serial )
			{
				foreach( $serial as $episod )
				{
					$episod = reset( $episod );

					$callback = array(
						'do'    => 'load',
						'eid'   => $episod['eid'],
						'hash'  => $this->getHash( $episod['token'], $episod['eid'], $episod['sid'], $episod['hash'] ),
						'token' => $episod['token'],
						'what'  => 'player'
					);


					$res = SomeShit::curl('https://soap4.me/callback/', $callback );
					$res = json_decode( $res, true );

					if( !empty( $res['ok'] ) )
					{
						l("Start download " . $this->getTextEpisodName( $episod ) );

						if( file_exists( $episod['path'] ) )
						{
							unlink( $episod['path'] );
						}

						$url = sprintf(
							'https://%s.soap4.me/%s/%s/%s/',
							$res['server'],
							$episod['token'],
							$episod['eid'],
							$this->getHash( $episod['token'], $episod['eid'], $episod['sid'], $episod['hash'] )
						);

						$this->checkFolder( $episod['path'] );

						$wget = `which wget`;
						exec( trim( $wget ) . ' --no-check-certificate --random-wait -t 10 --retry-connrefused  -O ' . escapeshellarg( $episod['path'] ) . ' ' . $url, $output, $retvar );

						l( 'downloading finished, wget exit code: ' . $retvar );

						if( $retvar === 0 )
						{
							l( '[!] downloading is ok / ' . $episod['name'] . ' / ' . $episod['path'] );

							$this->markWatched( $episod );
							$this->emailed( $episod );
						}
						else
						{
							l( 'downloading is broken, removed ' . $episod['path'] );
							shell_exec( 'rm -f ' . escapeshellarg( $episod['path'] ) );
						}
					}
				}
			}
		}

		private function getTextEpisodName( $episode )
		{
			return sprintf(' %s / Season %s / %s ', $episode['serial'], $episode['season'], $episode['number'] .' '. $episode['name'] );
		}

		private function emailed( $episod )
		{


			$data = array(
				'from'    => 'Turbofilm downloader <turboload@'. SomeShit::$config['mailgun']['domain'].'>',
				'to'      => implode(', ', SomeShit::$config['email']),
				'subject' => 'TurboLoader | ' . $episod['serial'] . ' | ' . $episod['name'],
				'text'    => 'Серия ' . $this->getTextEpisodName( $episod )  . ' закачана.',
				'html'    => '<html><p>Серия ' . $this->getTextEpisodName( $episod ) . ' закачана.</p><p>&nbsp;</p><p>' . $episod['path'] . '</p></html>',
			);

			$data = http_build_query( $data );

			$opts = array(
				'http' => array(
					'method'  => 'POST',

					'header'  =>
					"Content-type: application/x-www-form-urlencoded\r\n" .
					"Content-Length: " . strlen( $data ) . "\r\n" .
					"Authorization: Basic " . base64_encode( SomeShit::$config['mailgun']['api-key'] ) . "\r\n",

					'content' => $data
				)
			);

			$stream = stream_context_create( $opts );

			file_get_contents('https://api.mailgun.net/v2/'.SomeShit::$config['mailgun']['domain'].'/messages', false, $stream );
		}

		private function markWatched( array $epidsode )
		{
			$callback = array(
				'eid'   => $epidsode['eid'],
				'token' => $epidsode['token'],
				'what'  => 'mark_watched'
			);

			SomeShit::curl('https://soap4.me/callback/', $callback );
		}

		private function checkFolder( $path )
		{
			$path = dirname( $path );

			if( !file_exists( $path ) )
			{
				mkdir( $path, 0777, true );
			}
		}

		private function  getHash( $token, $eid, $sid, $hash )
		{
			return md5( $token . $eid . $sid . $hash );
		}

		private function filter( $episodes )
		{
			// @todo Наверно сюда надо захуячить всякие проверки типо языка, озвучки, но это все потом

			foreach( $episodes as $serial_name => $eps )
			{
				foreach( $eps as $ep => $episod )
				{
					if( isset( $episod['HD'] ) && isset( $episod['SD'] ) )
					{
						unset( $episodes[ $serial_name ][ $ep ]['SD'] );
					}
				}
			}

			return $episodes;
		}

		/**
		 * Проверка доступности авторизации и авторизация при необходимости.
		 * Метод полурекурсивный.
		 *
		 * @param   bool $reload
		 * @return  bool
		 */
		private function checkLogin( $reload = FALSE )
		{
			$res = SomeShit::curl( 'https://soap4.me/' );

			if( !empty( $res ) && preg_match( '~(вход на сайт)~usi', $res ) )
			{
				SomeShit::curl( 'https://soap4.me/login/',
					array(
						'login'    => SomeShit::$config['login'],
						'password' => SomeShit::$config['password'],
					)
				);

				if( !$reload )
				{
					return self::checkLogin( TRUE );
				}

				return FALSE;
			}

			return TRUE;
		}

	}

