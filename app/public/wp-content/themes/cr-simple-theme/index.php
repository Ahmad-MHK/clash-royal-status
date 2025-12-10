<?php
/**
 * Index template
 * Dit is de fallback template als er geen specifiekere template is.
 */
get_header();
?>

<main id="primary" class="site-main">
    <div class="cr-card">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <h2><?php the_title(); ?></h2>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p>Er is nog geen inhoud. Maak een pagina aan in het WordPress dashboard.</p>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
