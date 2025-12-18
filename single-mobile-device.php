<?php

/**
 * Template for displaying single mobile devices - SIMPLIFIED UI
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
                    $brands = get_the_terms(get_the_ID(), 'mobile_brand');
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
                    $review_sections_data = array(
                        // The introduction text is stored in the main post_content
                        'introduction' => array('title' => 'Overview', 'text_field' => 'review_introduction', 'image_field' => 'review_introduction_image'),
                        'display'      => array('title' => 'Display Experience', 'text_field' => 'review_display', 'image_field' => 'review_display_image'),
                        'performance'  => array('title' => 'Performance and Gaming', 'text_field' => 'review_performance', 'image_field' => 'review_performance_image'),
                        'camera'       => array('title' => 'Camera Performance', 'text_field' => 'review_camera', 'image_field' => 'review_camera_image'),
                        'battery'      => array('title' => 'Battery and Charging', 'text_field' => 'review_battery', 'image_field' => 'review_battery_image'),
                    );

                    // Counter to track if we've processed the overview section
                    $overview_processed = false;
                    
                    foreach ($review_sections_data as $key => $section) :
                        // Get content
                        $content_text = ($key === 'introduction') ? get_the_content() : get_field($section['text_field']);
                        $image_url = get_field($section['image_field']);

                        if (!empty($content_text) || !empty($image_url)) :
                ?>
                            <section class="device-review-section device-<?php echo esc_attr($key); ?> full-specifications">
                                <h2 class="section-title"><?php echo esc_html($section['title']); ?></h2>

                                <?php if (!empty($content_text)) : ?>
                                    <div class="post-content">
                                        <?php
                                        // Use the_content() only for the first section to handle formatting/blocks
                                        if ($key === 'introduction') {
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
                                    <section class="device-video-review full-specifications" style="margin-bottom: 30px;">
                                        <h2 class="section-title">Video Review & Show</h2>
                                        <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
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
                    <h2 class="section-title">Pros & Cons</h2>
                    <section class="pros-cons">
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
                $widget_title = 'Similar Devices';

                if (function_exists('ezoix_get_similar_mobile_devices')) :
                    // 1. Try to get similar devices
                    $sidebar_devices = ezoix_get_similar_mobile_devices($current_post_id, $device_limit);

                    // 2. Fallback: If no similar devices found, get random devices
                    if (!$sidebar_devices->have_posts()) {
                        // Reset query to run a new random query
                        wp_reset_query();

                        $sidebar_devices = new WP_Query(array(
                            'post_type'      => 'mobile_device',
                            'post_status'    => 'publish',
                            'posts_per_page' => $device_limit,
                            'post__not_in'   => array($current_post_id),
                            'orderby'        => 'rand',
                            'no_found_rows'  => true,
                        ));
                        $widget_title = 'Other Popular Devices';
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
                                                    <div class="device-thumb-placeholder">📱</div>
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