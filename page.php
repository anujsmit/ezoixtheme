<?php
/**
 * The template for displaying all single pages.
 *
 * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container">
    <div class="content-area">
        <main id="primary" class="site-main">
            <?php
            while ( have_posts() ) : the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
                    <header class="page-header">
                        <h1 class="page-title single-post-title"><?php the_title(); ?></h1>
                    </header>

                    <div class="post-content">
                        <?php the_content(); ?>
                    </div>
                </article>
                <?php
            endwhile; // End of the loop.
            ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>