<?php

class Phanes_Marketing_Youtube {

	public static function init() {
		add_action( 'woocommerce_after_shop_loop', array( __CLASS__, 'add_carousel' ), 10 );
		add_action( 'woocommerce_after_single_product_summary', array( __CLASS__, 'add_carousel' ), 10 );
	}

	public static function add_carousel() {
		$options = get_option( 'phanes_marketing', array() );

		if ( ! isset( $options['enable_youtube'] ) || ! $options['enable_youtube'] ) {
			return;
		}

		if ( is_shop() ) {
			$ytvid_ids = array();
			woocommerce_product_loop_start( false );
			while ( have_posts() ) {
				the_post();
				$id = get_the_title();
				array_push( $ytvid_ids, self::get_by_string( str_replace( ' ', '+', get_the_title() ) ) ) ;
			}
			woocommerce_product_loop_end( false );

			if ( ! isset( $ytvid_ids ) ) {
				return;
			}

			echo '<div class="owl-carousel">';
			foreach ($ytvid_ids as $key => $ytvid_id) {
			echo '<div class="item">
					<a class="popup-youtube" href="https://www.youtube.com/watch?v='.$ytvid_id[0]['id']['videoId'].'">
					<img src="https://img.youtube.com/vi/'.$ytvid_id[0]['id']['videoId'].'/0.jpg"><i class="fa fa-2x fa-youtube-play" aria-hidden="true"></i></a>
				</div>';
				}
			echo '</div>';
		} elseif ( is_product() ) {
			$ytvid_ids = array();

			$id = get_the_title();
			array_push($ytvid_ids , self::get_by_string(str_replace(' ', '+', get_the_title()), 4 )) ;


			if(!isset($ytvid_ids[0]))
			return;

			echo '<h2>Youtube reviews</h2>';
			echo '<div class="owl-carousel" style="clear:both;margin-bottom:75px;">';
			foreach ($ytvid_ids[0] as $key => $ytvid_id) {
			echo '<div class="item">
					<a class="popup-youtube" href="https://www.youtube.com/watch?v='.$ytvid_id['id']['videoId'].'">
					<img src="https://img.youtube.com/vi/'.$ytvid_id['id']['videoId'].'/0.jpg"><i class="fa fa-2x fa-youtube-play" aria-hidden="true"></i></a>
				</div>';
				}
			echo '</div>';
		}
	}

	public static function get_by_string( $ytvid_bystring, $maxResults = false ) {
		$maxResult = ($maxResults)?$maxResults:1;  //AIzaSyClgOwZPoQfeNpHjnDVCrbahTyUtLJP0Y0
		$options = get_option( 'phanes_marketing', array() );
		$yt_setting_value = isset( $options['youtube_apikey'] ) ? $options['youtube_apikey'] : '';
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://www.googleapis.com/youtube/v3/search?part=id,snippet&maxResults={$maxResult}&type=video&key={$yt_setting_value}&q={$ytvid_bystring}",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));

		$response = json_decode(curl_exec($curl), true);

		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			return $response['items'];
		}
	}

}

Phanes_Marketing_Youtube::init();
