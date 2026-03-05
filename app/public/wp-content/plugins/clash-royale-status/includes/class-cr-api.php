<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CR_API {
    const API_BASE = 'https://api.clashroyale.com/v1';

    protected $option_token_key;

    public function __construct( $option_token_key ) {
        $this->option_token_key = $option_token_key;
    }

    /**
     * @param string $endpoint like '/players/%23TAG'
     * @return array|WP_Error
     */
    public function get( $endpoint ) {
        $token = get_option( $this->option_token_key, '' ); //token ophalen
        if ( empty( $token ) ) { // Geen token ingesteld
            return new WP_Error( 'cr_no_token', __( 'Geen API token gevonden. Stel je token in op de instellingenpagina.', 'clash-royale-status' ) );
        }

        $url  = self::API_BASE . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ),
            'timeout' => 10,
        );

        $response = wp_remote_get( $url, $args ); 
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'cr_http_error', sprintf( __( 'Fout bij het ophalen van data: %s', 'clash-royale-status' ), $response->get_error_message() ) );
        } // niet kapot gaan

        $code = wp_remote_retrieve_response_code( $response );  // HTTP status code ophalen 
        $body = wp_remote_retrieve_body( $response );
        if ( $code < 200 || $code >= 300 ) { // Als de API geen succesvolle status teruggeeft
            return new WP_Error( 'cr_api_error', sprintf( __( 'De Clash Royale API gaf een foutmelding (HTTP %d). Controleer de speler tag en je token.', 'clash-royale-status' ), intval( $code ) ) );
        }

        $data = json_decode( $body, true ); // JSON omzetten naar een PHP array
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'cr_json_error', __( 'Kon de API response niet als JSON lezen.', 'clash-royale-status' ) );
        }

        return $data;
    }
}
