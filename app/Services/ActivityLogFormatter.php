<?php

namespace App\Services;

use App\Models\ProductVariation;
use Illuminate\Support\Str;

class ActivityLogFormatter
{
    /**
     * Custom labels for attributes that need specific wording.
     */
    protected array $labels = [
        // General fields
        'id' => 'ID',
        'sku' => 'SKU',
        'uuid' => 'UUID',
        'ip' => 'IP Address',
        'url' => 'URL',

        // Product fields
        'product_id' => 'Product',
        'variation_id' => 'Variation',
        'attribute_id' => 'Attribute',
        'attribute_value_id' => 'Attribute value',

        // Product variation fields
        'price' => 'Price',
        'sale_price' => 'Sale price',
        'is_default' => 'Default variation',
        'stock_quantity' => 'Stock quantity',
        'low_stock_threshold' => 'Low stock threshold',

        // Coupon fields
        'first_order_only' => 'First order only',
        'min_order_amount' => 'Minimum order amount',
        'max_discount' => 'Maximum discount',
        'per_customer_limit' => 'Per-customer limit',
        'usage_limit' => 'Usage limit',

        // Common status fields
        'is_active' => 'Active',
        'is_enabled' => 'Enabled',
        'is_featured' => 'Featured',
        'is_required' => 'Required',
        'is_default' => 'Default',

        // Product fields
        'show_on_homepage' => 'Show on homepage',

        // Date fields
        'starts_at' => 'Start date',
        'expires_at' => 'Expiry date',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
        'deleted_at' => 'Deleted at',

        // Other common fields
        'min_quantity' => 'Minimum quantity',
        'max_quantity' => 'Maximum quantity',
        'sort_order' => 'Sort order',
        'display_order' => 'Display order',
        'description' => 'Description',
        'name' => 'Name',
        'title' => 'Title',
        'type' => 'Type',
        'status' => 'Status',
        'value' => 'Value',
        'label' => 'Label',
    ];

    /**
     * Fields that should never be exposed in activity log details.
     */
    protected array $hiddenFields = [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'access_token',
        'refresh_token',
        'client_secret',
        'secret',
        'secret_key',
        'private_key',
        'verification_token',
        'two_factor_secret',
        'recovery_codes',
    ];

    /**
     * Recognized boolean field prefixes.
     */
    protected array $booleanPrefixes = [
        'is_',
        'has_',
        'can_',
        'allow_',
        'allows_',
        'require_',
        'requires_',
        'enable_',
        'enabled_',
        'should_',
    ];

    /**
     * Explicit boolean fields that do not follow the usual prefixes.
     */
    protected array $booleanFields = [
        'first_order_only',
    ];

    /**
     * Cache resolved variation labels to avoid repeated queries.
     */
    protected array $variationLabels = [];

    /**
     * Format activity log records.
     */
    public function format(?array $records): array
    {
        if (empty($records)) {
            return [];
        }

        $this->variationLabels = [];

        return $this->formatAttributes($records);
    }

    /**
     * Recursively format attributes and nested records.
     *
     * When processing the variations collection, replace
     * variation UUID keys with their product attribute values.
     */
    protected function formatAttributes(
        array $records,
        ?string $parentKey = null
    ): array {
        $formatted = [];

        foreach ($records as $key => $value) {
            if (is_string($key) && $this->isHiddenField($key)) {
                continue;
            }

            if (
                is_string($key)
                && strtolower($parentKey ?? '') === 'variations'
            ) {
                $label = $this->getVariationLabel($key);
            } elseif (
                is_int($key)
                || (is_string($key) && $this->isUuidKey($key))
            ) {
                // Preserve numeric indexes and other UUID keys.
                $label = $key;
            } else {
                $label = is_string($key)
                    ? $this->getLabel($key)
                    : $key;
            }

            $formatted[$label] = $this->formatValue($key, $value);
        }

        return $formatted;
    }

    /**
     * Resolve a variation's human-readable attribute values.
     */
    protected function getVariationLabel(string $variationId): string
    {
        if (array_key_exists($variationId, $this->variationLabels)) {
            return $this->variationLabels[$variationId];
        }

        $normalizedId = $this->normalizeUuid($variationId);

        if (!$normalizedId) {
            return $variationId;
        }

        $variation = ProductVariation::query()
            ->with('attributeValues.attribute')
            ->find($normalizedId);

        if (!$variation) {
            return $this->variationLabels[$variationId] = $variationId;
        }

        $attributes = $variation->attributeValues
            ->sortBy(fn ($attributeValue) => strtolower(
                $attributeValue->attribute?->name ?? ''
            ))
            ->map(function ($attributeValue) {
                $attributeName = $attributeValue->attribute?->name;
                $attributeValueName = $attributeValue->value;

                if (!$attributeName || !$attributeValueName) {
                    return null;
                }

                return "{$attributeName}: {$attributeValueName}";
            })
            ->filter()
            ->values()
            ->all();

        if (!empty($attributes)) {
            $label = implode(', ', $attributes);
        } elseif (!empty($variation->sku)) {
            // Use SKU as a fallback when no attribute values exist.
            $label = $variation->sku;
        } else {
            // Keep the original ID if no readable identifier exists.
            $label = $variationId;
        }

        return $this->variationLabels[$variationId] = $label;
    }

    /**
     * Normalize UUID keys for database lookup.
     *
     * Supports standard hyphenated UUIDs and UUIDs whose
     * hyphens have been replaced by spaces in stored data.
     */
    protected function normalizeUuid(string $value): ?string
    {
        $normalized = preg_replace('/[^a-f0-9]/i', '', $value);

        if (!$normalized || strlen($normalized) !== 32) {
            return null;
        }

        if (!ctype_xdigit($normalized)) {
            return null;
        }

        $uuid = substr($normalized, 0, 8) . '-'
            . substr($normalized, 8, 4) . '-'
            . substr($normalized, 12, 4) . '-'
            . substr($normalized, 16, 4) . '-'
            . substr($normalized, 20, 12);

        return strtolower($uuid);
    }

    /**
     * Determine whether a key represents a UUID.
     */
    protected function isUuidKey(string $key): bool
    {
        return $this->normalizeUuid($key) !== null;
    }

    /**
     * Resolve a human-readable attribute label.
     */
    protected function getLabel(string $key): string
    {
        return $this->labels[$key] ?? Str::headline($key);
    }

    /**
     * Format an individual value.
     */
    protected function formatValue(string|int $key, mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->formatAttributes(
                $value,
                is_string($key) ? $key : null
            );
        }

        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (
            is_string($key)
            && $this->isBooleanField($key)
            && $this->isBooleanValue($value)
        ) {
            return (bool) $value ? 'Yes' : 'No';
        }

        // Preserve original values such as SKUs, prices and codes.
        return $value;
    }

    /**
     * Determine whether an attribute represents a boolean field.
     */
    protected function isBooleanField(string $key): bool
    {
        if (in_array($key, $this->booleanFields, true)) {
            return true;
        }

        foreach ($this->booleanPrefixes as $prefix) {
            if (Str::startsWith($key, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether a value can safely be interpreted as boolean.
     */
    protected function isBooleanValue(mixed $value): bool
    {
        return in_array($value, [true, false, 1, 0, '1', '0'], true);
    }

    /**
     * Determine whether an attribute should be excluded.
     */
    protected function isHiddenField(string $key): bool
    {
        return in_array(
            strtolower($key),
            $this->hiddenFields,
            true
        );
    }
}
