<?php
/**
 * Template Name: Clash Royale Status Page
 *
 * Simpel pagina template dat je Clash Royale Player Status plug-in mooi toont.
 *
 * Gebruik:
 * 1. Activeer dit thema in WordPress.
 * 2. Maak een nieuwe pagina aan, bijvoorbeeld "Clash Royale Status".
 * 3. Kies bij Template: "Clash Royale Status Page".
 * 4. Publiceer en bezoek de pagina.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main">
    <div class="cr-card">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <header class="page-header" style="margin-bottom: 1.5rem;">
                    <h1 class="page-title" style="margin: 0 0 0.5rem;">
                        <?php the_title(); ?>
                    </h1>
                    <p style="margin: 0; color: rgba(209,213,219,0.9);">
                        Zoek een Clash Royale speler en bekijk stats, upcoming chests en recente battles.
                    </p>
                </header>

                <section class="page-content">
                    <?php
                    // Eventuele extra tekst uit de editor.
                    the_content();
                    ?>

                    <hr style="margin: 1.5rem 0; border-color: rgba(55,65,81,0.7);" />

                    <?php
                    // Hier wordt de shortcode uit je plug-in uitgevoerd.
                    echo do_shortcode( '[cr_player_search]' );
                    ?>
                </section>
            <?php endwhile; ?>
        <?php else : ?>
            <p>Geen pagina gevonden.</p>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
