<?php

namespace GeminiLabs\SiteReviews\Addon\Woocommerce\Controllers\BlocksApi;

use Automattic\WooCommerce\StoreApi\SchemaController;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\AbstractSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\ImageAttachmentSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\ProductReviewSchema as Schema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use GeminiLabs\SiteReviews\Helpers\Arr;
use GeminiLabs\SiteReviews\Review;

class ProductReviewSchema extends AbstractSchema
{
    /**
     * @var string
     */
    public const IDENTIFIER = 'product-review';

    /**
     * @var ImageAttachmentSchema
     */
    protected $image_attachment_schema;

    /**
     * @var string
     */
    protected $title = 'product_review';

    /**
     * @param ExtendSchema $extend
     * @param SchemaController $controller
     */
    public function __construct(ExtendSchema $extend, SchemaController $controller)
    {
        parent::__construct($extend, $controller);
        $this->image_attachment_schema = $this->controller->get(ImageAttachmentSchema::IDENTIFIER);
    }

    /**
     * @return array
     */
    public function get_item_response(Review $review)
    {
        $productId = Arr::get($review->assigned_posts, 0);
        $data = [
            'id' => $review->ID,
            'date_created' => wc_rest_prepare_date_response($review->date),
            'formatted_date_created' => mysql2date('F j, Y', $review->date),
            'date_created_gmt' => wc_rest_prepare_date_response($review->date_gmt),
            'product_id' => $productId,
            'product_name' => get_the_title($productId),
            'product_permalink' => get_permalink($productId),
            'product_image' => $this->image_attachment_schema->get_item_response(get_post_thumbnail_id($productId)),
            'reviewer' => $review->author,
            'review' => wpautop($review->content),
            'rating' => $review->rating,
            'verified' => wc_review_is_from_verified_owner($review->isVerified()),
            'reviewer_avatar_urls' => rest_get_avatar_urls($review->email),
        ];
        return $data;
    }

    /**
     * @return array
     */
    public function get_properties()
    {
        $schema = new Schema(
            StoreApi::container()->get(ExtendSchema::class),
            StoreApi::container()->get(SchemaController::class)
        );
        return $schema->get_properties();
    }
}
