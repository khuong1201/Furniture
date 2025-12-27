<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SYSTEM / SHARED (99)
    |--------------------------------------------------------------------------
    */

    '99' => [
        500990 => [
            'http' => 500,
            'message' => 'Internal server error',
            'description' => 'Unknown system failure occurred.',
        ],
        400991 => [
            'http' => 400,
            'message' => 'Invalid request',
            'description' => 'Request payload or format is invalid.',
        ],
        503992 => [
            'http' => 503,
            'message' => 'Service unavailable',
            'description' => 'Temporary overload or maintenance.',
        ],
        408993 => [
            'http' => 408,
            'message' => 'Request timeout',
            'description' => 'The server timed out waiting for the request.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | USER (01)
    |--------------------------------------------------------------------------
    */

    '01' => [
        404010 => [
            'http' => 404,
            'message' => 'User not found',
            'description' => 'The specified user does not exist.',
        ],
        409011 => [
            'http' => 409,
            'message' => 'Email already exists',
            'description' => 'Email address is already registered.',
        ],
        409012 => [
            'http' => 409,
            'message' => 'Phone already exists',
            'description' => 'Phone number is already registered.',
        ],
        403013 => [
            'http' => 403,
            'message' => 'User not verified',
            'description' => 'User must verify account before performing this action.',
        ],
        423014 => [
            'http' => 423,
            'message' => 'User locked',
            'description' => 'This user account is locked.',
        ],
        403015 => [
            'http' => 403, 
            'message' => 'Cannot delete your own account', 
            'description' => 'Self-deletion is restricted for admins.'
        ],
        400016 => [
            'http' => 400,
            'message' => 'Current password is incorrect',
            'description' => 'Password mismatch.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AUTH (02)
    |--------------------------------------------------------------------------
    */

    '02' => [
        401020 => [
            'http' => 401,
            'message' => 'Invalid token',
            'description' => 'Token malformed or tampered.',
        ],
        401021 => [
            'http' => 401,
            'message' => 'Token expired',
            'description' => 'Authentication token has expired.',
        ],
        401022 => [
            'http' => 401,
            'message' => 'Token missing',
            'description' => 'No authentication token provided.',
        ],
        403023 => [
            'http' => 403,
            'message' => 'Permission denied',
            'description' => 'User does not have required permissions.',
        ],
        429024 => [
            'http' => 429,
            'message' => 'Too many attempts',
            'description' => 'Too many authentication attempts.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ADDRESS (03)
    |--------------------------------------------------------------------------
    */

    '03' => [
        404030 => [
            'http' => 404,
            'message' => 'Address not found',
            'description' => 'The address does not exist.',
        ],
        400031 => [
            'http' => 400,
            'message' => 'Invalid address format',
            'description' => 'Address format does not match required structure.',
        ],
        403032 => [
            'http' => 403, 
            'message' => 'Cannot delete default address', 
            'description' => 'You must set another address as default before deleting this one.'
        ],
        400033 => [
            'http' => 400,
            'message' => 'Address limit reached',
            'description' => 'User cannot add more addresses.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | CART (04)
    |--------------------------------------------------------------------------
    */

    '04' => [
        404040 => [
            'http' => 404,
            'message' => 'Cart not found',
            'description' => 'Cart does not exist for this user.',
        ],
        404041 => [
            'http' => 404, 
            'message' => 'Cart item not found', 
            'description' => 'Item uuid not found in cart.'
        ],
        409042 => [
            'http' => 409, 
            'message' => 'Out of stock', 
            'description' => 'Requested quantity exceeds available stock.'
        ],
        422043 => [
            'http' => 422, 
            'message' => 'Product variant unavailable', 
            'description' => 'Variant is inactive or deleted.'
        ],
        400044 => [
            'http' => 400, 
            'message' => 'Cart is empty', 
            'description' => 'Cannot perform action on empty cart.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CATEGORY (05)
    |--------------------------------------------------------------------------
    */

    '05' => [
        404050 => [
            'http' => 404,
            'message' => 'Category not found',
            'description' => 'Requested category does not exist.',
        ],
        409051 => [
            'http' => 409,
            'message' => 'Category already exists',
            'description' => 'Category name duplicated.',
        ],
        422052 => [
            'http' => 422, 
            'message' => 'Invalid parent category', 
            'description' => 'Circular reference detected.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | COLLECTION (06)
    |--------------------------------------------------------------------------
    */

    '06' => [
        404060 => [
            'http' => 404,
            'message' => 'Collection not found',
            'description' => 'Requested collection does not exist.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CURRENCY (07)
    |--------------------------------------------------------------------------
    */

    '07' => [
        404070 => [
            'http' => 404,
            'message' => 'Currency not found',
            'description' => 'Unsupported or missing currency type.',
        ],
        409071 => [
            'http' => 409, 
            'message' => 'Cannot delete default currency',
            'description' => 'The system requires at least one default currency.',
        ],
        409072 => [
            'http' => 409,
            'message' => 'Currency code exists',
            'description' => 'Currency code must be unique.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD (08)
    |--------------------------------------------------------------------------
    */

    '08' => [
        500080 => [
            'http' => 500,
            'message' => 'Dashboard query failed',
            'description' => 'System cannot aggregate dashboard data.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | INVENTORY (09)
    |--------------------------------------------------------------------------
    */

    '09' => [
        404090 => [
            'http' => 404,
            'message' => 'SKU not found',
            'description' => 'SKU does not exist in inventory.',
        ],
        409091 => [
            'http' => 409, 
            'message' => 'Out of stock', 
            'description' => 'Insufficient quantity to fulfill the request.'
        ],
        400092 => [
            'http' => 400,
            'message' => 'Invalid stock adjustment',
            'description' => 'Resulting quantity cannot be negative.'
        ],
        500093 => [
            'http' => 500,
            'message' => 'Inventory sync error',
            'description' => 'Warehouse sync service failed.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | LOG (10)
    |--------------------------------------------------------------------------
    */

    '10' => [
        500100 => [
            'http' => 500,
            'message' => 'Log write failed',
            'description' => 'Unable to persist log entry.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MEDIA (11)
    |--------------------------------------------------------------------------
    */

    '11' => [
        404110 => [
            'http' => 404,
            'message' => 'Media not found',
            'description' => 'File or media record not found.',
        ],
        400111 => [
            'http' => 400,
            'message' => 'Invalid media type',
            'description' => 'Unsupported MIME or type mismatch.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION (12)
    |--------------------------------------------------------------------------
    */

    '12' => [
        500120 => [
            'http' => 500,
            'message' => 'Notification send failed',
            'description' => 'Provider or gateway failure.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ORDER (13)
    |--------------------------------------------------------------------------
    */

    '13' => [
        404130 => [
            'http' => 404,
            'message' => 'Order not found',
            'description' => 'The requested order cannot be found.',
        ],
        422131 => [
            'http' => 422, 
            'message' => 'Invalid order items', 
            'description' => 'Cart is empty or items invalid.'
        ],
        409132 => [
            'http' => 409, 
            'message' => 'Cannot cancel order', 
            'description' => 'Order is already shipped or delivered.'
        ],
        400133 => [
            'http' => 400, 
            'message' => 'Checkout validation failed', 
            'description' => 'Missing address or payment info.'
        ],
        500134 => [
            'http' => 500,
            'message' => 'Order processing error',
            'description' => 'Unexpected order service failure.',
        ],
        422135 => [ 
            'http' => 422,
            'message' => 'Product unavailable',
            'description' => 'One or more products in the order no longer exist or are inactive.'
        ],
        409136 => [ 
            'http' => 409,
            'message' => 'Out of stock',
            'description' => 'Insufficient inventory for one or more items.'
        ],
        409137 => [ 
            'http' => 409,
            'message' => 'Invalid status transition',
            'description' => 'Cannot change status from current state to the requested state.'
        ],
        409138 => [ 
            'http' => 409,
            'message' => 'Order already cancelled',
            'description' => 'Cannot modify an order that has already been cancelled.'
        ],
        402134 => [ 
            'http' => 402, 
            'message' => 'Payment required for delivery',
            'description' => 'Cannot set order to delivered because it has not been paid yet.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PAYMENT (14)
    |--------------------------------------------------------------------------
    */

    '14' => [
        404140 => [
            'http' => 404, 
            'message' => 'Payment not found', 
            'description' => 'Transaction ID invalid.'
        ],
        400141 => [
            'http' => 400, 
            'message' => 'Payment method not supported', 
            'description' => 'Gateway invalid.'
        ],
        400142 => [
            'http' => 400, 
            'message' => 'Invalid signature', 
            'description' => 'Webhook signature verification failed.'
        ],
        409143 => [
            'http' => 409, 
            'message' => 'Order already paid', 
            'description' => 'Cannot pay for a paid order.'
        ],
        '400994' => [
            'http' => 400,
            'message' => 'COD Payment logic violation',
            'description' => 'COD orders must be shipped before they can be marked as paid.'
        ],
        502144 => [
            'http' => 502, 
            'message' => 'Payment Gateway Error', 
            'description' => 'Third-party provider failed.'
        ],
        500144 => [
            'http' => 500,
            'message' => 'Gateway error',
            'description' => 'Payment gateway internal error.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PERMISSION (15)
    |--------------------------------------------------------------------------
    */

    '15' => [
        404150 => [
            'http' => 404,
            'message' => 'Permission not found',
            'description' => 'Requested permission does not exist.',
        ],
        409151 => [
            'http' => 409,
            'message' => 'Permission name already exists',
            'description' => 'Duplicate permission identifier.',
        ],
        403152 => [
            'http' => 403,
            'message' => 'Cannot delete permission attached to roles',
            'description' => 'Permission is currently assigned to one or more roles.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PRODUCT (16)
    |--------------------------------------------------------------------------
    */

    '16' => [
        404160 => [
            'http' => 404,
            'message' => 'Product not found',
            'description' => 'Product does not exist.',
        ],
        409161 => [
            'http' => 409,
            'message' => 'Product already exists',
            'description' => 'Duplicate product code or name.',
        ],
        400162 => [
            'http' => 400,
            'message' => 'Invalid product data',
            'description' => 'Invalid attributes or required fields missing.',
        ],
        409163 => [
            'http' => 409, 
            'message' => 'Attribute slug already exists',
            'description' => 'The attribute slug you provided is already used by another attribute.',
        ],
        422162 => [
            'http' => 422, 
            'message' => 'Duplicate SKU in variants', 
            'description' => 'Variants list contains duplicate SKUs.'
        ],
        422163 => [
            'http' => 422, 
            'message' => 'Invalid attributes', 
            'description' => 'One or more attributes do not exist.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PROMOTION (17)
    |--------------------------------------------------------------------------
    */

    '17' => [
        404170 => [
            'http' => 404,
            'message' => 'Promotion not found',
            'description' => 'Promotion code is invalid.',
        ],
        400171 => [
            'http' => 400,
            'message' => 'Promotion expired',
            'description' => 'Promotion has reached expiration time.',
        ],
        409172 => [
            'http' => 409,
            'message' => 'Promotion conflict',
            'description' => 'Promotion cannot be applied.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | REVIEW (18)
    |--------------------------------------------------------------------------
    */

    '18' => [
        404180 => [
            'http' => 404, 
            'message' => 'Review not found', 
            'description' => 'Review ID invalid.'
        ],
        409181 => [
            'http' => 409, 
            'message' => 'Product already reviewed', 
            'description' => 'User can only review a product once.'
        ],
        403182 => [
            'http' => 403, 
            'message' => 'Purchase required', 
            'description' => 'You must purchase this product to review.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ROLE (19)
    |--------------------------------------------------------------------------
    */

    '19' => [
        404190 => [
            'http' => 404,
            'message' => 'Role not found',
            'description' => 'Requested role does not exist.',
        ],
        409191 => [
            'http' => 409,
            'message' => 'Role name or slug already exists',
            'description' => 'Duplicate role identifier.',
        ],
        403192 => [
            'http' => 403,
            'message' => 'Cannot modify system role',
            'description' => 'System roles are protected from modification or deletion.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SHIPPING (20)
    |--------------------------------------------------------------------------
    */

    '20' => [
        404200 => [
            'http' => 404, 
            'message' => 'Shipping not found', 
            'description' => 'Shipping UUID invalid.'
        ],
        409201 => [
            'http' => 409, 
            'message' => 'Tracking number exists', 
            'description' => 'Tracking number must be unique.'
        ],
        422202 => [
            'http' => 422, 
            'message' => 'Invalid order status for shipping', 
            'description' => 'Cannot ship cancelled or completed orders.'
        ],
        500201 => [
            'http' => 500,
            'message' => 'Shipping calculation failed',
            'description' => 'Unable to compute shipping cost.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | VOUCHER (21)
    |--------------------------------------------------------------------------
    */

    '21' => [
        404210 => [
            'http' => 404,
            'message' => 'Voucher not found',
            'description' => 'Voucher code does not exist.',
        ],
        400211 => [
            'http' => 400, 
            'message' => 'Voucher invalid', 
            'description' => 'Expired or usage limit reached.'],
        400212 => [
            'http' => 400, 
            'message' => 'Minimum order value not met', 
            'description' => 'Cart total is too low.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WAREHOUSE (22)
    |--------------------------------------------------------------------------
    */

    '22' => [
        404220 => [
            'http' => 404,
            'message' => 'Warehouse not found',
            'description' => 'Warehouse code invalid.',
        ],
        409221 => [
            'http' => 409, 
            'message' => 'Warehouse name already exists', 
            'description' => 'Name must be unique.'
        ],
        409222 => [
            'http' => 409, 
            'message' => 'Cannot delete warehouse with stock', 
            'description' => 'Warehouse contains inventory items (quantity > 0).'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WISHLIST (23)
    |--------------------------------------------------------------------------
    */

    '23' => [
        404230 => [
            'http' => 404, 'message' => 
            'Wishlist item not found', 
            'description' => 'Item does not exist in wishlist.'
        ],
        400231 => [
            'http' => 400, 
            'message' => 'Wishlist limit reached', 
            'description' => 'User cannot add more items.'
        ],
    ],

];