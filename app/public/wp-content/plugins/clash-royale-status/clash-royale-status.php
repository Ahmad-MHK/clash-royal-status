<?php
/**
 * Plugin Name: Clash Royale Player Status
 * Plugin URI:  https://example.com
 * Description: Zoek Clash Royale spelers en toon player info, upcoming chests en battle log via de officiële Clash Royale API.
 * Version:     1.0.0
 * Author:      Ahmad Mahouk
 * Text Domain: clash-royale-status
 *
 * Belangrijk:
 * - Je hebt een API token nodig van https://developer.clashroyale.com
 * - Vul deze in op de instellingenpagina van deze plug-in.
 */

if ( ! defined( 'ABSPATH' ) ) {
    // Voorkomt dat iemand dit bestand direct aanroept via de browser.
    exit;
}

if ( ! class_exists( 'CR_Player_Status' ) ) {

    class CR_Player_Status {

        /**
         * Basis URL van de officiële Clash Royale API.
         * Documentatie: https://developer.clashroyale.com
         */
        const API_BASE = 'https://api.clashroyale.com/v1';

        /**
         * Optie-namen in de wp_options tabel.
         * Deze sleutels gebruiken we in register_setting() en get_option().
         */
        const OPTION_API_TOKEN      = 'cr_status_api_token';
        const OPTION_SHOW_CHESTS    = 'cr_status_show_chests';
        const OPTION_SHOW_BATTLES   = 'cr_status_show_battles';

        /**
         * Constructor: registreert alle WordPress hooks.
         */
        public function __construct() {
            // Maakt een menu-item in het WP dashboard voor de instellingenpagina.
            add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

            // Registreert de instellingen met de WordPress Settings API.
            add_action( 'admin_init', array( $this, 'register_settings' ) );

            // Registreert de shortcode [cr_player_search].
            add_shortcode( 'cr_player_search', array( $this, 'render_player_search_shortcode' ) );
        }

        /**
         * Registreer een submenu onder "Instellingen" in het WordPress dashboard.
         */
        public function register_settings_page() {
            add_options_page(
                __( 'Clash Royale Status', 'clash-royale-status' ), // Pagina-titel
                __( 'Clash Royale Status', 'clash-royale-status' ), // Menu-titel
                'manage_options',                                   // Capability (alleen admins)
                'cr-player-status',                                 // Slug in de URL
                array( $this, 'render_settings_page' )              // Callback om HTML te tonen
            );
        }

        /**
         * Registreer de opties met de WordPress Settings API.
         * Dit zorgt ervoor dat WordPress de data valideert en opslaat.
         */
        public function register_settings() {
            // Registreer het token
            register_setting(
                'cr_player_status_group',              // Settings group (gebruikt in <form>)
                self::OPTION_API_TOKEN,                // Optienaam
                array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                )
            );

            // Checkbox: upcoming chests ophalen?
            register_setting(
                'cr_player_status_group',
                self::OPTION_SHOW_CHESTS,
                array(
                    'type'              => 'boolean',
                    'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                    'default'           => 1,
                )
            );

            // Checkbox: battle log ophalen?
            register_setting(
                'cr_player_status_group',
                self::OPTION_SHOW_BATTLES,
                array(
                    'type'              => 'boolean',
                    'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                    'default'           => 1,
                )
            );

            // Voeg een sectie toe op de instellingenpagina.
            add_settings_section(
                'cr_player_status_main_section',                         // ID
                __( 'API instellingen', 'clash-royale-status' ),         // Titel
                array( $this, 'render_settings_section_intro' ),         // Callback voor introductietekst
                'cr-player-status'                                       // Page (slug van de opties-pagina)
            );

            // API token veld
            add_settings_field(
                self::OPTION_API_TOKEN,
                __( 'Clash Royale API token', 'clash-royale-status' ),
                array( $this, 'render_field_api_token' ),
                'cr-player-status',
                'cr_player_status_main_section'
            );

            // Checkbox veld: show chests
            add_settings_field(
                self::OPTION_SHOW_CHESTS,
                __( 'Upcoming chests ophalen', 'clash-royale-status' ),
                array( $this, 'render_field_show_chests' ),
                'cr-player-status',
                'cr_player_status_main_section'
            );

            // Checkbox veld: show battles
            add_settings_field(
                self::OPTION_SHOW_BATTLES,
                __( 'Battle log ophalen', 'clash-royale-status' ),
                array( $this, 'render_field_show_battles' ),
                'cr-player-status',
                'cr_player_status_main_section'
            );
        }

        /**
         * Eenvoudige sanitizer voor checkboxes.
         * WordPress stuurt "on" wanneer aangevinkt; we casten dit naar 1 of 0.
         */
        public function sanitize_checkbox( $value ) {
            return $value ? 1 : 0;
        }

        /**
         * Intro-tekst boven de instellingen-sectie.
         */
        public function render_settings_section_intro() {
            ?>
            <p>
                <?php esc_html_e( 'Vul hier je Clash Royale API token in en kies welke data je wilt ophalen.', 'clash-royale-status' ); ?>
            </p>
            <p>
                <?php esc_html_e( 'Je hebt een token nodig van developer.clashroyale.com. Na het opslaan kun je de shortcode [cr_player_search] gebruiken op een pagina.', 'clash-royale-status' ); ?>
            </p>
            <?php
        }

        /**
         * HTML voor het API token veld.
         */
        public function render_field_api_token() {
            $value = get_option( self::OPTION_API_TOKEN, '' );
            ?>
            <input
                type="text"
                name="<?php echo esc_attr( self::OPTION_API_TOKEN ); ?>"
                value="<?php echo esc_attr( $value ); ?>"
                class="regular-text"
            />
            <p class="description">
                <?php esc_html_e( 'Plak hier je "Bearer" token van de officiële Clash Royale API.', 'clash-royale-status' ); ?>
            </p>
            <?php
        }

        /**
         * HTML voor de "show chests" checkbox.
         */
        public function render_field_show_chests() {
            $value = get_option( self::OPTION_SHOW_CHESTS, 1 );
            ?>
            <label>
                <input
                    type="checkbox"
                    name="<?php echo esc_attr( self::OPTION_SHOW_CHESTS ); ?>"
                    value="1"
                    <?php checked( $value, 1 ); ?>
                />
                <?php esc_html_e( 'Toon ook de aankomende chests van de speler.', 'clash-royale-status' ); ?>
            </label>
            <?php
        }

        /**
         * HTML voor de "show battles" checkbox.
         */
        public function render_field_show_battles() {
            $value = get_option( self::OPTION_SHOW_BATTLES, 1 );
            ?>
            <label>
                <input
                    type="checkbox"
                    name="<?php echo esc_attr( self::OPTION_SHOW_BATTLES ); ?>"
                    value="1"
                    <?php checked( $value, 1 ); ?>
                />
                <?php esc_html_e( 'Toon ook de meest recente battles van de speler.', 'clash-royale-status' ); ?>
            </label>
            <?php
        }

        /**
         * Render de volledige instellingenpagina.
         */
        public function render_settings_page() {
            // Controle: alleen admins mogen dit zien.
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Clash Royale Player Status', 'clash-royale-status' ); ?></h1>

                <form method="post" action="options.php">
                    <?php
                    // WordPress nonce + settings fields voor de group.
                    settings_fields( 'cr_player_status_group' );
                    // Laad alle geregistreerde secties en velden voor deze pagina.
                    do_settings_sections( 'cr-player-status' );
                    // Standaard "opslaan" knop.
                    submit_button();
                    ?>
                </form>

                <hr />

                <h2><?php esc_html_e( 'Shortcode gebruik', 'clash-royale-status' ); ?></h2>
                <p>
                    <?php esc_html_e( 'Plaats de shortcode [cr_player_search] op een pagina of bericht om een zoekformulier te tonen.', 'clash-royale-status' ); ?>
                </p>
            </div>
            <?php
        }

        /**
         * Shortcode callback.
         * Deze functie wordt uitgevoerd wanneer WordPress [cr_player_search] tegenkomt.
         *
         * @return string HTML output.
         */
        public function render_player_search_shortcode() {
            // Start output buffering zodat we makkelijk HTML kunnen opbouwen in PHP.
            ob_start();

            // Lees de huidige waarde van het tag veld (bijvoorbeeld na submit).
            $raw_tag = isset( $_GET['cr_player_tag'] ) ? wp_unslash( $_GET['cr_player_tag'] ) : '';

            // Tag normaliseren (we maken deze uppercase, verwijderen # en spaces).
            $normalized_tag = $this->normalize_player_tag( $raw_tag );

            // Toon altijd eerst het formulier.
            $this->render_search_form( $raw_tag );

            // Als er geen tag is ingevuld, stoppen we hier. De gebruiker kan dan eerst zoeken.
            if ( empty( $normalized_tag ) ) {
                return ob_get_clean();
            }

            // Controleer of er een API token is ingesteld.
            $token = get_option( self::OPTION_API_TOKEN, '' );
            if ( empty( $token ) ) {
                echo '<p style="color:red;">' . esc_html__( 'Geen API token ingesteld. Vul je token in op de instellingenpagina van de plug-in.', 'clash-royale-status' ) . '</p>';
                return ob_get_clean();
            }

            // Toon een kleine samenvatting welke tag we nu ophalen.
            printf(
                '<p><strong>%s</strong> %s</p>',
                esc_html__( 'Zoekresultaten voor tag:', 'clash-royale-status' ),
                esc_html( '#' . $normalized_tag )
            );

            // API-call: basis player info
            $player_data = $this->api_get( '/players/%23' . rawurlencode( $normalized_tag ) );

            // Foutafhandeling: als de API een WP_Error teruggeeft, tonen we een nette melding.
            if ( is_wp_error( $player_data ) ) {
                echo '<p style="color:red;">' . esc_html( $player_data->get_error_message() ) . '</p>';
                return ob_get_clean();
            }

            // Als de API geen geldige data teruggeeft, stoppen we ook.
            if ( empty( $player_data ) || ! is_array( $player_data ) ) {
                echo '<p style="color:red;">' . esc_html__( 'Onverwachte response van de API. Controleer je tag en probeer het opnieuw.', 'clash-royale-status' ) . '</p>';
                return ob_get_clean();
            }

            // Render basis player info.
            $this->render_player_info( $player_data );

            // Instellingen lezen om te bepalen welke extra API-calls we doen.
            $show_chests  = (bool) get_option( self::OPTION_SHOW_CHESTS, 1 );
            $show_battles = (bool) get_option( self::OPTION_SHOW_BATTLES, 1 );

            // Optioneel: upcoming chests ophalen.
            if ( $show_chests ) {
                $chests_data = $this->api_get( '/players/%23' . rawurlencode( $normalized_tag ) . '/upcomingchests' );
                $this->render_upcoming_chests( $chests_data );
            }

            // Optioneel: battle log ophalen.
            if ( $show_battles ) {
                $battles_data = $this->api_get( '/players/%23' . rawurlencode( $normalized_tag ) . '/battlelog' );
                $this->render_battle_log( $battles_data );
            }

            // Stuur alle verzamelde HTML terug naar WordPress.
            return ob_get_clean();
        }

        /**
         * Teken het zoekformulier voor de speler-tag.
         *
         * @param string $current_tag De huidige (ongefilterde) waarde van het tag input veld.
         */
        protected function render_search_form( $current_tag ) {
            ?>
            <form method="get" class="cr-player-search-form" style="margin-bottom:1.5rem;">
                <label for="cr_player_tag">
                    <?php esc_html_e( 'Speler tag (bijv. #P0LY8QJ):', 'clash-royale-status' ); ?>
                </label><br />
                <input
                    type="text"
                    id="cr_player_tag"
                    name="cr_player_tag"
                    value="<?php echo esc_attr( $current_tag ); ?>"
                    placeholder="#P0LY8QJ"
                    style="max-width: 250px;"
                />
                <button type="submit">
                    <?php esc_html_e( 'Zoek speler', 'clash-royale-status' ); ?>
                </button>
            </form>
            <?php
        }

        /**
         * Normaliseer een speler-tag:
         * - verwijder # en spaties
         * - maak alles uppercase
         * - filter op toegestane tekens (Clash Royale gebruikt alleen 0289PYLQGRJCUV)
         *
         * @param string $raw_tag
         * @return string
         */
        protected function normalize_player_tag( $raw_tag ) {
            // Alles naar uppercase.
            $tag = strtoupper( trim( $raw_tag ) );
            // Verwijder een leading # als die is meegegeven.
            if ( strpos( $tag, '#' ) === 0 ) {
                $tag = substr( $tag, 1 );
            }

            // Laat alleen toegestane karakters over.
            $tag = preg_replace( '/[^0289PYLQGRJCUV]/', '', $tag );

            return $tag;
        }

        /**
         * Algemene helper om een GET request naar de Clash Royale API te sturen.
         *
         * @param string $endpoint Bijvoorbeeld: '/players/%23TAG' (let op: # => %23)
         * @return array|WP_Error
         */
        protected function api_get( $endpoint ) {
            $token = get_option( self::OPTION_API_TOKEN, '' );

            if ( empty( $token ) ) {
                return new WP_Error(
                    'cr_no_token',
                    __( 'Geen API token gevonden. Stel je token in op de instellingenpagina.', 'clash-royale-status' )
                );
            }

            // Volledige URL opbouwen.
            $url = self::API_BASE . $endpoint;

            // Headers voor authenticatie.
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ),
                // Zet een relatief korte timeout zodat de site niet te lang "hangt".
                'timeout' => 10,
            );

            $response = wp_remote_get( $url, $args );

            // Controleer of WordPress zelf een fout geeft (bijvoorbeeld time-out).
            if ( is_wp_error( $response ) ) {
                return new WP_Error(
                    'cr_http_error',
                    sprintf(
                        /* translators: %s is de foutmelding van WordPress. */
                        __( 'Fout bij het ophalen van data: %s', 'clash-royale-status' ),
                        $response->get_error_message()
                    )
                );
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );

            // Als de HTTP status geen 2xx is, geven we een nette foutmelding.
            if ( $code < 200 || $code >= 300 ) {
                return new WP_Error(
                    'cr_api_error',
                    sprintf(
                        /* translators: %d is de HTTP status code. */
                        __( 'De Clash Royale API gaf een foutmelding (HTTP %d). Controleer de speler tag en je token.', 'clash-royale-status' ),
                        intval( $code )
                    )
                );
            }

            // Probeer JSON te decoderen.
            $data = json_decode( $body, true );

            if ( json_last_error() !== JSON_ERROR_NONE ) {
                return new WP_Error(
                    'cr_json_error',
                    __( 'Kon de API response niet als JSON lezen.', 'clash-royale-status' )
                );
            }

            return $data;
        }

        /**
         * Render de basis player info in een nette kaart.
         *
         * @param array $player
         */
        protected function render_player_info( array $player ) {
            ?>
            <div class="cr-player-card" style="border:1px solid #ddd; padding:1rem; margin-bottom:1.5rem;">
                <h2 style="margin-top:0;">
                    <?php echo esc_html( $player['name'] ?? 'Onbekende speler' ); ?>
                    <small style="font-size:0.8em; font-weight:normal;">
                        <?php echo isset( $player['tag'] ) ? esc_html( $player['tag'] ) : ''; ?>
                    </small>
                </h2>

                <p>
                    <strong><?php esc_html_e( 'Level:', 'clash-royale-status' ); ?></strong>
                    <?php echo isset( $player['expLevel'] ) ? intval( $player['expLevel'] ) : '-'; ?>
                    <br />
                    <strong><?php esc_html_e( 'Trophies:', 'clash-royale-status' ); ?></strong>
                    <?php echo isset( $player['trophies'] ) ? intval( $player['trophies'] ) : '-'; ?>
                    <br />
                    <strong><?php esc_html_e( 'Best trophies:', 'clash-royale-status' ); ?></strong>
                    <?php echo isset( $player['bestTrophies'] ) ? intval( $player['bestTrophies'] ) : '-'; ?>
                </p>

                <?php if ( isset( $player['clan'] ) && is_array( $player['clan'] ) ) : ?>
                    <p>
                        <strong><?php esc_html_e( 'Clan:', 'clash-royale-status' ); ?></strong>
                        <?php echo esc_html( $player['clan']['name'] ?? '' ); ?>
                        <?php echo isset( $player['clan']['tag'] ) ? ' (' . esc_html( $player['clan']['tag'] ) . ')' : ''; ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php
        }

        /**
         * Render de upcoming chests sectie.
         *
         * @param array|WP_Error $chests_data
         */
        protected function render_upcoming_chests( $chests_data ) {
            echo '<h3>' . esc_html__( 'Upcoming chests', 'clash-royale-status' ) . '</h3>';

            if ( is_wp_error( $chests_data ) ) {
                echo '<p style="color:red;">' . esc_html( $chests_data->get_error_message() ) . '</p>';
                return;
            }

            if ( empty( $chests_data['items'] ) || ! is_array( $chests_data['items'] ) ) {
                echo '<p>' . esc_html__( 'Geen chest-data beschikbaar voor deze speler.', 'clash-royale-status' ) . '</p>';
                return;
            }

            echo '<ul>';
            // Toon alleen de eerste ~10 chests voor overzicht.
            $counter = 0;
            foreach ( $chests_data['items'] as $item ) {
                $counter++;
                if ( $counter > 10 ) {
                    break;
                }

                $name  = isset( $item['name'] ) ? $item['name'] : __( 'Onbekende chest', 'clash-royale-status' );
                $index = isset( $item['index'] ) ? intval( $item['index'] ) : null;

                echo '<li>';
                echo esc_html( $name );
                if ( $index !== null ) {
                    echo ' - ';
                    printf(
                        /* translators: %d is het aantal chests tot deze. */
                        esc_html__( 'over %d chest(s)', 'clash-royale-status' ),
                        $index
                    );
                }
                echo '</li>';
            }
            echo '</ul>';
        }

        /**
         * Render de battle log sectie in een eenvoudige tabel.
         *
         * @param array|WP_Error $battles_data
         */
        protected function render_battle_log( $battles_data ) {
            echo '<h3>' . esc_html__( 'Recente battles', 'clash-royale-status' ) . '</h3>';

            if ( is_wp_error( $battles_data ) ) {
                echo '<p style="color:red;">' . esc_html( $battles_data->get_error_message() ) . '</p>';
                return;
            }

            if ( empty( $battles_data ) || ! is_array( $battles_data ) ) {
                echo '<p>' . esc_html__( 'Geen battles gevonden voor deze speler.', 'clash-royale-status' ) . '</p>';
                return;
            }

            echo '<table class="cr-battle-log" style="width:100%; border-collapse:collapse;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="border-bottom:1px solid #ddd; text-align:left; padding:4px 8px;">' . esc_html__( 'Tijd', 'clash-royale-status' ) . '</th>';
            echo '<th style="border-bottom:1px solid #ddd; text-align:left; padding:4px 8px;">' . esc_html__( 'Type', 'clash-royale-status' ) . '</th>';
            echo '<th style="border-bottom:1px solid #ddd; text-align:left; padding:4px 8px;">' . esc_html__( 'Resultaat', 'clash-royale-status' ) . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Toon maximaal 10 battles.
            $count = 0;
            foreach ( $battles_data as $battle ) {
                $count++;
                if ( $count > 10 ) {
                    break;
                }

                // Resultaat bepalen (win/lose/draw) op basis van crowns.
                $team_crowns  = $battle['team'][0]['crowns'] ?? 0;
                $opponent_crowns = $battle['opponent'][0]['crowns'] ?? 0;
                $result_label = __( 'Onbekend', 'clash-royale-status' );

                if ( $team_crowns > $opponent_crowns ) {
                    $result_label = __( 'Win', 'clash-royale-status' );
                } elseif ( $team_crowns < $opponent_crowns ) {
                    $result_label = __( 'Verlies', 'clash-royale-status' );
                } else {
                    $result_label = __( 'Gelijkspel', 'clash-royale-status' );
                }

                $battle_type = $battle['gameMode']['name'] ?? __( 'Onbekend', 'clash-royale-status' );
                $battle_time = $battle['battleTime'] ?? '';

                echo '<tr>';
                echo '<td style="border-bottom:1px solid #eee; padding:4px 8px;">' . esc_html( $battle_time ) . '</td>';
                echo '<td style="border-bottom:1px solid #eee; padding:4px 8px;">' . esc_html( $battle_type ) . '</td>';
                echo '<td style="border-bottom:1px solid #eee; padding:4px 8px;">' . esc_html( $result_label ) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }
    }

    // Instantiate de hoofdklasse zodat de hooks worden geregistreerd.
    new CR_Player_Status();
}
