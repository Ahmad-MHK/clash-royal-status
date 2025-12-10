<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CR_Admin {
    protected $assets;

    public function __construct( CR_Assets $assets ) {
        $this->assets = $assets;
        add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_settings_page() {
        add_options_page(
            __( 'Clash Royale Status', 'clash-royale-status' ),
            __( 'Clash Royale Status', 'clash-royale-status' ),
            'manage_options',
            'cr-player-status',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'cr_player_status_group',
            CR_STATUS_OPTION_API_TOKEN,
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            )
        );

        register_setting(
            'cr_player_status_group',
            CR_STATUS_OPTION_SHOW_CHESTS,
            array(
                'type'              => 'boolean',
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'default'           => 1,
            )
        );

        register_setting(
            'cr_player_status_group',
            CR_STATUS_OPTION_SHOW_BATTLES,
            array(
                'type'              => 'boolean',
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'default'           => 1,
            )
        );

        add_settings_section(
            'cr_player_status_main_section',
            __( 'API instellingen', 'clash-royale-status' ),
            array( $this, 'render_settings_section_intro' ),
            'cr-player-status'
        );

        add_settings_field(
            CR_STATUS_OPTION_API_TOKEN,
            __( 'Clash Royale API token', 'clash-royale-status' ),
            array( $this, 'render_field_api_token' ),
            'cr-player-status',
            'cr_player_status_main_section'
        );

        add_settings_field(
            CR_STATUS_OPTION_SHOW_CHESTS,
            __( 'Upcoming chests ophalen', 'clash-royale-status' ),
            array( $this, 'render_field_show_chests' ),
            'cr-player-status',
            'cr_player_status_main_section'
        );

        add_settings_field(
            CR_STATUS_OPTION_SHOW_BATTLES,
            __( 'Battle log ophalen', 'clash-royale-status' ),
            array( $this, 'render_field_show_battles' ),
            'cr-player-status',
            'cr_player_status_main_section'
        );
    }

    public function sanitize_checkbox( $value ) {
        return $value ? 1 : 0;
    }

    public function render_settings_section_intro() {
        ?>
        <p><?php esc_html_e( 'Vul hier je Clash Royale API token in en kies welke data je wilt ophalen.', 'clash-royale-status' ); ?></p>
        <p><?php esc_html_e( 'Je hebt een token nodig van developer.clashroyale.com. Na het opslaan kun je de shortcode [cr_player_search] gebruiken op een pagina.', 'clash-royale-status' ); ?></p>
        <p>
            <?php esc_html_e( 'Chest-afbeeldingen plaatsen? De plugin zoekt automatisch in:', 'clash-royale-status' ); ?>
            <code>wp-content/plugins/clash-royale-status/assets/img/Chest/</code>
            <?php esc_html_e( 'Bestandsnaam = chestnaam (kleine letters, streepjes), bv.:', 'clash-royale-status' ); ?>
            <code>silver-chest.png</code>, <code>golden-chest.png</code>, <code>legendary-chest.png</code>.
        </p>
        <?php
    }

    public function render_field_api_token() {
        $value = get_option( CR_STATUS_OPTION_API_TOKEN, '' );
        ?>
        <input type="text" name="<?php echo esc_attr( CR_STATUS_OPTION_API_TOKEN ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e( 'Plak hier je "Bearer" token van de officiële Clash Royale API.', 'clash-royale-status' ); ?></p>
        <?php
    }

    public function render_field_show_chests() {
        $value = get_option( CR_STATUS_OPTION_SHOW_CHESTS, 1 );
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( CR_STATUS_OPTION_SHOW_CHESTS ); ?>" value="1" <?php checked( $value, 1 ); ?> />
            <?php esc_html_e( 'Toon ook de aankomende chests van de speler.', 'clash-royale-status' ); ?>
        </label>
        <?php
    }

    public function render_field_show_battles() {
        $value = get_option( CR_STATUS_OPTION_SHOW_BATTLES, 1 );
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( CR_STATUS_OPTION_SHOW_BATTLES ); ?>" value="1" <?php checked( $value, 1 ); ?> />
            <?php esc_html_e( 'Toon ook de meest recente battles van de speler.', 'clash-royale-status' ); ?>
        </label>
        <?php
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Clash Royale Player Status', 'clash-royale-status' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'cr_player_status_group' ); do_settings_sections( 'cr-player-status' ); submit_button(); ?>
            </form>
            <hr />
            <h2><?php esc_html_e( 'Shortcode gebruik', 'clash-royale-status' ); ?></h2>
            <p><?php esc_html_e( 'Plaats de shortcode [cr_player_search] op een pagina of bericht om een zoekformulier te tonen.', 'clash-royale-status' ); ?></p>
        </div>
        <?php
    }
}
