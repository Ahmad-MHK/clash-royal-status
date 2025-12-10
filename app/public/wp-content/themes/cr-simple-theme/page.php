<?php
/**
 * Standaard page template
 */
get_header();
?>

<main id="primary" class="site-main">
    <div class="cr-card">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <h1><?php the_title(); ?></h1>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p>Geen pagina gevonden.</p>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
