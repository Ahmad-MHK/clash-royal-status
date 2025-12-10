<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CR_Assets {
    protected $base_url;
    protected $base_path;
    protected $assets_url;
    protected $assets_path;
    protected $chest_search_paths = array();

    public function __construct( $plugin_path, $plugin_url ) {
        $this->base_path   = trailingslashit( $plugin_path );
        $this->base_url    = trailingslashit( $plugin_url );
        $this->assets_path = $this->base_path . 'assets/';
        $this->assets_url  = $this->base_url  . 'assets/';

        $this->chest_search_paths = apply_filters(
            'cr_status_chest_icon_paths',
            array(
                array(
                    'path' => $this->base_path . 'assets/img/icon/kaarten/chest/',
                    'url'  => $this->base_url  . 'assets/img/icon/kaarten/chest/',
                ),
                array(
                    'path' => $this->base_path . 'assets/img/images/icon/kaarten/chest/',
                    'url'  => $this->base_url  . 'assets/img/images/icon/kaarten/chest/',
                ),
                array(
                    'path' => $this->base_path . 'assets/img/Chest/',
                    'url'  => $this->base_url  . 'assets/img/Chest/',
                ),
                array(
                    'path' => $this->base_path . 'assets/img/chests/',
                    'url'  => $this->base_url  . 'assets/img/chests/',
                ),
                array(
                    'path' => $this->assets_path . 'chests/',
                    'url'  => $this->assets_url  . 'chests/',
                ),
            ),
            $this->base_path,
            $this->base_url
        );
    }

    public function get_chest_icon_url( $chest_name ) {
        if ( empty( $chest_name ) ) { return null; }
        $slug  = $this->resolve_chest_slug( $chest_name );
        $index = $this->get_chest_icon_index();
        return isset( $index[ $slug ] ) ? $index[ $slug ] : null;
    }

    protected function resolve_chest_slug( $chest_name ) {
        $map = array(
            'Silver Chest'             => 'Chest_Silver_Chest',
            'Golden Chest'             => 'Chest_Golden_Chest',
            'Giant Chest'              => 'Chest_Giant_Chest',
            'Magical Chest'            => 'Chest_Magical_Chest',
            'Super Magical Chest'      => 'Chest_Super_Magical_Chest',
            'Epic Chest'               => 'Chest_Epic_Chest',
            'Legendary Chest'          => 'Chest_Legendary_Chest',
            'Lightning Chest'          => 'Chest_Lightning_Chest',
            'Mega Lightning Chest'     => 'Chest_Mega_Lightning_Chest',
            'Royal Wild Chest'         => 'Chest_Royal_Wild_Chest',
            'Plentiful Gold Crate'     => 'Chest_Plentiful_Gold_Crate',
            'Overflowing Gold Crate'   => 'Chest_Overflowing_Gold_Crate',
            'Gold Crate'               => 'Chest_Gold_Crate',
            'Tower Troop Chest'       => 'Chest_Tower_Troops_Chest',
        );
        $slug = isset( $map[ $chest_name ] ) ? $map[ $chest_name ] : sanitize_title( $chest_name );
        return $this->canonicalize_slug( $slug );
    }

    protected function get_chest_icon_index() {
        static $index = null;
        if ( $index !== null ) { return $index; }

        $index = array();
        $exts  = array( 'png', 'svg', 'jpg', 'jpeg', 'webp' );

        foreach ( $this->chest_search_paths as $entry ) {
            $dir = isset( $entry['path'] ) ? $entry['path'] : '';
            $url = isset( $entry['url'] )  ? $entry['url']  : '';
            if ( empty( $dir ) || empty( $url ) || ! is_dir( $dir ) ) { continue; }

            foreach ( $exts as $ext ) {
                $files = glob( $dir . '*.' . $ext );
                if ( empty( $files ) ) { continue; }
                foreach ( $files as $file ) {
                    $base = pathinfo( $file, PATHINFO_FILENAME );
                    $slug = sanitize_title( $base );

                    $variants = array();
                    $variants[] = $slug;
                    if ( strpos( $slug, 'chest-' ) === 0 ) {
                        $variants[] = substr( $slug, 6 );
                    }
                    if ( substr( $slug, -6 ) === '-chest' ) {
                        $variants[] = substr( $slug, 0, -6 );
                    }
                    if ( strpos( $slug, 'chest-' ) === 0 && substr( $slug, -6 ) === '-chest' && strlen( $slug ) > 12 ) {
                        $variants[] = substr( $slug, 6 );
                    }

                    foreach ( $variants as $v ) {
                        $norm = $this->canonicalize_slug( $v );
                        if ( $norm && ! isset( $index[ $norm ] ) ) {
                            $index[ $norm ] = trailingslashit( $url ) . basename( $file );
                        }
                    }
                }
            }
        }

        return $index;
    }

    protected function canonicalize_slug( $slug ) {
        $slug = strtolower( trim( $slug ) );
        $slug = preg_replace( '/-+/', '-', $slug );
        $replacements = array(
            'sliver'          => 'silver',
            'legandary'       => 'legendary',
            'legendairy'      => 'legendary',
            'lightening'      => 'lightning',
            'mega-lightening' => 'mega-lightning',
        );
        $parts = explode( '-', $slug );
        foreach ( $parts as &$p ) {
            if ( isset( $replacements[ $p ] ) ) { $p = $replacements[ $p ]; }
        }
        unset( $p );
        $slug = implode( '-', $parts );
        $slug = preg_replace( '/-+/', '-', $slug );
        return $slug;
    }
}
