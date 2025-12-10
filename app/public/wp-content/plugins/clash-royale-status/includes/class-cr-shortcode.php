<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CR_Shortcode {
    protected $api;
    protected $assets;

    public function __construct( CR_API $api, CR_Assets $assets ) {
        $this->api    = $api;
        $this->assets = $assets;
        add_shortcode( 'cr_player_search', array( $this, 'render_player_search_shortcode' ) );
    }

    public function render_player_search_shortcode() {
        ob_start();

        $raw_tag = isset( $_GET['cr_player_tag'] ) ? wp_unslash( $_GET['cr_player_tag'] ) : '';
        $normalized_tag = $this->normalize_player_tag( $raw_tag );

        $this->render_search_form( $raw_tag );
        if ( empty( $normalized_tag ) ) { return ob_get_clean(); }

        $token = get_option( CR_STATUS_OPTION_API_TOKEN, '' );
        if ( empty( $token ) ) {
            echo '<p style="color:red;">' . esc_html__( 'Geen API token ingesteld. Vul je token in op de instellingenpagina van de plug-in.', 'clash-royale-status' ) . '</p>';
            return ob_get_clean();
        }

        printf( '<p><strong>%s</strong> %s</p>', esc_html__( 'Zoekresultaten voor tag:', 'clash-royale-status' ), esc_html( '#' . $normalized_tag ) );

        $player_data = $this->api->get( '/players/%23' . rawurlencode( $normalized_tag ) );
        if ( is_wp_error( $player_data ) ) { echo '<p style="color:red;">' . esc_html( $player_data->get_error_message() ) . '</p>'; return ob_get_clean(); }
        if ( empty( $player_data ) || ! is_array( $player_data ) ) { echo '<p style="color:red;">' . esc_html__( 'Onverwachte response van de API. Controleer je tag en probeer het opnieuw.', 'clash-royale-status' ) . '</p>'; return ob_get_clean(); }

        $this->render_player_info( $player_data );

        $show_chests  = (bool) get_option( CR_STATUS_OPTION_SHOW_CHESTS, 1 );
        $show_battles = (bool) get_option( CR_STATUS_OPTION_SHOW_BATTLES, 1 );

        if ( $show_chests ) {
            $chests_data = $this->api->get( '/players/%23' . rawurlencode( $normalized_tag ) . '/upcomingchests' );
            $this->render_upcoming_chests( $chests_data );
        }

        if ( $show_battles ) {
            $battles_data = $this->api->get( '/players/%23' . rawurlencode( $normalized_tag ) . '/battlelog' );
            $this->render_battle_log( $battles_data );
        }

        return ob_get_clean();
    }

    protected function render_search_form( $current_tag ) {
        ?>
        <form method="get" class="cr-player-search-form" style="margin-bottom:1.5rem;">
            <label for="cr_player_tag"><?php esc_html_e( 'Speler tag (bijv. #2099QYC98):', 'clash-royale-status' ); ?></label><br />
            <input type="text" id="cr_player_tag" name="cr_player_tag" value="<?php echo esc_attr( $current_tag ); ?>" placeholder="#P0LY8QJ" style="max-width: 250px;" />
            <button type="submit"><?php esc_html_e( 'Zoek speler', 'clash-royale-status' ); ?></button>
        </form>
        <?php
    }

    protected function normalize_player_tag( $raw_tag ) {
        $tag = strtoupper( trim( $raw_tag ) );
        if ( strpos( $tag, '#' ) === 0 ) { $tag = substr( $tag, 1 ); }
        $tag = preg_replace( '/[^0289PYLQGRJCUV]/', '', $tag );
        return $tag;
    }

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

    protected function render_upcoming_chests( $chests_data ) {
        echo '<h3>' . esc_html__( 'Upcoming chests', 'clash-royale-status' ) . '</h3>';
        if ( is_wp_error( $chests_data ) ) { echo '<p style="color:red;">' . esc_html( $chests_data->get_error_message() ) . '</p>'; return; }
        if ( empty( $chests_data['items'] ) || ! is_array( $chests_data['items'] ) ) { echo '<p>' . esc_html__( 'Geen chest-data beschikbaar voor deze speler.', 'clash-royale-status' ) . '</p>'; return; }

        echo '<ul>';
        $counter = 0;
        foreach ( $chests_data['items'] as $item ) {
            if ( ++$counter > 10 ) { break; }
            $name  = isset( $item['name'] ) ? $item['name'] : __( 'Onbekende chest', 'clash-royale-status' );
            $index = isset( $item['index'] ) ? intval( $item['index'] ) : null;
            $icon_url = $this->assets->get_chest_icon_url( $name );

            echo '<li>';
            if ( $icon_url ) { echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $name ) . '" style="width:24px;height:24px;vertical-align:middle;margin-right:6px;" />'; }
            echo esc_html( $name );
            if ( $index !== null ) {
                echo ' - ';
                printf( esc_html__( 'over %d chest(s)', 'clash-royale-status' ), $index );
            }
            echo '</li>';
        }
        echo '</ul>';
    }

    protected function render_battle_log( $battles_data ) {
        echo '<h3>' . esc_html__( 'Recente battles', 'clash-royale-status' ) . '</h3>';
        if ( is_wp_error( $battles_data ) ) { echo '<p style="color:red;">' . esc_html( $battles_data->get_error_message() ) . '</p>'; return; }
        if ( empty( $battles_data ) || ! is_array( $battles_data ) ) { echo '<p>' . esc_html__( 'Geen battles gevonden voor deze speler.', 'clash-royale-status' ) . '</p>'; return; }

        echo '<table class="cr-battle-log" style="width:100%; border-collapse:collapse;">';
        echo '<thead><tr>';
        echo '<th style="border-bottom:1px solid #ddd; text-align:left; padding:4px 8px;">' . esc_html__( 'Tijd', 'clash-royale-status' ) . '</th>';
        echo '<th style="border-bottom:1px solid #ddd; text-align:left; padding:4px 8px;">' . esc_html__( 'Type', 'clash-royale-status' ) . '</th>';
        echo '<th style="border-bottom:1px solid #ddd; text-align:left; padding:4px 8px;">' . esc_html__( 'Resultaat', 'clash-royale-status' ) . '</th>';
        echo '</tr></thead><tbody>';

        $count = 0;
        foreach ( $battles_data as $battle ) {
            if ( ++$count > 10 ) { break; }
            $team_crowns     = $battle['team'][0]['crowns'] ?? 0;
            $opponent_crowns = $battle['opponent'][0]['crowns'] ?? 0;
            $result_label = __( 'Onbekend', 'clash-royale-status' );
            if ( $team_crowns > $opponent_crowns ) { $result_label = __( 'Win', 'clash-royale-status' ); }
            elseif ( $team_crowns < $opponent_crowns ) { $result_label = __( 'Verlies', 'clash-royale-status' ); }
            else { $result_label = __( 'Gelijkspel', 'clash-royale-status' ); }

            $battle_type = $battle['gameMode']['name'] ?? __( 'Onbekend', 'clash-royale-status' );
            $battle_time = $battle['battleTime'] ?? '';

            echo '<tr>';
            echo '<td style="border-bottom:1px solid #eee; padding:4px 8px;">' . esc_html( $battle_time ) . '</td>';
            echo '<td style="border-bottom:1px solid #eee; padding:4px 8px;">' . esc_html( $battle_type ) . '</td>';
            echo '<td style="border-bottom:1px solid #eee; padding:4px 8px;">' . esc_html( $result_label ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}
