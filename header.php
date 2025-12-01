<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    
    <!-- Preload critical resources -->
    <?php ezoix_preload_critical_resources(); ?>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="site-branding">
                    <?php if (has_custom_logo()) : ?>
                        <div class="site-logo"><?php the_custom_logo(); ?></div>
                    <?php else : ?>
                        <div class="site-logo"><a href="https://ezoix.com"><?php bloginfo('name'); ?></a></div>
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

                <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="main-navigation">
                    <span class="menu-icon">☰</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <?php if (is_front_page()) : ?>
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <?php echo get_theme_mod('ezoix_hero_title', 'Latest Tech Insights & Reviews'); ?>
                </h1>
                <p class="hero-subtitle">
                    <?php echo get_theme_mod('ezoix_hero_subtitle', 'Stay updated with the newest technology trends, gadget reviews, and AI developments'); ?>
                </p>
                
                <div class="trending-topics">
                    <span class="trending-topic">AI & Machine Learning</span>
                    <span class="trending-topic">Gadget Reviews</span>
                    <span class="trending-topic">Software Tools</span>
                    <span class="trending-topic">Tech News</span>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>