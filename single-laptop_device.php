<?php

/**
 * Template for displaying single laptop devices - SIMPLIFIED UI
 * * This file replaces the complex gallery and accordion with a single, table-based specification layout.
 */

get_header(); ?>

<style>
    /* Custom Styling for Review Section Images */
    .review-image-wrapper {
        margin-top: 15px;
        /* Added margin for separation from text */
        margin-bottom: 25px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .review-section-image {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
    }
</style>

<div class="mobile-device-container">
    <div class="device-header">
        <div class="container">
            <div class="breadcrumbs">
                <a href="<?php echo home_url('/'); ?>">Home</a> &raquo;
                <span><?php the_title(); ?></span>
            </div>

            <div class="device-title-section">
                <h1 class="device-title"><?php the_title(); ?></h1>
                <div class="device-meta">
                    <?php
                    // Display Brand
                    $brands = get_the_terms(get_the_ID(), 'laptop_brand'); // MODIFIED TAXONOMY
                    if ($brands && !is_wp_error($brands)) :
                        echo '<span class="device-brand">';
                        echo '<span class="meta-label">Brand:</span> ';
                        foreach ($brands as $brand) {
                            echo '<a href="' . get_term_link($brand) . '">' . esc_html($brand->name) . '</a>';
                            break;
                        }
                        echo '</span>';
                    endif;

                    // Display Model
                    $model = get_field('device_model');
                    if ($model) {
                        echo '<span class="device-model"><span class="meta-label">Model:</span> ' . esc_html($model) . '</span>';
                    }

                    // Display Status
                    $status = get_field('device_status');
                    if ($status) {
                        $status_labels = array(
                            'available' => 'Available',
                            'upcoming' => 'Upcoming',
                            'discontinued' => 'Discontinued',
                            'rumored' => 'Rumored'
                        );
                        // Ensure the key is lowercase for lookup
                        $status_key = strtolower($status);

                        if (isset($status_labels[$status_key])) {
                            echo '<span class="device-status status-' . esc_attr($status_key) . '">' . esc_html($status_labels[$status_key]) . '</span>';
                        } else {
                            // Fallback if the status is set but not defined in the labels array
                            echo '<span class="device-status status-unknown">' . esc_html(ucfirst($status)) . '</span>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="device-content-wrapper">
            <main class="device-main-content">

                <?php if (has_post_thumbnail()) : ?>
                    <section class="device-featured-image" style="margin-bottom: 30px;">
                        <div class="main-image" style="text-align: center;">
                            <?php the_post_thumbnail('featured-image', array('class' => 'featured-image', 'style' => 'max-height: 400px; width: auto; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);')); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                // --- CUSTOM INTERLEAVED REVIEW SECTIONS ---
                if (function_exists('get_field')) :
                    // For laptops, we should use different section names than mobile devices
                    $review_sections_data = array(
                        'introduction' => array(
                            'title' => 'Overview', 
                            'text_field' => 'review_introduction', 
                            'image_field' => 'review_introduction_image',
                            'is_main_content' => true
                        ),
                        'design_build' => array(
                            'title' => 'Design & Build Quality', 
                            'text_field' => 'laptop_design_build', 
                            'image_field' => 'laptop_design_build_image',
                            'is_main_content' => false
                        ),
                        'display' => array(
                            'title' => 'Display Quality', 
                            'text_field' => 'review_display', 
                            'image_field' => 'review_display_image',
                            'is_main_content' => false
                        ),
                        'performance' => array(
                            'title' => 'Performance & Benchmarks', 
                            'text_field' => 'review_performance', 
                            'image_field' => 'review_performance_image',
                            'is_main_content' => false
                        ),
                        'keyboard_trackpad' => array(
                            'title' => 'Keyboard & Trackpad', 
                            'text_field' => 'laptop_keyboard_trackpad', 
                            'image_field' => 'laptop_keyboard_trackpad_image',
                            'is_main_content' => false
                        ),
                        'battery' => array(
                            'title' => 'Battery Life & Charging', 
                            'text_field' => 'review_battery', 
                            'image_field' => 'review_battery_image',
                            'is_main_content' => false
                        ),
                        'connectivity' => array(
                            'title' => 'Connectivity & Ports', 
                            'text_field' => 'laptop_connectivity', 
                            'image_field' => 'laptop_connectivity_image',
                            'is_main_content' => false
                        ),
                    );

                    // Counter to track if we've processed the overview section
                    $overview_processed = false;
                    
                    foreach ($review_sections_data as $key => $section) :
                        // Get content - check if field exists before getting it
                        $content_text = '';
                        $image_url = '';
                        
                        if ($section['is_main_content']) {
                            // For introduction, use the main post content
                            $content_text = get_the_content();
                        } else {
                            // For other sections, try to get the ACF field
                            if (function_exists('get_field')) {
                                $content_text = get_field($section['text_field']);
                                $image_url = get_field($section['image_field']);
                            }
                        }

                        // Only display section if there's content or an image
                        if (!empty($content_text) || !empty($image_url)) :
                ?>
                            <section class="device-review-section device-<?php echo esc_attr($key); ?>">
                                <h2 class="section-title"><?php echo esc_html($section['title']); ?></h2>

                                <?php if (!empty($content_text)) : ?>
                                    <div class="post-content">
                                        <?php
                                        // Use the_content() only for the first section to handle formatting/blocks
                                        if ($section['is_main_content']) {
                                            the_content();
                                        } else {
                                            echo wpautop($content_text); // Use wpautop for ACF textareas
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($image_url)) : ?>
                                    <div class="review-image-wrapper">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title() . ' - ' . $section['title']); ?>" class="review-section-image" loading="lazy">
                                    </div>
                                <?php endif; ?>
                            </section>
                <?php
                            // Mark overview as processed
                            if ($key === 'introduction') {
                                $overview_processed = true;
                            }
                            
                            // Display YouTube video right after overview
                            if ($overview_processed && !isset($youtube_shown)) {
                                $youtube_video = get_field('youtube_video');
                                if ($youtube_video) : 
                                    $youtube_shown = true;
                                ?>
                                    <section class="device-video-review">
                                        <div class="video-wrapper">
                                            <div>
                                                <?php echo $youtube_video; ?>
                                            </div>
                                        </div>
                                    </section>
                                <?php endif;
                            }
                        endif;
                    endforeach;
                endif;
                // --- END CUSTOM INTERLEAVED REVIEW SECTIONS ---
                ?>

                <?php if (function_exists('get_field') && get_field('specifications')) : ?>
                    <section class="full-specifications">
                        <h2 class="section-title">Full Specifications</h2>
                        <table class="specs-table">
                            <tbody>
                                <?php
                                $specifications = get_field('specifications');
                                foreach ($specifications as $category) :
                                    if (empty($category['category']) || empty($category['items'])) continue;
                                ?>
                                    <tr class="spec-category">
                                        <th colspan="2"><?php echo esc_html($category['category']); ?></th>
                                    </tr>
                                    <?php foreach ($category['items'] as $item) : ?>
                                        <tr>
                                            <td class="spec-key"><?php echo esc_html($item['key']); ?></td>
                                            <td class="spec-value"><?php echo esc_html($item['value']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>

                <?php
                // Final Verdict Section (Now displayed after specifications)
                $final_verdict = get_field('review_verdict');
                $final_verdict_image = get_field('review_verdict_image');
                if (!empty($final_verdict) || !empty($final_verdict_image)) :
                ?>
                    <section class="device-review-section device-final-verdict">
                        <h2 class="section-title">Final Verdict</h2>
                        <?php if (!empty($final_verdict)) : ?>
                            <div class="post-content">
                                <?php echo wpautop($final_verdict); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($final_verdict_image)) : ?>
                            <div class="review-image-wrapper">
                                <img src="<?php echo esc_url($final_verdict_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?> - Final Verdict" class="review-section-image" loading="lazy">
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('pros_cons')) :
                    $pros_cons = get_field('pros_cons');
                ?>
                    <section class="pros-cons">
                        <h2 class="section-title">Pros & Cons</h2>
                        <div class="conti">
                            <div class="pros-cons-grid">
                                <div class="pros-section">
                                    <h3>👍 Pros (<?php echo count($pros_cons['pros']); ?>)</h3>
                                    <ul class="pros-list">
                                        <?php if ($pros_cons['pros']) : ?>
                                            <?php foreach ($pros_cons['pros'] as $pro) : ?>
                                                <li>
                                                    <span class="check-icon">✓</span>
                                                    <?php echo esc_html($pro['item']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="cons-section">
                                    <h3>👎 Cons (<?php echo count($pros_cons['cons']); ?>)</h3>
                                    <ul class="cons-list">
                                        <?php if ($pros_cons['cons']) : ?>
                                            <?php foreach ($pros_cons['cons'] as $con) : ?>
                                                <li>
                                                    <span class="cross-icon">✗</span>
                                                    <?php echo esc_html($con['item']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

            </main>
            <aside class="device-sidebar">
                <?php
                $current_post_id = get_the_ID();
                $device_limit = 10;
                $sidebar_devices = null;
                $widget_title = 'Similar Laptops';

                if (function_exists('ezoix_get_similar_laptop_devices')) : // MODIFIED FUNCTION NAME
                    // 1. Try to get similar devices
                    $sidebar_devices = ezoix_get_similar_laptop_devices($current_post_id, $device_limit); // MODIFIED FUNCTION NAME

                    // 2. Fallback: If no similar devices found, get random devices
                    if (!$sidebar_devices->have_posts()) {
                        // Reset query to run a new random query
                        wp_reset_query();

                        $sidebar_devices = new WP_Query(array(
                            'post_type'      => 'laptop_device', // MODIFIED POST TYPE
                            'post_status'    => 'publish',
                            'posts_per_page' => $device_limit,
                            'post__not_in'   => array($current_post_id),
                            'orderby'        => 'rand',
                            'no_found_rows'  => true,
                        ));
                        $widget_title = 'Other Popular Laptops';
                    }

                    // 3. Proceed only if we have posts (either similar or random)
                    if ($sidebar_devices->have_posts()) :
                ?>
                        <div class="similar-devices-widget sidebar-widget">
                            <h3 class="widget-title"><?php echo esc_html($widget_title); ?></h3>
                            <ul class="similar-devices-list">
                                <?php while ($sidebar_devices->have_posts()) : $sidebar_devices->the_post();
                                    $price = function_exists('get_field') ? get_field('device_price') : '';
                                ?>
                                    <li>
                                        <a href="<?php the_permalink(); ?>" class="similar-device-link">
                                            <div class="device-thumb-wrapper">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <?php the_post_thumbnail('thumbnail', array(
                                                        'loading' => 'lazy',
                                                        'class' => 'device-thumb-image',
                                                        'alt' => get_the_title()
                                                    )); ?>
                                                <?php else: ?>
                                                    <div class="device-thumb-placeholder">💻</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="device-details-text">
                                                <div class="device-title-small">
                                                    <?php the_title(); ?>
                                                </div>
                                                <?php if ($price) : ?>
                                                    <div class="device-price-small">
                                                        <?php echo esc_html($price); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endwhile;
                                wp_reset_postdata(); ?>
                            </ul>
                        </div>
                <?php
                    endif;
                endif;
                ?>

                <?php echo ezoix_get_most_recent_link(get_the_ID()); ?>
                <?php if (function_exists('get_field') && get_field('affiliate_links')) : ?>
                    <div class="cta-widget sidebar-widget">
                        <h3 class="widget-title">Ready to Buy?</h3>
                        <p>Check the best prices from our trusted partners:</p>
                        <?php
                        $affiliate_links = get_field('affiliate_links');
                        $first_link = reset($affiliate_links);
                        if ($first_link) :
                        ?>
                            <a href="<?php echo esc_url($first_link['url']); ?>"
                                class="cta-button"
                                target="_blank"
                                rel="nofollow noopener">
                                <button class="buynow">
                                    <span class="cta-icon">🛒</span>
                                    <span>Buy Now</span>
                                </button>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>

<?php get_footer(); ?>