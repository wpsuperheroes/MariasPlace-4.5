<?php

namespace GeminiLabs\SiteReviews\Addon\Woocommerce\Widgets;

use GeminiLabs\SiteReviews\Addon\Woocommerce\Application;
use GeminiLabs\SiteReviews\Helpers\Arr;
use GeminiLabs\SiteReviews\Modules\Rating;
use GeminiLabs\SiteReviews\Modules\Style;

class WidgetRatingFilter extends \WC_Widget_Rating_Filter
{
    /**
     * @param array $args
     * @param array $instance
     * @return void
     */
    public function widget($args, $instance)
    {
        if (!$this->isWidgetVisible()) {
            return;
        }
        if ($filters = $this->filteredProductCounts()) {
            ob_start();
            $this->widget_start($args, $instance);
            glsr(Application::class)->render('templates/widgets/rating-filter', [
                'filters' => $filters,
                'style' => 'glsr glsr-'.glsr(Style::class)->get(),
            ]);
            $this->widget_end($args);
            echo ob_get_clean(); // WPCS: XSS ok.
        }
    }

    /**
     * @return array
     */
    protected function filteredProductCounts()
    {
        $averages = $this->productAverages();
        $baseUrl = remove_query_arg('paged', $this->get_current_page_url());
        $filteredRatings = explode(',', wp_unslash(filter_input(INPUT_GET, 'rating_filter')));
        $filteredRatings = Arr::uniqueInt($filteredRatings);
        $filters = [];
        foreach ($averages as $rating => $count) {
            if (empty($count)) {
                continue;
            }
            $isFiltered = in_array($rating, $filteredRatings, true);
            $linkRatings = call_user_func($isFiltered ? 'array_diff' : 'array_merge', $filteredRatings, [$rating]);
            $linkRatings = implode(',', $linkRatings);
            $link = $linkRatings ? add_query_arg('rating_filter', $linkRatings, $baseUrl) : remove_query_arg('rating_filter');
            $link = apply_filters('woocommerce_rating_filter_link', $link);
            $countHtml = apply_filters('woocommerce_rating_filter_count', "({$count})", $count, $rating);
            $countHtml = wp_kses($countHtml, ['em' => [], 'span' => [], 'strong' => []]);
            $filters[] = glsr()->args([
                'classes' => esc_attr('wc-layered-nav-rating'.($isFiltered ? ' chosen' : '')),
                'count' => $countHtml,
                'permalink' => esc_url($link),
                'stars' => str_replace(['<div', 'div>'], ['<span', 'span>'], glsr_star_rating($rating)),
            ]);
        }
        return $filters;
    }

    /**
     * @return bool
     */
    protected function isWidgetVisible()
    {
        if (!is_shop() && !is_product_taxonomy()) {
            return false;
        }
        if (!WC()->query->get_main_query()->post_count) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    protected function productAverages()
    {
        global $wpdb;
        $productIds = wc_get_products(['limit' => -1, 'return' => 'ids']);
        $productIds = implode(',', $productIds);
        $products = $wpdb->get_results("
            SELECT apt.post_id AS product_id, ROUND(AVG(r.rating)) AS average
            FROM {$wpdb->prefix}glsr_ratings AS r 
            INNER JOIN {$wpdb->prefix}glsr_assigned_posts AS apt ON r.ID = apt.rating_id
            INNER JOIN {$wpdb->posts} AS p ON (apt.post_id = p.ID AND p.post_type IN ('product'))
            WHERE 1=1 
            AND apt.is_published = 1 
            AND r.is_approved = 1 
            AND r.rating > 0 
            AND r.type = 'local'
            GROUP BY apt.post_id
        ");
        $averages = array_reverse(glsr(Rating::class)->emptyArray(), true); // preserve keys
        foreach ($products as $product) {
            ++$averages[$product->average];
        }
        return $averages;
    }
}
