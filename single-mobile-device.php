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
                        echo '<span class="device-status status-' . esc_attr($status) . '">' . esc_html($status_labels[$status]) . '</span>';
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
                // All sidebar widgets remain functional, fetching their respective ACF fields.
                ?>
                <?php if (function_exists('get_field') && get_field('device_rating')) : ?>
                    <div class="rating-widget sidebar-widget">
                        <h3 class="widget-title">Our Rating</h3>
                        <div class="rating-score">
                            <span class="score"><?php echo number_format(get_field('device_rating') / 2, 1); ?>/5</span>
                            <div class="stars">
                                <?php
                                $rating = get_field('device_rating');
                                $full_stars = floor($rating / 2);
                                $half_star = ($rating / 2) - $full_stars >= 0.5;
                                for ($i = 1; $i <= 5; $i++) :
                                    if ($i <= $full_stars) {
                                        echo '<span class="star full">★</span>';
                                    } elseif ($i == $full_stars + 1 && $half_star) {
                                        echo '<span class="star half">★</span>';
                                    } else {
                                        echo '<span class="star empty">★</span>';
                                    }
                                endfor;
                                ?>
                            </div>
                            <div class="rating-bar">
                                <div class="bar-fill" style="width: <?php echo ($rating / 10) * 100; ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('device_price')) : ?>
                    <div class="price-widget sidebar-widget">
                        <h3 class="widget-title">Price</h3>
                        <div class="price-value"><?php echo esc_html(get_field('device_price')); ?></div>
                        <p class="price-note">Official price at launch</p>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('release_date')) : ?>
                    <div class="release-widget sidebar-widget">
                        <h3 class="widget-title">Release Date</h3>
                        <div class="release-date"><?php echo esc_html(get_field('release_date')); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('device_status')) : ?>
                    <div class="status-widget sidebar-widget">
                        <h3 class="widget-title">Status</h3>
                        <div class="status-badge status-<?php echo esc_attr(get_field('device_status')); ?>">
                            <?php
                            $status = get_field('device_status');
                            $status_labels = array(
                                'available' => 'Available',
                                'upcoming' => 'Upcoming',
                                'discontinued' => 'Discontinued',
                                'rumored' => 'Rumored'
                            );
                            echo isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="share-widget sidebar-widget">
                    <h3 class="widget-title">Share This Device</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
                            target="_blank" class="share-button facebook" title="Share on Facebook">
                            <span class="icon">f</span>
                            <span class="label">Facebook</span>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                            target="_blank" class="share-button twitter" title="Share on Twitter">
                            <span class="icon">𝕏</span>
                            <span class="label">Twitter</span>
                        </a>
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
                            target="_blank" class="share-button whatsapp" title="Share on WhatsApp">
                            <span class="icon">WhatsApp</span>
                            <span class="label">WhatsApp</span>
                        </a>
                        <a href="mailto:?subject=<?php echo rawurlencode(get_the_title()); ?>&body=<?php echo rawurlencode(get_permalink()); ?>"
                            class="share-button email" title="Share via Email">
                            <span class="icon">✉</span>
                            <span class="label">Email</span>
                        </a>
                    </div>
                </div>

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
                                <span class="cta-icon">🛒</span>
                                <span>Buy Now</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>

<?php get_footer(); ?>