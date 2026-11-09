<?php
return [

    "exception_error" => "Something went wrong.",

    // Add To Cart Validation

    // 'user_id' => [
    //     'required' => 'User ID is required.',
    //     'exists' => 'The selected user does not exist.',
    //     'integer' => 'User ID must be an integer.',
    // ],

    'variant_id' => [
        'required' => 'Variant ID is required.',
        'exists' => 'The selected variant does not exist.',
        'integer' => 'Variant ID must be an integer.',
    ],

    'quantity' => [
        'required' => 'Quantity is required.',
        'integer' => 'Quantity must be an integer.',
        'min' => 'Quantity must be at least 1.',
    ],

    'exceeded_quantity' => 'The requested quantity exceeds available stock.',

    // Delete Cart List Validation

    'cart_id' => [
        'required' => 'Cart ID is required.',
        'exists' => 'The selected cart item does not exist.',
        'integer' => 'Cart ID must be an integer.',
    ],

    // Add To Wishlist Validation

    'product_id' => [
        'required' => 'Product ID is required.',
        'exists' => 'The selected product does not exist.',
        'integer' => 'Product ID must be an integer.',
    ],

    // Delete Wishlist Validation

    'wishlist_id' => [
        'required' => 'Wishlist ID is required.',
        'exists' => 'The selected wishlist item does not exist.',
        'integer' => 'Wishlist ID must be an integer.',
    ],

    // Review Product

    'comment' => [
        'required_without' => 'Please provide a comment or rating.',
        'string' => 'Comment must be a string.',
        'max' => 'Comment must not exceed 1000 characters.',
    ],

    'rating' => [
        'required_without' => 'Please provide a comment or rating.',
        'integer' => 'Rating must be an integer.',
        'in' => 'Rating must be one of the following values: 1, 2, 3, 4, or 5.',
    ],


    // Place New Order Validation

    'address_id' => [
        'required' => 'Address ID is required.',
        'exists' => 'The selected address does not exist.',
        'integer' => 'Address ID must be an integer.',
    ],

    'shipping_id' => [
        'required' => 'Shipping method ID is required.',
        'exists' => 'The selected shipping method does not exist.',
        'integer' => 'Shipping method ID must be an integer.',
    ],

    'order_details' => [
        'required' => 'Order details are required.',
        'array' => 'Order details must be an array.',
        'min' => 'Order must contain at least one product.',
    ],

    'unit_price' => [
        'required' => 'Unit price is required.',
        'numeric' => 'Unit price must be a number.',
        'min' => 'Unit price must be greater than or equal to zero.',
    ],

    'total_price' => [
        'required' => 'Total price is required.',
        'numeric' => 'Total price must be a number.',
        'min' => 'Total price must be greater than or equal to zero.',
    ],


    // Client Side Messgaes

    'home_page' => 'All Site Details successfully retrived.',
    'get_site_theme' => 'Site theme retrieved successfully.',
    'get_site_media' => 'Site media retrieved successfully.',
    'get_products_list' => 'Products list retrieved successfully.',
    'get_product_details' => 'Product details retrieved successfully.',
    'get_shipping_methods_list' => 'Shipping methods list retrieved successfully.',
    'get_coupons_list' => 'Coupons list retrieved successfully.',
    'review_product' => 'Your review has been submitted successfully.',
    'get_user_wishlist' => 'User wishlist retrieved successfully.',
    'add_to_wishlist' => 'Product added to wishlist successfully.',
    'delete_wishlist' => 'Wishlist item deleted successfully.',
    'get_user_cart_list' => 'User cart list retrieved successfully.',
    'add_to_cart' => 'Product added to cart successfully.',
    'update_cart_details' => 'Cart details updated successfully.',
    'delete_cart_list' => 'Cart item deleted successfully.',
    'get_user_orders' => 'User orders retrieved successfully.',
    'add_new_order' => 'Order created successfully.',
    'cart_not_found' => 'Cart item not found.',



    'data_retrieved' => 'Data retrieved successfully.',

    'data_not_found' => 'Data not found.',

    'data_added' => 'Data added successfully.',

    'data_updated' => 'Data updated successfully.',

    'data_deleted' => 'Data deleted successfully.',



    'login_user_required' => 'Employee file number is required.',
    'login_user_integer' => 'Employee file number must be integer.',


    /*
    |--------------------------------------------------------------------------
    | Lookup Type Validation
    |--------------------------------------------------------------------------
    */

    'type_en_name' => [
        'required' => 'English lookup type name is required.',
        'string'   => 'English lookup type name must be a string.',
        'max'      => 'English lookup type name may not be greater than 100 characters.',
        'unique'   => 'English lookup type name already exists.',
    ],

    'type_ar_name' => [
        'required' => 'Arabic lookup type name is required.',
        'string'   => 'Arabic lookup type name must be a string.',
        'max'      => 'Arabic lookup type name may not be greater than 100 characters.',
        'unique'   => 'Arabic lookup type name already exists.',
    ],

    'type_description' => [
        'string' => 'Description must be a string.',
        'max'    => 'Description may not be greater than 255 characters.',
    ],

    'status' => [
        'integer' => 'Status must be an integer.',
        'in'      => 'Status must be either Active or Inactive.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lookup Value Validation
    |--------------------------------------------------------------------------
    */


    'type_id' => [
        'required' => 'Lookup type is required.',
        'integer'  => 'Lookup type must be a valid integer.',
        'exists'   => 'The selected lookup type does not exist.',
    ],

    'code' => [
        'required' => 'Code is required.',
        'string'   => 'Code must be a string.',
        'max'      => 'Code may not be greater than 50 characters.',
        'unique'   => 'Code already exists for the selected lookup type.',
    ],

    'meaning' => [
        'required' => 'Lookup meaning is required.',
        'string'   => 'Lookup meaning must be a string.',
        'max'      => 'Lookup meaning may not be greater than 255 characters.',
    ],

    'description' => [
        'string' => 'Description must be a string.',
        'max'    => 'Description may not be greater than 500 characters.',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Validation
    |--------------------------------------------------------------------------
    */

    'full_name' => [
        'required' => 'Full name is required.',
        'string'   => 'Full name must be a string.',
        'max'      => 'Full name may not be greater than 255 characters.',
    ],

    'user_name' => [
        'required' => 'Username is required.',
        'string'   => 'Username must be a string.',
        'max'      => 'Username may not be greater than 255 characters.',
        'unique'   => 'Username already exists.',
    ],

    'phone_number' => [
        'string' => 'Phone number must be a string.',
        'max'    => 'Phone number may not be greater than 20 characters.',
    ],

    'email_address' => [
        'required' => 'Email address is required.',
        'email'    => 'Please enter a valid email address.',
        'max'      => 'Email address may not be greater than 255 characters.',
        'unique'   => 'Email address already exists.',
    ],

    'password' => [
        'required' => 'Password is required.',
        'string'   => 'Password must be a string.',
        'min'      => 'Password must be at least 8 characters.',
        'max'      => 'Password may not be greater than 255 characters.',
    ],

    'user_type' => [
        'integer' => 'User type must be an integer.',
        'in'      => 'User type must be Customer, Admin, or Staff.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Address Validation
    |--------------------------------------------------------------------------
    */

    'user_id' => [
        'required' => 'User is required.',
        'integer'  => 'User ID must be an integer.',
        'exists'   => 'The selected user does not exist.',
    ],

    'label' => [
        'string' => 'Address label must be a string.',
        'max'    => 'Address label may not be greater than 50 characters.',
    ],

    'address_line' => [
        'required' => 'Address line is required.',
        'string'   => 'Address line must be a string.',
        'max'      => 'Address line may not be greater than 500 characters.',
    ],

    'city' => [
        'required' => 'City is required.',
        'string'   => 'City must be a string.',
        'max'      => 'City may not be greater than 100 characters.',
    ],

    'country' => [
        'required' => 'Country is required.',
        'string'   => 'Country must be a string.',
        'max'      => 'Country may not be greater than 100 characters.',
    ],

    'postal_code' => [
        'string' => 'Postal code must be a string.',
        'max'    => 'Postal code may not be greater than 20 characters.',
    ],

    'phone' => [
        'string' => 'Phone number must be a string.',
        'max'    => 'Phone number may not be greater than 20 characters.',
    ],

    'is_default' => [
        'integer' => 'Default status must be an integer.',
        'in'      => 'Default status must be either Yes or No.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Validation
    |--------------------------------------------------------------------------
    */

    'category_en_name' => [
        'required' => 'English category name is required.',
        'string'   => 'English category name must be a string.',
        'max'      => 'English category name may not be greater than 255 characters.',
    ],

    'category_ar_name' => [
        'required' => 'Arabic category name is required.',
        'string'   => 'Arabic category name must be a string.',
        'max'      => 'Arabic category name may not be greater than 255 characters.',
    ],

    'category_description' => [
        'string' => 'Category description must be a string.',
        'max'    => 'Category description may not be greater than 500 characters.',
    ],

    'category_image' => [
        'required' => 'Category image is required.',
        'file'   => 'Category image must be a file.',
        'mimes'      => 'Category image must be of type (jpg,jpeg,png,webp).',
        'max'      => 'Category image path may not be greater than 5120 MB.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Validation
    |--------------------------------------------------------------------------
    */


    'product_en_name' => [
        'required' => 'English product name is required.',
        'string'   => 'English product name must be a string.',
        'max'      => 'English product name may not be greater than 255 characters.',
    ],

    'product_ar_name' => [
        'required' => 'Arabic product name is required.',
        'string'   => 'Arabic product name must be a string.',
        'max'      => 'Arabic product name may not be greater than 255 characters.',
    ],

    'product_en_description' => [
        'string' => 'English product description must be a string.',
        'max'    => 'English product description may not be greater than 1000 characters.',
    ],

    'product_ar_description' => [
        'string' => 'Arabic product description must be a string.',
        'max'    => 'Arabic product description may not be greater than 1000 characters.',
    ],

    'product_material' => [
        'integer' => 'Product material must be an integer.',
        'exists'  => 'The selected product material does not exist.',
    ],

    'product_stone' => [
        'integer' => 'Product stone must be an integer.',
        'exists'  => 'The selected product stone does not exist.',
    ],

    'product_reels' => [
        'file' => 'Product reels must be a file.',
        'max'    => 'Product reels may not be greater than 500 characters.',
        'mimes'    => 'Product reels must be of type mp4.',
    ],

    'product_price' => [
        'numeric' => 'Product price must be a number.',
        'min'     => 'Product price must be greater than or equal to 0.',
    ],

    'product_status' => [
        'integer' => 'Product status must be an integer.',
        'in'      => 'Product status must be either Active or Inactive.',
    ],

    'category_id' => [
        'required' => 'Category is required.',
        'integer'  => 'Category ID must be an integer.',
        'exists'   => 'The selected category does not exist.',
    ],


    'images' => [
        'array' => 'Images must be an array.',
    ],

    'images.*.image' => [
        'required' => 'Product image is required.',
        'file'   => 'Product image must be a file.',
        'mimes'      => 'Product image must be of type (jpg,jpeg,png,webp).',
        'max'      => 'Product image path may not be greater than 5120 MB.',
    ],

    'images.*.is_primary' => [
        'integer' => 'Primary image status must be an integer.',
        'in'      => 'Primary image status must be either Yes or No.',
    ],

    'images.*.sort_order' => [
        'integer' => 'Sort order must be an integer.',
        'min'     => 'Sort order must be greater than or equal to 0.',
    ],


    'variants' => [
        'array' => 'Product variants must be an array.',
    ],

    'variants.*.color_id' => [
        'integer' => 'Color must be an integer.',
        'exists'  => 'The selected color does not exist.',
    ],

    'variants.*.size_id' => [
        'integer' => 'Size must be an integer.',
        'exists'  => 'The selected size does not exist.',
    ],

    'variants.*.sku' => [
        'string'   => 'SKU must be a string.',
        'max'      => 'SKU may not be greater than 100 characters.',
        'distinct' => 'SKU values must be unique.',
        'unique'   => 'SKU already exists.',
    ],

    'variants.*.stock' => [
        'required' => 'Stock quantity is required.',
        'integer'  => 'Stock must be an integer.',
        'min'      => 'Stock must be greater than or equal to 0.',
    ],

    'variants.*.price' => [
        'numeric' => 'Variant price must be a number.',
        'min'     => 'Variant price must be greater than or equal to 0.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Method Validation
    |--------------------------------------------------------------------------
    */


    'method_en_name' => [
        'required' => 'English shipping method name is required.',
        'string'   => 'English shipping method name must be a string.',
        'max'      => 'English shipping method name may not be greater than 100 characters.',
    ],

    'method_ar_name' => [
        'required' => 'Arabic shipping method name is required.',
        'string'   => 'Arabic shipping method name must be a string.',
        'max'      => 'Arabic shipping method name may not be greater than 100 characters.',
    ],

    'price' => [
        'numeric' => 'Shipping price must be a number.',
        'min'     => 'Shipping price must be greater than or equal to 0.',
    ],

    'estimated_days' => [
        'integer' => 'Estimated days must be an integer.',
        'min'     => 'Estimated days must be greater than or equal to 0.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Coupon Validation
    |--------------------------------------------------------------------------
    */

    'discount_amount' => [
        'required' => 'Discount amount is required.',
        'numeric'  => 'Discount amount must be a number.',
        'gt'       => 'Discount amount must be greater than 0.',
    ],

    'minimum_order_amount' => [
        'numeric' => 'Minimum order amount must be a number.',
        'min'     => 'Minimum order amount must be greater than or equal to 0.',
    ],

    'max_usage' => [
        'integer' => 'Maximum usage must be an integer.',
        'min'     => 'Maximum usage must be greater than or equal to 0.',
    ],

    'used_count' => [
        'integer' => 'Used count must be an integer.',
        'min'     => 'Used count must be greater than or equal to 0.',
    ],

    'expires_at' => [
        'date' => 'Expiration date must be a valid date.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Site Theme Validation
    |--------------------------------------------------------------------------
    */



    'theme_name' => [
        'required' => 'Theme name is required.',
        'string'   => 'Theme name must be a string.',
        'max'      => 'Theme name may not be greater than 100 characters.',
    ],

    'color_scheme' => [
        'string' => 'Color scheme must be a string.',
        'max'    => 'Color scheme may not be greater than 255 characters.',
    ],

    'font_style' => [
        'string' => 'Font style must be a string.',
        'max'    => 'Font style may not be greater than 100 characters.',
    ],

    'background_image' => [
        'string' => 'Background image path must be a string.',
        'max'    => 'Background image path may not be greater than 500 characters.',
    ],

    'borders' => [
        'string' => 'Borders value must be a string.',
        'max'    => 'Borders value may not be greater than 100 characters.',
    ],



    /*
    |--------------------------------------------------------------------------
    | Coupon Validation
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Coupon Validation
    |--------------------------------------------------------------------------
    */



    /*
    |--------------------------------------------------------------------------
    | Coupon Validation
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Coupon Validation
    |--------------------------------------------------------------------------
    */




];
