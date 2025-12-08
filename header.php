<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php ezoix_preload_critical_resources(); ?>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <a class="skip-link screen-reader-text" href="#main-content">
        <?php esc_html_e('Skip to content', 'ezoix'); ?>
    </a>
    
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="site-branding">
                    <?php if (has_custom_logo()) : ?>
                        <div class="site-logo"><?php the_custom_logo(); ?></div>
                    <?php else : ?>
                        <div class="site-logo"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></div>
                    <?php endif; ?>
                </div>

                <nav class="main-navigation" id="main-navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_class' => 'nav-menu',
                        'container' => false,
                        'fallback_cb' => false,
                    ));
                    ?>
                </nav>

                <div class="header-controls">
                    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode">🌙</button>

                    <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="main-navigation">
                        <span class="menu-icon">☰</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section - Only on front page -->
    <?php if (is_front_page()) : ?>
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><?php bloginfo('name'); ?></h1>
                <p class="hero-subtitle"><?php bloginfo('description'); ?></p>
                
                <div class="trending-topics">
                    <?php
                    $categories = get_categories(array(
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 5,
                    ));
                    
                    foreach ($categories as $category) {
                        echo '<a href="' . get_category_link($category->term_id) . '" class="trending-topic">' . esc_html($category->name) . '</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <main id="main-content" class="site-main">
