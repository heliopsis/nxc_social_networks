<?php
/**
 * @package nxcSocialNetworks
 * @class   nxcSocialNetworksPublishHandlerTwitter
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    21 Sep 2012
 **/

class nxcSocialNetworksPublishHandlerTwitter extends nxcSocialNetworksPublishHandler
{
	protected $name = 'Twitter';

	public function publish( eZContentObject $object, $message ) {
		$options = $this->getOptions();

		$messageLength = 140;
		$url = false;
		if(
			isset( $options['include_url'] )
			&& (bool) $options['include_url'] === true
		) {
			$url = $object->attribute( 'main_node' )->attribute( 'url_alias' );
			eZURI::transformURI( $url, true, 'full' );
			$messageLength = $messageLength - strlen( $url ) - 1;
		}

		if( class_exists( 'Normalizer' ) ) {
			$message = Normalizer::normalize( $message, Normalizer::FORM_C );
		}
		$message = mb_substr( $message, 0, $messageLength );

		if( $url ) {
			$message .= ' ' . $url;
		}

		$response = $this->getAPI()->post(
			'statuses/update',
			array( 'status' => $message )
		);

		if( isset( $response->errors ) ) {
            $message = array();

            foreach( $response->errors as $error )
            {
                $message[] = $error->message . ' => Code : ' . $error->code;
            }

			throw new Exception( implode( ', ', $message ) );
		}
	}

	protected function getAPI() {
		$OAuth2      = nxcSocialNetworksOAuth2::getInstanceByType( 'twitter' );
		$OAuth2Token = $OAuth2->getToken();

		return new TwitterOAuth(
			$OAuth2->appSettings['key'],
			$OAuth2->appSettings['secret'],
			$OAuth2Token->attribute( 'token' ),
			$OAuth2Token->attribute( 'secret' )
		);
	}
}
?>
