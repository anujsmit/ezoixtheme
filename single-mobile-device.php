<?php

/**
 * Template for displaying single mobile devices - SIMPLIFIED UI
 * * This file replaces the complex gallery and accordion with a single, table-based specification layout.
 */

get_header(); ?>

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

                <?php if (get_the_content()) : ?>
                    <section class="device-description full-specifications">
                        <h2 class="section-title">Overview</h2>
                        <div class="post-content">
                            <?php the_content(); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('specifications')) :
                    $specifications = get_field('specifications');
                ?>
                    <section class="full-specifications simple-specs">
                        <h2 class="section-title">Full Specifications</h2>

                        <table class="specs-table" style="width: 100%; border-collapse: collapse;">
                            <tbody>
                                <?php
                                foreach ($specifications as $category) :
                                    if (!empty($category['items'])) :
                                ?>
                                        <tr class="category-header">
                                            <td colspan="2" style="background: var(--primary-blue-light); color: var(--primary-blue-dark); padding: 15px 20px; font-weight: 700; font-size: 18px; border-top: 2px solid var(--primary-blue);">
                                                <?php echo esc_html($category['category']); ?>
                                            </td>
                                        </tr>

                                        <?php foreach ($category['items'] as $item) : ?>
                                            <tr class="spec-row" style="border-bottom: 1px solid var(--border-light);">
                                                <td class="spec-key" style="padding: 12px 20px; font-weight: 600; color: var(--text-medium); width: 35%; background: var(--background-light); border-right: 1px solid var(--border-light);">
                                                    <?php echo esc_html($item['key']); ?>
                                                </td>
                                                <td class="spec-value" style="padding: 12px 20px; color: var(--text-dark);">
                                                    <?php echo esc_html($item['value']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('pros_cons')) :
                    $pros_cons = get_field('pros_cons');
                ?>
                    <h2 class="section-title">Pros & Cons</h2>
                    <section class="pros-cons">
                        <div class="conti">
                            <div class="pros-cons-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="pros-section" style="padding: 20px; border-radius: 8px; background: rgba(46, 204, 113, 0.1); border-left: 4px solid var(--color-success);">
                                    <h3 style="color: var(--color-success); font-size: 18px; margin-top: 0; margin-bottom: 15px;">👍 Pros (<?php echo count($pros_cons['pros']); ?>)</h3>
                                    <ul class="pros-list" style="list-style: none; padding: 0;">
                                        <?php if ($pros_cons['pros']) : ?>
                                            <?php foreach ($pros_cons['pros'] as $pro) : ?>
                                                <li style="padding: 8px 0; border-bottom: 1px dashed rgba(46, 204, 113, 0.3); display: flex; align-items: center; gap: 8px;">
                                                    <span class="check-icon" style="color: var(--color-success);">✓</span>
                                                    <?php echo esc_html($pro['item']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="cons-section" style="padding: 20px; border-radius: 8px; background: rgba(231, 76, 60, 0.1); border-left: 4px solid var(--color-danger);">
                                    <h3 style="color: var(--color-danger); font-size: 18px; margin-top: 0; margin-bottom: 15px;">👎 Cons (<?php echo count($pros_cons['cons']); ?>)</h3>
                                    <ul class="cons-list" style="list-style: none; padding: 0;">
                                        <?php if ($pros_cons['cons']) : ?>
                                            <?php foreach ($pros_cons['cons'] as $con) : ?>
                                                <li style="padding: 8px 0; border-bottom: 1px dashed rgba(231, 76, 60, 0.3); display: flex; align-items: center; gap: 8px;">
                                                    <span class="cross-icon" style="color: var(--color-danger);">✗</span>
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
                    // NEW: Similar Devices Widget
                    if (function_exists('ezoix_get_similar_mobile_devices')) :
                        $similar_devices = ezoix_get_similar_mobile_devices(get_the_ID(), 10);

                        if ($similar_devices->have_posts()) :
                    ?>
                            <div class="similar-devices-widget sidebar-widget">
                                <h3 class="widget-title">Similar Devices</h3>
                                <ul class="similar-devices-list" style="list-style: none; padding: 0; margin: 0;">
                                    <?php while ($similar_devices->have_posts()) : $similar_devices->the_post();
                                        $price = function_exists('get_field') ? get_field('device_price') : '';
                                    ?>
                                        <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-light);">
                                            <a href="<?php the_permalink(); ?>" style="display: flex; gap: 10px; align-items: center; color: var(--text-dark);">
                                                <div style="flex-shrink: 0;">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <?php the_post_thumbnail('thumbnail', array(
                                                            'loading' => 'lazy',
                                                            'style' => 'width: 60px; height: 60px; object-fit: cover; border-radius: 6px;',
                                                            'alt' => get_the_title()
                                                        )); ?>
                                                    <?php else: ?>
                                                        <div style="width: 60px; height: 60px; background: var(--background-light); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px;">📱</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="flex-grow: 1;">
                                                    <div style="font-weight: 600; font-size: 15px; line-height: 1.3; margin-bottom: 2px;">
                                                        <?php the_title(); ?>
                                                    </div>
                                                    <?php if ($price) : ?>
                                                        <div style="color: var(--primary-blue-dark); font-size: 13px; font-weight: 700;">
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