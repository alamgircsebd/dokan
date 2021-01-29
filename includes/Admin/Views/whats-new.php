<?php
$changelog = array(
    array(
        'version'  => 'Version 3.2.0',
        'released' => '2021-01-29',
        'changes'  => array(
            array(
                'title'       => 'Added WhatsApp Provider [Livechat]',
                'type'        => 'Improvement',
                'description' => 'Added WhatsApp provider for livechat [Livechat]',
            ),
            array(
                'title'       => 'Added Tawk.to Provider [Livechat] ',
                'type'        => 'Improvement',
                'description' => 'Added tawk.to provider for livechat [Livechat] ',
            ),
            array(
                'title'       => 'Added New Settings Where Admin Can Set Whether to Display the Map [Geolocation]',
                'type'        => 'Improvement',
                'description' => 'Added new settings where admin can set whether to display the map in shop or store listing page or both page.',
            ),
            array(
                'title'       => 'Added Store Support for Single Product [Store Support]',
                'type'        => 'Improvement',
                'description' => 'Added Store support form for single product page.',
            ),
            array(
                'title'       => 'Added Separate Email Subject and Body for Subscription Cancellation [Vendor Subscription]',
                'type'        => 'Improvement',
                'description' => 'Added separate email subject and body for subscription cancellation and alert emails.',
            ),
            array(
                'title'       => 'Added Dokan Upgrader to Move Existing Vendor Subscription [Vendor Subscription]',
                'type'        => 'Improvement',
                'description' => 'Added Dokan upgrader to move existing vendor subscription data to new keys.',
            ),
            array(
                'title'       => 'Update Billing Cycle Stops Fields [Vendor Subscription]',
                'type'        => 'Improvement',
                'description' => 'Update Billing Cycle Stops fields if Billing Cycle Type changes.',
            ),
            array(
                'title'       => 'Changed Product Pack Start Date and End Date Formate [Vendor Subscription]',
                'type'        => 'Improvement',
                'description' => 'Changed product_pack_startdate and product_pack_enddate value from date() to current_datetime(), this will fix timezone mismatch.',
            ),
            array(
                'title'       => 'Changed Some Meta Key in Subscription Data [Vendor Subscription]',
                'type'        => 'Improvement',
                'description' => 'Changed _subscription_period_interval, _subscription_period, _subscription_length into _dokan_subscription_period_interval, _dokan_subscription_period, _dokan_subscription_length. This was causing conflict with WooCommerce Subscription.',
            ),
            array(
                'title'       => 'Disable Email Verification If Subscription Module is Enabled [Vendor Subscription]',
                'type'        => 'Improvement',
                'description' => 'Disable email verification if subscription module is enabled in the registration form.',
            ),
            array(
                'title'       => 'All Metadata are Not Exporting [Import Export]',
                'type'        => 'Fix',
                'description' => 'All metadata are not exporting issue fixed.',
            ),
            array(
                'title'       => 'Dokan Auction Product Addons Are Not Saving [Auction]',
                'type'        => 'Fix',
                'description' => 'Dokan auction product addons are not saving issue fixed.',
            ),
            array(
                'title'       => 'Fixed Seller Can Clone Product Without a Subscription [SPMV]',
                'type'        => 'Fix',
                'description' => 'Fixed seller can clone product using sell this item without a subscription.',
            ),
            array(
                'title'       => 'Product Duplicate Button Based on Active Subscription [Vendor Subscription]',
                'type'        => 'Fix',
                'description' => 'Product duplicate button based on active subscription issue fixed.',
            ),
            array(
                'title'       => 'Booking Buffer Period Duration Label Translatable [Booking]',
                'type'        => 'Fix',
                'description' => 'Booking buffer period duration unit label is not translatable now fixed.',
            ),
            array(
                'title'       => 'Email Subscription Ending Soon Email is Never Sent [Vendor Subscription]',
                'type'        => 'Fix',
                'description' => 'Email Subscription Ending Soon email is never sent issue fixed.',
            ),
            array(
                'title'       => 'Recurring Payment is Not Canceling if Admin Assigns Non-recurring Subscription [Vendor Subscription]',
                'type'        => 'Fix',
                'description' => 'Recurring payment is not canceling if admin assigns non-recurring subscription from the admin dashboard.',
            ),
            array(
                'title'       => 'Subscription Purchased by PayPal was Canceled Immediately [Vendor Subscription]',
                'type'        => 'Fix',
                'description' => 'Subscription purchased by PayPal was canceled immediately if subscription pack is not recurring.',
            ),
            array(
                'title'       => 'Added Additional Fee if Commission Type is Combined for Non-dokan Payment [Vendor Subscription]',
                'type'        => 'Fix',
                'description' => 'Added additional fee if commission type is combined for non-dokan payment gateways issue fixed.',
            ),
            array(
                'title'       => 'Multiple Stripe Webhook Was Creating, Moved Webhook [Stripe]',
                'type'        => 'Fix',
                'description' => 'Multiple stripe webhook was creating, moved webhook creation code under activation/deactivation hooks, deactivate and active module to apply these changes.',
            ),
            array(
                'title'       => 'Fixed Fatal Error if the Source String is Empty if Users Try to Change Payment [Stripe]',
                'type'        => 'Fix',
                'description' => 'Fixed fatal error if the source string is empty if users try to change payment method from my account page.',
            ),
            array(
                'title'       => 'Fixed Fatal Error if the Order Value is Less Than or Equal to Zero for Stripe 3DS Mode [Stripe]',
                'type'        => 'Fix',
                'description' => 'Fixed fatal error if the order value is less than or equal to zero for Stripe 3DS mode, this was causing the whole payment to fail.',
            ),
            array(
                'title'       => 'Relist Feature is Unavailable on the Vendor Dashboard [Auction]',
                'type'        => 'Fix',
                'description' => 'Relist feature is unavailable on the vendor dashboard issue fixed.',
            ),
            array(
                'title'       => 'Vendors Can not Add & Save New Tags on Auction Type Products [Auction]',
                'type'        => 'Fix',
                'description' => 'Vendors can not add & save new tags on Auction type products issue fixed.',
            ),
            array(
                'title'       => 'Fixed Elementor Module Causing Issue with Support Ticket Mail [Elementor]',
                'type'        => 'Fix',
                'description' => 'Fixed Elementor module causing issue with support ticket mail issue fixed.',
            ),
            array(
                'title'       => 'Fixed Mapbox Issue with RTL Supported Language [Geolocation]',
                'type'        => 'Fix',
                'description' => 'Fixed Mapbox issue with RTL supported language.',
            ),
            array(
                'title'       => 'Fixed Geolocation Position Settings Left and Right [Geolocation]',
                'type'        => 'Fix',
                'description' => 'Fixed Geolocation position settings left and right area working proper.',
            ),
            array(
                'title'       => 'Geolocation Map Autozoom When Getting Long Distance [Geolocation]',
                'type'        => 'Fix',
                'description' => 'Geolocation map autozoom when getting long distance between multiples stores/products locations.',
            ),
            array(
                'title'       => 'Hide Export Button When no Product Found for That Author [Import Export]',
                'type'        => 'Fix',
                'description' => 'Hide export button when no product found for that author.',
            ),
            array(
                'title'       => 'Vendor Analytics Deprecated Warning [Vendor Analytics]',
                'type'        => 'Fix',
                'description' => 'Vendor analytics deprecated warning fixed now.',
            ),
            array(
                'title'       => 'Delete Recurring Subscription Key After a Subscription Has Been Deleted [Subscription]',
                'type'        => 'Fix',
                'description' => 'Delete recurring subscription key after a subscription has been deleted.',
            ),
            array(
                'title'       => 'Fixed Wrong Order Reference URL in Support Tickets [Store Support]',
                'type'        => 'Fix',
                'description' => 'Fixed wrong order reference URL in support tickets in WooCommerce my account and Dokan vendor dashboard area.',
            ),
            array(
                'title'       => 'Product Add pop-up Validation Error Message Style',
                'type'        => 'Fix',
                'description' => 'Product add pop-up validation error message style issue fixed.',
            ),
            array(
                'title'       => 'Fixed dokan_admin JS var Undefined Issue',
                'type'        => 'Fix',
                'description' => 'Fixed dokan_admin js var undefined issue at add/edit product page.',
            ),
            array(
                'title'       => 'Fixed Undefined ID Notice While Creating Products',
                'type'        => 'Fix',
                'description' => 'Fixed undefined ID notice while creating products from vendor dashboard.',
            ),
            array(
                'title'       => 'Downloadable Options Panel Not Showing',
                'type'        => 'Fix',
                'description' => 'Downloadable options panel not showing.',
            ),
            array(
                'title'       => 'Fixed Vendor Setting to Discount on Order Calculation Error',
                'type'        => 'Fix',
                'description' => 'Fixed Vendor Setting to discount on order calculation error fixed now.',
            ),
            array(
                'title'       => 'Fixed WPML Conflict with Menu and Widget Page',
                'type'        => 'Fix',
                'description' => 'Fixed WPML conflict with menu and widget page when users try to switch between language.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.4',
        'released' => '2021-01-11',
        'changes'  => array(
            array(
                'title'       => 'Added Disconnect Button and Some Issues Fixed [Vendor Analytics]',
                'type'        => 'Improvement',
                'description' => 'Added disconnect button on dokan admin setting page, also fixed some issues.',
            ),
            array(
                'title'       => 'Product Add-on Module Template Override [Product Addon]',
                'type'        => 'Improvement',
                'description' => 'Product add-on module template override does not work with theme folder issue fixed.',
            ),
            array(
                'title'       => 'Changed Social Login Sign in URL Change [Vendor Social Login]',
                'type'        => 'Improvement',
                'description' => 'Changed social login sign in URL from dokan_reg to vendor_social_reg  on query param.',
            ),
            array(
                'title'       => 'Import Option Allows Vendors to Create Categories',
                'type'        => 'Fix',
                'description' => 'Import option allows vendors to create categories issue fixed, now vendor not able to create new category when import csv.',
            ),
            array(
                'title'       => 'If Admin Commission for Flat Type Commission is Set to Zero Was Showing Not Set [Vendor Commission]',
                'type'        => 'Fix',
                'description' => 'If admin commission for flat type commission is set to zero it was showing not set.',
            ),
            array(
                'title'       => 'Text-domain Missing on Confirmation Alert',
                'type'        => 'Fix',
                'description' => 'Text-domain missing on confirmation alert. Now it fixed all alert confirmation on vendor dashboard.',
            ),
            array(
                'title'       => 'Default Attribute Was Not Displaying [Vendor Product Update]',
                'type'        => 'Fix',
                'description' => 'Default attribute was not displaying when variation product edit from vendor dashboard issue fixed.',
            ),
            array(
                'title'       => 'Vendor Details Admin Commission Label Changed',
                'type'        => 'Fix',
                'description' => 'Vendor details admin commission label changed to commission rate on the admin area dokan vendor details page.',
            ),
            array(
                'title'       => 'Fixed Vendor Staff Was Not Receiving New Order Email [Vendor Staff]',
                'type'        => 'Fix',
                'description' => 'Fixed vendor staff was not receiving new order email issue fixed now.',
            ),
            array(
                'title'       => 'Fixed Variations Was Not Saving Correctly [Vendor Product]',
                'type'        => 'Fix',
                'description' => 'Fixed Variations was not saving correctly from vendor dashboard when try to use multiples attributes.',
            ),
            array(
                'title'       => 'Fixed Store Support Form Showing Wrong With Elementor [Elementor]',
                'type'        => 'Fix',
                'description' => 'Fixed store support form showing wrong with Elementor if still have logged out users.',
            ),
            array(
                'title'       => 'Replaced WP SEO Deprecated Functions [Product SEO]',
                'type'        => 'Fix',
                'description' => 'Replaced WP SEO deprecated functions, now product seo capable with latest wp seo plugin.',
            ),
            array(
                'title'       => 'Fixed Product Location Mismatch [Geolocation]',
                'type'        => 'Fix',
                'description' => 'Fixed product location mismatch if created from admin and try to reassign a vendor on a product.',
            ),
            array(
                'title'       => 'Auction Product SKU is Not Updating [Auction]',
                'type'        => 'Fix',
                'description' => 'Auction product SKU is not updating or saving now fixed.',
            ),
            array(
                'title'       => 'Single Product Multiple Vendor Redirection [Auction]',
                'type'        => 'Fix',
                'description' => 'Single Product Multiple Vendor redirection for auction and booking type product.',
            ),
            array(
                'title'       => 'Updated Stripe Codebase and Fixed Some Issues [Dokan Stripe]',
                'type'        => 'Fix',
                'description' => 'Updated stripe codebase and fixed some issues with Stripe modules.',
            ),
            array(
                'title'       => 'Responsive Dashboard Product and Order Table',
                'type'        => 'Fix',
                'description' => 'Responsive dashboard product and order table now fixed.',
            ),
            array(
                'title'       => 'Removed Addon Validation for Dokan Subscription [Dokan Subscription]',
                'type'        => 'Fix',
                'description' => 'Removed addon validation for Dokan Subscription product.',
            ),
            array(
                'title'       => 'Vendor Updates Other Vendor Product',
                'type'        => 'Fix',
                'description' => 'Vendor updates other vendor product if SKU/ID is same, instead of creating a new product for requesting vendor.',
            ),
            array(
                'title'       => 'Make Product Status Draft After a Vendor Cancels Their Subscriptions [Dokan Subscriptions]',
                'type'        => 'Fix',
                'description' => 'Make product status draft after a vendor/admin immediately cancels their subscriptions.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.3',
        'released' => '2020-12-17',
        'changes'  => array(
            array(
                'title'       => 'Added Tax/Discount for Vendor Subscriptions [WireCard]',
                'type'        => 'Improvement',
                'description' => 'Added tax/discount for Vendor Subscriptions, previously only actual product price was sent to API.',
            ),
            array(
                'title'       => 'Added a New Exception if Vendor Account [WireCard]',
                'type'        => 'Improvement',
                'description' => 'Added a new exception if vendor account is not linked with wire card, now the user will get proper error messages instead of Something went wrong.',
            ),
            array(
                'title'       => 'Refund and Announcement Page Loading Problem [Dokan Admin]',
                'type'        => 'Fix',
                'description' => 'Refund and announcement listing loading problem and text-domain issue fixed.',
            ),
            array(
                'title'       => 'Booking Addon Options are Missing [Dokan Booking]',
                'type'        => 'Fix',
                'description' => 'Booking addon options are missing on the Booking type product edit panel.',
            ),
            array(
                'title'       => 'Variable Product Image Upload Issue with Yoast SEO [Vendor Product SEO]',
                'type'        => 'Fix',
                'description' => 'Variable product image upload when yoast seo plugin is active.',
            ),
            array(
                'title'       => 'Added Tax Fields for Vendor Subscription [Vendor Subscription Product]',
                'type'        => 'Fix',
                'description' => 'Added tax fields for vendor subscription type product.',
            ),
            array(
                'title'       => 'Booking Simple Product to Virtual Product [Dokan Booking]',
                'type'        => 'Fix',
                'description' => 'Booking simple product changes to virtual product when create a booking product from vendor area.',
            ),
            array(
                'title'       => 'Stripe Recurring Issue With 3ds [Dokan Stripe]',
                'type'        => 'Fix',
                'description' => 'Fixed Dokan Stripe 3ds recurring issue with vendor subscription products.',
            ),
            array(
                'title'       => 'Dokan Order Discount Mismatch When Recalculate',
                'type'        => 'Fix',
                'description' => 'Dokan order discount mismatch when recalculate from admin panel order details page.',
            ),
            array(
                'title'       => 'Fixed Cart Coupon Option Disabled Multi Vendors',
                'type'        => 'Fix',
                'description' => 'Fixed cart coupon option disabled for multi vendors, it will be work only when single seller mode enabled form dokan settings.',
            ),
            array(
                'title'       => 'Added Some New Exceptions to Display Formatted [WireCard]',
                'type'        => 'Fix',
                'description' => 'Added some new exceptions to display formatted errors to users.',
            ),
            array(
                'title'       => 'Fixed Product Pack End Date for Vendor Subscription [WireCard]',
                'type'        => 'Fix',
                'description' => 'Fixed product pack end date for vendor subscription, previously this was causing subscription to get canceled automatically before subscriptions actual end date.',
            ),
            array(
                'title'       => 'Fixed Decimal Issues on Product Price [WireCard]',
                'type'        => 'Fix',
                'description' => 'Fixed decimal issues on product price, this was causing API error due to mismatch order total.',
            ),
            array(
                'title'       => 'Removed rmccue/requests Library From Vendor Folder [WireCard]',
                'type'        => 'Fix',
                'description' => 'Removed rmccue/requests library from vendor folder, WordPress already has this library preinstalled. This was causing a fatal error on some installations.',
            ),
            array(
                'title'       => 'Fixed Limit Your Zone Selected by Default [Dokan Vendor Shipping]',
                'type'        => 'Fix',
                'description' => 'Limit your zone selected by default when zone created with a country.',
            ),
            array(
                'title'       => 'Vendor Verification Upload Documents Folder Disallow',
                'type'        => 'Fix',
                'description' => 'Disallow direct access vendor verification uploaded documents folder.',
            ),
            array(
                'title'       => 'Fixed Dokan Stripe Resource Missing API',
                'type'        => 'Fix',
                'description' => 'Fixed Dokan Stripe resource missing api error for empty source provided via api call.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.2',
        'released' => '2020-12-01',
        'changes'  => array(
            array(
                'title'       => 'Vendor Gets Error With PayPal',
                'type'        => 'Fix',
                'description' => 'Vendor gets error while purchasing products if they purchased a vendor subscription product with PayPal in checkout page.',
            ),
            array(
                'title'       => 'Multi Vendor Product Showing Others Vendor',
                'type'        => 'Fix',
                'description' => 'Single product multi vendor products showing others vendor area issue when SPMV product duplicated.',
            ),
            array(
                'title'       => 'Admin Commission Set 0 by Default',
                'type'        => 'Fix',
                'description' => 'Admin commission set 0 by default when create/update vendor form admin area.',
            ),
            array(
                'title'       => 'Enabling Vacation Mode is Hiding Products',
                'type'        => 'Fix',
                'description' => 'Enabling vacation mode is hiding products from vendor dashboard, vendor is not able to see the products.',
            ),
            array(
                'title'       => 'Vendor Staff Is Not Able To Manage Product',
                'type'        => 'Fix',
                'description' => 'Vendor staff is not able to add/edit any product on vendor dashboard, also fixed capabilities issue.',
            ),
            array(
                'title'       => 'Vendor Shipping Settings Page Console Error',
                'type'        => 'Fix',
                'description' => 'Vendor shipping settings page showing js error issue when try to add/update any shipping zone.',
            ),
            array(
                'title'       => 'Update Vendor Review REST API',
                'type'        => 'Fix',
                'description' => 'Update vendor review REST API and fixed some errors.',
            ),
            array(
                'title'       => 'SMS verification Error Message Translation',
                'type'        => 'Fix',
                'description' => 'SMS verification error message translation was not available.',
            ),
            array(
                'title'       => 'SMS Verification Error Handling',
                'type'        => 'Fix',
                'description' => 'SMS verification error handling for vendors.',
            ),
            array(
                'title'       => 'Booking Product Virtual Option Not Saving',
                'type'        => 'Fix',
                'description' => 'Booking product virtual option not saving while 1st time create form vendor dashboard.',
            ),
            array(
                'title'       => 'Coupon Minimum Amount Not Working',
                'type'        => 'Fix',
                'description' => 'Coupon minimum amount not working with variation products issue fixed.',
            ),
            array(
                'title'       => 'Vendor Product Addon Appears on Other Vendors',
                'type'        => 'Fix',
                'description' => 'Vendor product addon appears in every product in marketplace when that vendor is logged in.',
            ),
            array(
                'title'       => 'Product Wise Commission Issue In Subscription Product',
                'type'        => 'Fix',
                'description' => 'Product wise Commission is not working in subscription product on admin area product edit page.',
            ),
            array(
                'title'       => 'Report CSV Header Mismatch',
                'type'        => 'Fix',
                'description' => 'Report csv header mismatch issue fixed.',
            ),
            array(
                'title'       => 'Stripe Dashboard Tax Issue',
                'type'        => 'Fix',
                'description' => 'Stripe Dashboard does not show the price including the tax for vendors.',
            ),
            array(
                'title'       => 'SKU Data Not importing with CSV',
                'type'        => 'Fix',
                'description' => 'SKU data not importing when CSV import on vendor dashboard.',
            ),
            array(
                'title'       => 'Booking Single Day Data Issue',
                'type'        => 'Fix',
                'description' => 'Booking single day no data showing, responsiveness issue fixes form vendor dashboard booking details page.',
            ),
            array(
                'title'       => 'Product Seo Default Meta Field Issue',
                'type'        => 'Fix',
                'description' => 'Product seo default meta description removed from vendor dashboard product edit page.',
            ),
            array(
                'title'       => 'Variable product gets extra fields of variable subscription product',
                'type'        => 'Fix',
                'description' => 'When a vendor wants to create a variable product, extra field added from the vendor subscription product.',
            ),
            array(
                'title'       => 'Check End Date Before Cancelling Vendor Subscriptions',
                'type'        => 'Fix',
                'description' => 'Check subscription product pack end date matched with stored end date before cancelling vendor subscriptions. If both value does not match, update end date value.',
            ),
            array(
                'title'       => 'Downloads files showing multiple entries when have suborder',
                'type'        => 'Fix',
                'description' => 'Downloads files showing multiple entries when have suborder.',
            ),
            array(
                'title'       => 'Gateway fee paid by admin if empty',
                'type'        => 'Fix',
                'description' => 'If the processing fee is not 0 and if the dokan_gateway_fee_paid_by meta is blank then the processing fee is paid by the admin.',
            ),
            array(
                'title'       => 'Booking by day view which is missing in Booking calendar',
                'type'        => 'Fix',
                'description' => 'Bookable Product: Booking by day view which is missing in Booking calender.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1.1',
        'released' => '2020-11-14',
        'changes'  => array(
            array(
                'title'       => 'Refactored Stripe Connect Module',
                'type'        => 'Fix',
                'description' => 'Refactored and fixed stripe connect module. Here fixed multiples dokan and vendor product subscription issues.',
            ),
            array(
                'title'       => 'Vendor Subscription Product Cancel Not Showing on Vendor Dashboard',
                'type'        => 'Fix',
                'description' => 'When a customer canceled their subscription then last status not showing vendor dashboard.',
            ),
            array(
                'title'       => 'Wholesale Product Checkbox Not Working',
                'type'        => 'Fix',
                'description' => 'Wholesale product checkbox not working when product status pending in vendor product edit page',
            ),
            array(
                'title'       => 'Product Wise Commission Not Working Comma Decimal',
                'type'        => 'Fix',
                'description' => 'Product wise commission not working when use comma decimal separator issue fixed',
            ),
            array(
                'title'       => 'Dokan Modules Section Active/Inactive Tab Issue',
                'type'        => 'Fix',
                'description' => 'Dokan modules section active/inactive tab section not work correctly',
            ),
            array(
                'title'       => 'Product Addon Select Field Options Issue with Price Field Blank',
                'type'        => 'Fix',
                'description' => 'When a vendor try to add a product addon select field with price field blank then the option not saving',
            ),
            array(
                'title'       => 'Required Minimum PHP Version Set to 7.0.0',
                'type'        => 'Fix',
                'description' => 'PHP 5.6 Compatibility, update required minimum php version is set to 7.0.0 on Dokan',
            ),
            array(
                'title'       => 'Vendor Not Able to Duplicate Product',
                'type'        => 'Fix',
                'description' => 'Duplicate product not working when try any product duplicate from vendor dashboard',
            ),
            array(
                'title'       => 'Fixed translation Issue for Dokan pro',
                'type'        => 'Fix',
                'description' => 'Fixed multiple translation issues for Dokan amdin settings pages',
            ),
            array(
                'title'       => 'Refactored Dokan Admin Modules Page',
                'type'        => 'Fix',
                'description' => 'Modules url changed on title and image in dokan admin modules page',
            ),
            array(
                'title'       => 'Dokan Booking Calendar Issue on Single day',
                'type'        => 'Fix',
                'description' => 'Dokan booking calendar only shows one booking on a single day on vendor dashboard booking details page',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.1',
        'released' => '2020-10-20',
        'changes'  => array(
            array(
                'title'       => 'Vendor Analytics',
                'type'        => 'New',
                'description' => 'Get more insights to vendor data and track store performances. Vendor will now get google analytics for his store and product pages.',
            ),
            array(
                'title'       => 'Live Search',
                'type'        => 'New',
                'description' => 'Refine your search results just like Google. Autocomplete will give you a better search experience than ever before.',
            ),
            array(
                'title'       => 'Refactored Stripe Connect Module',
                'type'        => 'Fix',
                'description' => 'Refactored and fixed stripe connect module. Updated stripe SDK version and stripe connect type.',
            ),
            array(
                'title'       => 'Gateway Fee on Admin Report Logs',
                'type'        => 'Fix',
                'description' => 'Added gateway fee payee indicator in admin report logs. Now the admin will have a proper view of the gateway fee amount and who is paying that.',
            ),
            array(
                'title'       => 'Booking Confirmation from the Booking List',
                'type'        => 'Fix',
                'description' => 'When the vendor tries to confirm booking from the booking list, it was not working properly and was not showing a thank you message.',
            ),
            array(
                'title'       => 'Activate Modules During Plugin Activation',
                'type'        => 'Fix',
                'description' => 'The modules will now be inactive by default after plugin installation, enable the modules you need.',
            ),
            array(
                'title'       => 'Product Grouped Type',
                'type'        => 'Fix',
                'description' => 'We have fixed the issue, product type was not changing to grouped product when trying to change.',
            ),
            array(
                'title'       => 'Branding Issue on Seller Search',
                'type'        => 'Fix',
                'description' => 'You can now have a proper search result of vendors by filtering them with brand on store listing page.',
            ),
            array(
                'title'       => 'Vendor Earning in Order Details',
                'type'        => 'Fix',
                'description' => 'Now your vendors will see accurate vendor earnings in order details after the refund.',
            ),
            array(
                'title'       => 'Vendor Export Report',
                'type'        => 'Fix',
                'description' => 'We have fixed the statement of your vendor\'s balance when they export the statement from their dashboard.',
            ),
            array(
                'title'       => 'Removed External Product Type',
                'type'        => 'Fix',
                'description' => 'Removed external product type from subscription allowed product types for vendor subscription product.',
            ),
            array(
                'title'       => 'Subscription Product Price Not Saving',
                'type'        => 'Fix',
                'description' => 'You can now save the subscription product price when WC auction plugin is active.',
            ),
            array(
                'title'       => 'Featured Seller limit',
                'type'        => 'Fix',
                'description' => 'On your store listing page, the featured sellers number was showing more than the limit. We have fixed that.',
            ),
            array(
                'title'       => 'Product Tags add on Quick Edit Area',
                'type'        => 'Fix',
                'description' => 'Product tags search experience improvement and fixed the issue of not working properly on quick edit area.',
            ),
            array(
                'title'       => 'Text Domain in JS end',
                'type'        => 'Fix',
                'description' => 'Text domain issue when report abuse delete in js end and translate not working properly.',
            ),
            array(
                'title'       => 'JS Console Error on Report Abuse',
                'type'        => 'Fix',
                'description' => 'JS console error fixed on report abuse module from admin area edit product page',
            ),
            array(
                'title'       => 'Subscription Plan Page Design',
                'type'        => 'Fix',
                'description' => 'Subscription plan page design will work properly now when different languages are used.',
            ),
            array(
                'title'       => 'Vendor Product Import',
                'type'        => 'Fix',
                'description' => 'When a vendor imports a product from the dashboard then the default advanced option shows automatically, it\'s not an expected behavior. So we fixed that UI.',
            ),
            array(
                'title'       => 'Dokan Pro Email Template',
                'type'        => 'Fix',
                'description' => 'Dokan Pro core email template locations updated, so now you can override the template file from theme.',
            ),
            array(
                'title'       => 'Store Default Geolocation',
                'type'        => 'Fix',
                'description' => 'When you try to create a new product from the vendor dashboard then store default geolocation was not set in the product.',
            ),
            array(
                'title'       => 'Coupon Product and Exclude Product Field Move',
                'type'        => 'Fix',
                'description' => 'Coupon product and exclude product field move to search select with variations.',
            ),
            array(
                'title'       => 'Product Variation Toggle',
                'type'        => 'Fix',
                'description' => 'Product variation toggle issue, variation downloadable file delete issue.',
            ),
            array(
                'title'       => 'Vendor Can Modify Other Product',
                'type'        => 'Fix',
                'description' => 'There was a permission issue with vendor product edit. ‘Vendors can modify other vendor products’ are now restricted and not possible from this version.',
            ),
            array(
                'title'       => 'Multi Vendor Duplicate SKU',
                'type'        => 'Fix',
                'description' => 'When someone was trying to create a product from another product, then the SKU will not conflict with the existing one.',
            ),
            array(
                'title'       => 'Vendor Confirmation Email',
                'type'        => 'Fix',
                'description' => 'When some purchased a booking and the vendor did not get a booking confirmation email. That issue is fixed now.',
            ),
            array(
                'title'       => 'Quick Update Products',
                'type'        => 'Fix',
                'description' => 'Can not quick update products when product limit reached form vendor dashboard.',
            ),
            array(
                'title'       => 'CSV Import Feature Column',
                'type'        => 'Fix',
                'description' => 'When vendors import CSV from vendor dashboard and feature column make false, here checking CSV import vendor or admin.',
            ),
            array(
                'title'       => 'Export Wholesale Column Missing',
                'type'        => 'Fix',
                'description' => 'The vendor will now see the export wholesale column when you export product from vendor dashboard.',
            ),
            array(
                'title'       => 'Product Add-on Type File not Showed on Order',
                'type'        => 'Fix',
                'description' => 'Product add-on type File upload does not show the file on vendor order.',
            ),
            array(
                'title'       => 'Auction Start End Field',
                'type'        => 'Fix',
                'description' => 'Auction start, end field disable from keyboard.',
            ),
            array(
                'title'       => 'Announcements Week',
                'type'        => 'Fix',
                'description' => 'You will get all the announcements in time regardless of the timezone.',
            ),
            array(
                'title'       => 'Product Discount Scheduled',
                'type'        => 'Fix',
                'description' => 'Your vendor had problems setting schedule discounts for their products in the previous version. Dokan new version has the fix for this issue. Your vendor  can now schedule the discounts to their products.',
            ),
            array(
                'title'       => 'Import Restriction with Subscription ',
                'type'        => 'Fix',
                'description' => 'When someone imports product with category name by using the import tool, now validation for subscription category restricted if found will be applied.',
            ),
            array(
                'title'       => 'Wholesale Customer Registration Email',
                'type'        => 'Fix',
                'description' => 'Wholesale customer registration email to the admin did not contain proper information. This version has the proper template and data.',
            ),
            array(
                'title'       => 'Report Select Date not Working',
                'type'        => 'Fix',
                'description' => 'Report custom date not working for daily sales & statements are fixed now. You can now use a custom date as you want.',
            ),
            array(
                'title'       => 'New Refund Request Email',
                'type'        => 'Fix',
                'description' => 'You can now easily send a refund request email and it will reach the admin.',
            ),
            array(
                'title'       => 'WooCommerce Deprecated Functions',
                'type'        => 'Fix',
                'description' => 'Dokan has updated the list of WooCommerce deprecated functions. Outdated or previous versions templates and functions are not used without proper documentation from this version.',
            ),
            array(
                'title'       => 'Refund Issue with Decimal Number',
                'type'        => 'Fix',
                'description' => 'When the vendor sends a refund request from the order details page then the total and refund amount were not compared correctly.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.8',
        'released' => '2020-09-04',
        'changes'  => array(
            array(
                'title'       => 'Shipping data updater',
                'type'        => 'Fix',
                'description' => 'Shipping data updater is not showing some situations',
            ),
            array(
                'title'       => 'Product type allowed in Vendor subsction product',
                'type'        => 'Fix',
                'description' => 'Default subscription type product is not showing in vendor subscription type product module',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.7',
        'released' => '2020-09-01',
        'changes'  => array(
            array(
                'title'       => 'Refactor Product SEO',
                'type'        => 'Fix',
                'description' => 'Vendor product SEO refactor codes where improve performance',
            ),
            array(
                'title'       => 'Shipping Continent Issue (Shipping)',
                'type'        => 'Fix',
                'description' => 'When try to add shipping with a continent then it not working properly',
            ),
            array(
                'title'       => 'Global Multiple Zone Conflict (Shipping)',
                'type'        => 'Fix',
                'description' => 'Global multiple zone conflict issue in shipping',
            ),
            array(
                'title'       => 'Paypal Gateway Fee not Showing on All Logs (PayPal)',
                'type'        => 'Fix',
                'description' => 'Paypal gateway fee not showing on all logs when products purchase by multi vendors',
            ),
            array(
                'title'       => 'CSV Import Not Working with WordPress v5.5 (Import/Export Tool)',
                'type'        => 'Fix',
                'description' => 'CSV import not working cause of JS error',
            ),
            array(
                'title'       => 'Product Addon Conflicting with WooCommerce Booking (Product Addon)',
                'type'        => 'Fix',
                'description' => 'Product addon conflicting with WooCommerce booking when try to add new addon fields',
            ),
            array(
                'title'       => 'Tags List Loading Problem',
                'type'        => 'Fix',
                'description' => 'Long tags listing issue fixed on product quick edit area',
            ),
            array(
                'title'       => 'Duplicate Booking Email',
                'type'        => 'Fix',
                'description' => 'Vendor getting duplicate booking email when new customer booking',
            ),
            array(
                'title'       => 'Store Review Author Name (Store Review)',
                'type'        => 'Fix',
                'description' => 'Store review author name show display name if exits',
            ),
            array(
                'title'       => 'Yoast SEO Hooks Changed',
                'type'        => 'Fix',
                'description' => 'Yoast SEO plugin some hooks changed on latest version',
            ),
            array(
                'title'       => 'Update Vendor Analytics Logo and Key (Vendor Analytics)',
                'type'        => 'Fix',
                'description' => 'Update Vendor Analytics module logo and primary metrics key',
            ),
            array(
                'title'       => 'Store Category Resets',
                'type'        => 'Fix',
                'description' => 'Store category resets after updating store Payment details',
            ),
            array(
                'title'       => 'Automatic Save Zone Location Data (Shipping)',
                'type'        => 'Fix',
                'description' => 'Automatic save zone location data during method add, edit and delete',
            ),
            array(
                'title'       => 'Product Type not Saving',
                'type'        => 'Fix',
                'description' => 'Product type not saving when product addon module active with WooCommerce product addon',
            ),
            array(
                'title'       => 'RMA Request Delete by Vendor',
                'type'        => 'Fix',
                'description' => 'RMA request delete by vendor and change text-domain',
            ),
            array(
                'title'       => 'Add Missing Permission Callback in REST Routes',
                'type'        => 'Fix',
                'description' => 'Add missing permission callback in REST routes to make WordPress 5.5 compatible',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.6',
        'released' => '2020-07-23',
        'changes'  => array(
            array(
                'title'       => 'Shipping Issue with Same zone Multiple postcode (Shipping)',
                'type'        => 'Fix',
                'description' => 'Full Shipping system revamped our codes structure and make performance improvement where allowing same country multiple zones',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.5',
        'released' => '2020-07-23',
        'changes'  => array(
            array(
                'title'       => 'Decimal and Thousand Separator with Comma',
                'type'        => 'New',
                'description' => 'Now allowing decimal and thousand separator with comma sign in every where',
            ),
            array(
                'title'       => 'New 3 Columns Added on All Logs (Vendor Subscription Module)',
                'type'        => 'New',
                'description' => 'Gateway Fee, Total Shipping and Total Tax 3 new columns added on all logs',
            ),
            array(
                'title'       => 'Gallery Image Restriction (Vendor Subscription Module)',
                'type'        => 'New',
                'description' => 'Gallery image restriction count for vendor subscription module',
            ),
            array(
                'title'       => 'Token Issue with Dokan Stripe Module',
                'type'        => 'Fix',
                'description' => 'Stripe token issue come when try to payment with stripe for logged and guest use',
            ),
            array(
                'title'       => 'Shipping Issue with Same Country Multiple Zones (Shipping)',
                'type'        => 'Fix',
                'description' => 'Full Shipping system revamped our codes structure and make performance improvement where allowing same country multiple zones',
            ),
            array(
                'title'       => 'Vendor Subscriptions Product not Allow with Dokan Stripe (Vendor Subscriptions Product)',
                'type'        => 'Fix',
                'description' => 'When try to payment with stripe on Vendor Subscription Product then it not worked',
            ),
            array(
                'title'       => 'After Payment Completed Order Status Not Change (Vendor Subscriptions Product)',
                'type'        => 'Fix',
                'description' => 'Vendor Subscription Products after payment completed order status not changed',
            ),
            array(
                'title'       => 'Gateway Fee Subtract from Admin Commission',
                'type'        => 'Fix',
                'description' => 'Now gateway fee subtract from admin commission value and make it separate column on all logs',
            ),
            array(
                'title'       => 'Products Addon Fields Not Worked for Vendor Staff (Products Addon)',
                'type'        => 'Fix',
                'description' => 'Products Addon fields manage by vendor staff and fields showing on product page',
            ),
            array(
                'title'       => 'Add New Card Not Worked on My Account Page',
                'type'        => 'Fix',
                'description' => 'When try to add new card number in my account page on payment methods tab then it not worked',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.4',
        'released' => '2020-06-19',
        'changes'  => array(
            array(
                'title'       => 'Stripe Module add 2 Requires Options (Stripe Connect)',
                'type'        => 'Fix',
                'description' => 'Stripe Module add 2 requires options must need to add stripe credential and SSL',
            ),
            array(
                'title'       => 'Stripe Module Added 2 Notices (Stripe Connect)',
                'type'        => 'Fix',
                'description' => 'Stripe Module added 2 notices for add stripe credentials and another for SSL activation',
            ),
            array(
                'title'       => 'Geolocation Auto Set Same as Store (Geolocation)',
                'type'        => 'Fix',
                'description' => 'Geolocation auto set same as store when product update from admin',
            ),
            array(
                'title'       => 'Add Text Shipping Policies Link on Shipping Setting Page',
                'type'        => 'Fix',
                'description' => 'Add text Shipping Policies link after gear icon on vendor shipping setting page',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.3',
        'released' => '2020-06-11',
        'changes'  => array(
            array(
                'title'       => 'Add Facebook Messenger to Dokan live chat (Live Chat)',
                'type'        => 'New',
                'description' => 'The Facebook Messenger is new Dokan live chat for vendor single page and product page like as TalkJS',
            ),
            array(
                'title'       => 'Stripe Connect Module Revamped (Stripe Connect)',
                'type'        => 'Improvement',
                'description' => 'Full Stripe Connect Module revamped our codes structure and make performance improvement',
            ),
            array(
                'title'       => 'Vendor Subscription Module Revamped (Vendor Subscription)',
                'type'        => 'Improvement',
                'description' => 'Full Vendor Subscription Module revamped our codes structure and make performance improvement',
            ),
            array(
                'title'       => 'Minimum Amount for Discount Coupon',
                'type'        => 'Fix',
                'description' => 'The minimum amount for discount coupon not working on checkout which amount added by vendor',
            ),
            array(
                'title'       => 'Store Review Not Working for Verified Owner',
                'type'        => 'Fix',
                'description' => 'Store review not working if verified owner option is checked (Store Reviews)',
            ),
            array(
                'title'       => 'Sellers Sitemap XML',
                'type'        => 'Fix',
                'description' => 'Dokan Sellers Sitemap XML file showing 404 when visit it from SEO XML file',
            ),
            array(
                'title'       => 'Shipping Tax Calculates',
                'type'        => 'Fix',
                'description' => 'Shipping tax calculates wrong for sub orders',
            ),
            array(
                'title'       => 'Vendor Subscription Product Error with get_current_screen Function',
                'type'        => 'Fix',
                'description' => 'Remove get_current_screen function from vendor subscription product module (Vendor Subscription Product)',
            ),
            array(
                'title'       => 'Vendor Subscription Product Variation Product Price Not Saving',
                'type'        => 'Fix',
                'description' => 'Variation product price not saving when vendor subscription product module enable issue fixed (Vendor Subscription Product)',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.2',
        'released' => '2020-04-22',
        'changes'  => array(
            array(
                'title'       => 'Vendor Subscription Product Module',
                'type'        => 'New',
                'description' => 'The new Vendor Subscription Product module is a WooCommerce Subscription integration(VSP)',
            ),
            array(
                'title'       => 'JS error in backend report abuse page (Report Abuse)',
                'type'        => 'Fix',
                'description' => 'There was a warning JS error in backend report abuse page, which has been resolved',
            ),
            array(
                'title'       => 'Live chat with elementor issue',
                'type'        => 'Fix',
                'description' => 'Live chat showing fatal error when using with elementor (Elementor)',
            ),
            array(
                'title'       => 'Fatal Error on Booking',
                'type'        => 'Fix',
                'description' => 'Fatal error and calendar issue in frontend booking page (Booking)',
            ),
            array(
                'title'       => 'Vendor Biography Tab Not Showing',
                'type'        => 'Fix',
                'description' => 'Vendor biography tab not showing in store page which is designed with elementor',
            ),
            array(
                'title'       => 'Vendor email issues',
                'type'        => 'Fix',
                'description' => 'Vendor disable email does not work and the vendor enables email is send twice',
            ),
            array(
                'title'       => 'Category Search Issue on Frontpage',
                'type'        => 'Fix',
                'description' => 'When store listing page set as frontpage, category search does not work',
            ),
            array(
                'title'       => 'Unable to create refund from both backend and frontend',
                'type'        => 'Fix',
                'description' => 'Unable to refund order from both backend and frontend if item total is not set',
            ),
        ),
    ),
    array(
        'version'  => 'Version 3.0.0',
        'released' => '2020-03-25',
        'changes'  => array(
            array(
                'title'       => 'Brand Support for Single Product Multivendor',
                'type'        => 'New',
                'description' => 'Brand support for single product multivendor and normal clone products (SPMV)',
            ),
            array(
                'title'       => 'Outdated Template Warning on Vendor Migration Page',
                'type'        => 'Fix',
                'description' => 'There was a warning regarding outdated template in vendor migration page, which has been resolved',
            ),
            array(
                'title'       => 'Store Progressbar Issue',
                'type'        => 'Fix',
                'description' => 'Store progressbar was\'t updating when vendor save stripe or wirecard payment method (Stipre & Wirecard)',
            ),
            array(
                'title'       => 'Seller Vacation Issue',
                'type'        => 'Fix',
                'description' => 'Customer was able to place order from sellers who are on vacation (Seller Vacation)',
            ),
            array(
                'title'       => 'Vendor Staff Permissions Label',
                'type'        => 'Fix',
                'description' => 'Make vendor staff permissions label translatable (Vendor Staff)',
            ),
            array(
                'title'       => 'Product Review Pagination',
                'type'        => 'Fix',
                'description' => 'Product review pagination is not working correctly',
            ),
            array(
                'title'       => 'Geolocation Map Issue',
                'type'        => 'Fix',
                'description' => 'MAP on the store listing page is not showing if Google API key field is empty but Mapbox (Geolocation)',
            ),
            array(
                'title'       => 'Geolocation Product Update Issue',
                'type'        => 'Fix',
                'description' => 'Modifying the product from the Admin backend reverts the product location to `same as store` (Geolocation)',
            ),
            array(
                'title'       => 'Stripe Refund Issue',
                'type'        => 'Fix',
                'description' => 'If admin has earning from an order, only then refund application fee (Stripe)',
            ),
            array(
                'title'       => 'Module Documention',
                'type'        => 'Improvement',
                'description' => 'Added documentation link for modules in admin module page',
            ),
            array(
                'title'       => 'Code Structure and Performance Improvement',
                'type'        => 'Improvement',
                'description' => 'We have revamped our code structure and make performance improvement',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.13',
        'released' => '2019-08-29',
        'changes'  => array(
            array(
                'title'         => 'Scheduled Announcement',
                'type'          => 'New',
                'description'   => 'Add scheduled announcement option for admin.',
            ),
            array(
                'title'         => 'Identity Verification in Live Chat',
                'type'          => 'New',
                'description'   => 'Add identity verification and unread message count in live chat (Live Chat Module).',
            ),
            array(
                'title'         => 'Admin Defined Default Geolocation',
                'type'          => 'New',
                'description'   => 'Add admin defined location on Geolocation map to be shown instead of default `Dhaka, Bangladesh` when there is no vendor or product found (Geolocation Module).',
            ),
            array(
                'title'         => 'Guest User Checkout',
                'type'          => 'fix',
                'description'   => 'Guest user is unable to checkout with stripe (Stripe Module).',
            ),
            array(
                'title'         => 'Stripe Certificate Missing Issue',
                'type'          => 'Fix',
                'description'   => 'Add ca-certificate file to allow certificate verification of stripe SSL (Stripe Module).',
            ),
            array(
                'title'         => 'Shipping doesn\'t Work on Variable Product',
                'type'          => 'Fix',
                'description'   => 'If variable product is created by admin for a vendor, vendor shipping method doesn\'t work.',
            ),
            array(
                'title'         => 'Payment Fields are Missing in Edit Vendor Page',
                'type'          => 'Fix',
                'description'   => 'Set default bank payment object if it\'s not found from the API response.',
            ),
            array(
                'title'         => 'Product Lot Discount on Sub Orders',
                'type'          => 'Fix',
                'description'   => 'Product lot discount is getting applied on sub-orders even though discount is disabled.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.12',
        'released' => '2019-08-09',
        'changes'  => array(
            array(
                'title'         => 'Stripe 3D Secure and Authentication',
                'type'          => 'New',
                'description'   => 'Add stripe 3D secure and strong customer authentication (Stripe Connect Module).',
            ),
            array(
                'title'         => 'Subscription Upgrade Downgrade',
                'type'          => 'New',
                'description'   => 'Add subscription pack upgrade downgrade option for vendors (Subscription Module).',
            ),
            array(
                'title'         => 'Wholesale Options in Backend',
                'type'          => 'New',
                'description'   => 'Add wholesale options in the admin backend (Wholesale Module).',
            ),
            array(
                'title'         => 'Elementor Vendor Verification Widget',
                'type'          => 'New',
                'description'   => 'Add support for vendor verification widget (Elementor Module).',
            ),
            array(
                'title'         => 'Product Discount',
                'type'          => 'Fix',
                'description'   => 'Attach product discount in order details.',
            ),
            array(
                'title'         => 'Coupon Type Changes',
                'type'          => 'Fix',
                'description'   => 'Coupon discount type changes on coupon edit. This issue has been fixed in this release.',
            ),
            array(
                'title'         => 'Order Refund from Admin Backend',
                'type'          => 'Fix',
                'description'   => 'Refund calculation was wrong when it\'s done from the admin backend. It\'s been fixed in this release.',
            ),
            array(
                'title'         => 'Dokan Admin Settings',
                'type'          => 'Improvement',
                'description'   => 'Dokan admin settings rearrange and refactor.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.11',
        'released' => '2019-07-02',
        'changes'  => array(
            array(
                'title'         => 'Elementor Module',
                'type'          => 'New',
                'description'   => 'Add elementor page builder widgets for Dokan.',
            ),
            array(
                'title'         => 'Single Product Multi Vendor',
                'type'          => 'Improvement',
                'description'   => 'Single product multiple vendor hide duplicates based on admin settings.',
            ),
            array(
                'title'         => 'Zone Wise Vendor Shipping',
                'type'          => 'Fix',
                'description'   => 'Limit your zone location by default was enabled, which is incorrect. It should only be enabled when admin limit the zone.',
            ),
            array(
                'title'         => 'Vendor Biography Tab',
                'type'          => 'Fix',
                'description'   => 'Line break and youtube video was not working in vendor biography tab. We have fixed the issue in this update.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.10',
        'released' => '2019-06-19',
        'changes'  => array(
            array(
                'title'         => 'Vendor Biography Tab',
                'type'          => 'New',
                'description'   => 'Add vendor biography tab in dokan store page',
            ),
            array(
                'title'         => 'Filtering and Searching Options',
                'type'          => 'New',
                'description'   => 'Add filtering and searching option in admin report logs area',
            ),
            array(
                'title'         => 'Vendor Vacation',
                'type'          => 'New',
                'description'   => 'Add multiple vacation date system for vendor',
            ),
            array(
                'title'         => 'Refund Request Validation',
                'type'          => 'Fix',
                'description'   => 'Validate refund request in seller dashboard',
            ),
            array(
                'title'         => 'Coupon Validation',
                'type'          => 'Fix',
                'description'   => 'Ensure coupon works on vendors product not the cart',
            ),
            array(
                'title'         => 'Best Selling and Top Rated Widget',
                'type'          => 'Fix',
                'description'   => 'Remove subscription product from best selling and top rated product widget',
            ),
            array(
                'title'         => 'Subscription Renew and Cancellation',
                'type'          => 'Fix',
                'description'   => 'Subscription renew and cancellation with PayPal',
            ),
            array(
                'title'         => 'Store Progressbar',
                'type'          => 'Improvement',
                'description'   => 'Store progress serialization and congrats message on 100% profile completenes',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.9',
        'released' => '2019-05-15',
        'changes'  => array(
            array(
                'title'         => 'Translation issue',
                'type'          => 'Fix',
                'description'   => 'Make coupon strings translatable',
            ),
            array(
                'title'         => 'Report Abuse Module thumbnail',
                'type'          => 'Improvement',
                'description'   => 'Add thumbnail and description of report abuse module',
            ),
            array(
                'title'         => 'Social login and vendor verification',
                'type'          => 'Improvement',
                'description'   => 'Refactor social login and vendor verification module',
            ),
            array(
                'title'         => 'Change Moip brand to wirecard',
                'type'          => 'Improvement',
                'description'   => 'Rename Moip to Wirecard payment gateway',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.8',
        'released' => '2019-05-07',
        'changes'  => array(
            array(
                'title'         => 'Report Abuse',
                'type'          => 'New',
                'description'   => 'Customer will be able to report againts product.',
            ),
            array(
                'title'         => 'Vendor Add Edit',
                'type'          => 'New',
                'description'   => 'Admin will be able to create new Vendor from the backend',
            ),
            array(
                'title'         => 'Dokan Booking',
                'type'          => 'New',
                'description'   => 'Add restricted days functionality in dokan booking module',
            ),
            array(
                'title'         => 'Single Product Multi Vendor',
                'type'          => 'New',
                'description'   => 'Enable SPMV for admins to duplicate products from admin panel',
            ),
            array(
                'title'         => 'Store Category',
                'type'          => 'Fix',
                'description'   => 'Fix store category list table search form',
            ),
            array(
                'title'         => 'Duplicate Subscription Form',
                'type'          => 'Fix',
                'description'   => 'Subscription form is rendering twice in registration form',
            ),
            array(
                'title'         => 'Subscription Cancellation',
                'type'          => 'Fix',
                'description'   => 'Cancel subscription doesn\'t work for manually assigned subscription',
            ),
            array(
                'title'         => 'Vendor Shipping',
                'type'          => 'new',
                'description'   => 'Add wilecard and range matching for vendor shipping zone',
            ),
            array(
                'title'         => 'Depricated Functions',
                'type'          => 'Improvement',
                'description'   => 'Replace get_woocommerce_term_meta with get_term_meta as it was deprecated',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.7',
        'released' => '2019-03-25',
        'changes'  => array(
            array(
                'title'         => 'Store Category',
                'type'          => 'New',
                'description'   => 'Vendor will be able to register under specefic cateogry. ei(Furniture, Mobile)',
            ),
            array(
                'title'         => 'YITH WC Brand Compatible',
                'type'          => 'New',
                'description'   => 'Make Dokan YITH WC Brand add-on compatible',
            ),
            array(
                'title'         => 'Date and refund column in admin logs area',
                'type'          => 'New',
                'description'   => 'Add date and refund column in admin logs area to get more detaild overview.',
            ),
            array(
                'title'         => 'Product Status',
                'type'          => 'New',
                'description'   => 'Change product status according to subscription status',
            ),
            array(
                'title'         => 'Show button for non logged-in user',
                'type'          => 'Fix',
                'description'   => 'Show button for non logged-in user',
            ),
            array(
                'title'         => 'Refund Calculation Issue',
                'type'          => 'Fix',
                'description'   => 'Send refund admin commission to customer',
            ),
            array(
                'title'         => 'Error on subscription cancellation email',
                'type'          => 'Fix',
                'description'   => 'There was an error on subscription cancellation, which has been fixed in this release.',
            ),
            array(
                'title'         => 'Trial Subscription',
                'type'          => 'Improvement',
                'description'   => 'When a vendor subscribe to a trial subscription, make all other trial to non-trial subscription for that vendor',
            ),
            array(
                'title'         => 'Social Login Issue',
                'type'          => 'Fix',
                'description'   => 'Update social login and vendor verification API',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.5',
        'released' => '2019-02-18',
        'changes'  => array(
            array(
                'title'         => 'Automate order refund process via stripe',
                'type'          => 'New',
                'description'   => 'Vendor can now send automatic refund to their customer from vendor order dashboard',
            ),
            array(
                'title'         => 'Add trial subscription (Subscription Module)',
                'type'          => 'New',
                'description'   => 'Admin can now offer trail subscription for vendors',
            ),
            array(
                'title'         => 'Product type & gallery image restriction',
                'type'          => 'New',
                'description'   => 'Admin can now restrict product type & gallery image upload for vendor subscription',
            ),
            array(
                'title'         => 'Privacy and Policy',
                'type'          => 'New',
                'description'   => 'Admin can configure privacy policy info for frontend product enquiry form',
            ),
            array(
                'title'         => 'Email notification for store follow',
                'type'          => 'Fix',
                'description'   => 'Now vendor can get email notification on store follows and unfollows',
            ),
            array(
                'title'         => 'Unable to select country or state in vendor shipping',
                'type'          => 'Fix',
                'description'   => 'Country dropdown not working in shipping and announcement',
            ),
            array(
                'title'         => 'Admin report logs calculation issue is fixed in admin dashboard',
                'type'          => 'Fix',
                'description'   => 'Some calculation issue fixed in admin reports',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.4',
        'released' => '2019-01-23',
        'changes'  => array(
            array(
                'title'         => 'Wholesale Module(Business, Enterprise Package)',
                'type'          => 'New',
                'description'   => 'Added new Wholesale module. Vendor can offer wholesale price for his/her products.',
            ),
            array(
                'title'         => 'Return and Warranty Module(Professional, Business, Enterprise Package)',
                'type'          => 'New',
                'description'   => 'Vendor can offer warranty and return system for their products and customer can take this warranty offers',
            ),
            array(
                'title'         => 'Subscription cancellation email',
                'type'          => 'New',
                'description'   => 'Now admin can get email if any subscription is cancelled by vendor',
            ),
            array(
                'title'         => 'Subscription Unlimited pack',
                'type'          => 'New',
                'description'   => 'Admin can offer unlimited package for vendor subscription',
            ),
            array(
                'title'         => 'MOIP Gateway connection issue',
                'type'          => 'Fix',
                'description'   => 'Change some gateway api params for connection moip gateway',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.3',
        'released' => '2018-12-18',
        'changes'  => array(
            array(
                'title'         => 'ShipStation Module(Business, Enterprise Package)',
                'type'          => 'New',
                'description'   => 'Added new ShipStation module',
            ),
            array(
                'title'         => 'Follow Store Module(Professional, Business, Enterprise Package)',
                'type'          => 'New',
                'description'   => 'Added Follow Store module',
            ),
            array(
                'title'         => 'Product Quick Edit',
                'type'          => 'New',
                'description'   => 'Added Quick edit option for product in vendor dashboard.',
            ),
            array(
                'title'         => 'Searching Option',
                'type'          => 'New',
                'description'   => 'Add searching option in dokan vendor and refund page',
            ),
            array(
                'title'         => 'Admin Tools & Subscription Page Improvement',
                'type'          => 'Improvement',
                'description'   => 'Rewrite admin tools & subscription page in vue js',
            ),
            array(
                'title'         => 'Filter form & Map in Category Page',
                'type'          => 'Fix',
                'description'   => 'Show filter form and map in product category pages (geolocation module)',
            ),
            array(
                'title'         => 'Bookable Product Commission',
                'type'          => 'Fix',
                'description'   => 'Add per product commission option for bookable product',
            ),
            array(
                'title'         => 'Refund Calculation Issue',
                'type'          => 'Fix',
                'description'   => 'Refund calculation is wrong when shipping fee recipient is set to vendor',
            ),
            array(
                'title'         => 'Bulk Refund is Not Working',
                'type'          => 'Fix',
                'description'   => 'Approving batch refund is not working in admin backend',
            ),
            array(
                'title'         => 'Product Stock Issue on Refund',
                'type'          => 'Fix',
                'description'   => 'Increase stock ammount if the product is refunded',
            ),
            array(
                'title'         => 'Category Restriction Issue',
                'type'          => 'Fix',
                'description'   => 'Booking product category restriction for subscription pack is not working',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.2',
        'released' => '2018-11-09',
        'changes'  => array(
            array(
                'title'         => 'Geolocation Module',
                'type'          => 'New',
                'description'   => 'Added zoom level settings in geolocation module.',
            ),
            array(
                'title'         => 'Zone Wise Shipping',
                'type'          => 'New',
                'description'   => 'Added shipping policy and processing time settings in zone wise shipping.',
            ),
            array(
                'title'         => 'Rest API for Store Reviews',
                'type'          => 'New',
                'description'   => 'Added rest API support for store review post type.',
            ),
            array(
                'title'         => 'Show Tax on Bookable Product',
                'type'          => 'Fix',
                'description'   => 'Show tax on bookable product for vendor',
            ),

            array(
                'title'         => 'Product Importing Issue for Subscribed Vendor',
                'type'          => 'Fix',
                'description'   => 'Allow vendor to import only allowed number of products.',
            ),
            array(
                'title'         => 'Product and Order Discount Issue',
                'type'          => 'Fix',
                'description'   => 'Product and order discount for vendor is not working.',
            ),
            array(
                'title'         => 'Shipping Class Issue',
                'type'          => 'Fix',
                'description'   => 'Shipping class is not saving for bookable product.',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.9.0',
        'released' => '2018-10-03',
        'changes'  => array(
            array(
                'title'         => 'Geolocation Module',
                'type'          => 'New',
                'description'   => 'Enable this module to let the customers search for a specific product or vendor using any location they want.',
            ),
            array(
                'title'         => 'Moip Payment Gateway',
                'type'          => 'New',
                'description'   => 'Use one of the most popular payment system known for it\'s efficiency with Dokan.',
            ),
            array(
                'title'         => 'Allow Vendor to crate tags',
                'type'          => 'New',
                'description'   => 'Your vendors don\'t need to rely on prebuilt tags anymore. Now they can create their own in seconds',
            ),
            array(
                'title'         => 'Responsive Admin Pages',
                'type'          => 'New',
                'description'   => 'All the admin backend pages is now responsive for all devices',
            ),
            array(
                'title'         => 'Staff email for New Order',
                'type'          => 'New',
                'description'   => 'Staff will able to get all emails for new order from customer',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.8.3',
        'released' => '2018-07-19',
        'changes'  => array(
            array(
                'title'         => 'Live Chat Module',
                'type'          => 'Fix',
                'description'   => 'Right now the chat box is available in customer myaccount page and also make responsive chat box window',
            ),
            array(
                'title'         => 'Statement and Refund',
                'type'          => 'Fix',
                'description'   => 'Change core table structure for refund and statements. Now its easy to understand for vendor to check her statements. Also fixed statement exporting problem',
            ),
            array(
                'title'         => 'Zonewise Shipping',
                'type'          => 'Fix',
                'description'   => 'Shipping state rendering issue fixed. If any country have no states then states not showing undefine problem',
            ),
            array(
                'title'         => 'Stripe Module',
                'type'          => 'Fix',
                'description'   => 'Card is automatically saved if customer does not want to save his/her card info during checkout',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.8.2',
        'released' => '2018-06-29',
        'changes'  => array(
            array(
                'title'         => 'Live Chat Module',
                'type'          => 'New',
                'description'   => 'Vendors will now be able to provide live chat support to visitors and customers through this TalkJS integration. Talk from anywhere in your store, add attachments, get desktop notifications, enable email notifications, and store all your messages safely in Vendor Inbox.<br><iframe width="560" height="315" src="https://www.youtube.com/embed/BHuTLjY78cY" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>',
            ),
            array(
                'title'         => 'Added Refund and Announcement REST API',
                'type'          => 'New',
                'description'   => 'Admins can now modify refund and announcement section of Dokan easily through the Rest API',
            ),
            array(
                'title'         => 'Local pickup is visible when the cost is set to zero',
                'type'          => 'Fix',
                'description'   => 'When local pickup cost in Dokan Zone-wise shipping is set to zero it will show on the cart/checkout page',
            ),
            array(
                'title'         => 'Store Support ticket is visible in customer dashboard support menu',
                'type'          => 'Fix',
                'description'   => 'Now customers can view the support tickets they create in My Account> support ticket area',
            ),
            array(
                'title'         => 'Added tax and shipping functionalities in auction product',
                'type'          => 'Fix',
                'description'   => 'Now admins can add shipping and tax rates for auctionable product',
            ),
            array(
                'title'         => 'Appearance module for admins',
                'type'          => 'Fix',
                'description'   => 'Now Admins can view Color Customizer settings in backend without any problem',
            ),
            array(
                'title'         => 'Unable to delete vendor form admin panel',
                'type'          => 'Fix',
                'description'   => 'Admin was unable to delete a vendor from admin panel',
            ),
        ),
    ),
    array(
        'version'  => 'Version 2.8.0',
        'released' => '2018-05-01',
        'changes'  => array(
            array(
                'title'         => 'Introduction of REST APIs',
                'type'          => 'New',
                'description'   => 'We have introduced REST APIs in dokan',
            ),
            array(
                'title'         => 'Zone wize shipping',
                'type'          => 'New',
                'description'   => 'We have introduced zone wize shipping functionality similar to WooCommerce in dokan. <img src="https://wedevs-com-wedevs.netdna-ssl.com/wp-content/uploads/2018/04/dokan-vendor-dashboard-settings-shipping-method-settings.gif">',
            ),
            array(
                'title'         => 'Earning suggestion for variable product',
                'type'          => 'New',
                'description'   => 'As like simple product, vendor will get to see the earning suggestion for variable product as well',
            ),
            array(
                'title'         => 'Confirmation on subscription cancellation',
                'type'          => 'New',
                'description'   => 'Cancellation of a subscription pack will ask for confirmation',
            ),
            array(
                'title'         => 'Unable to login with social media',
                'type'          => 'Fix',
                'description'   => 'Customer, Seller was unable to login with social media',
            ),
            array(
                'title'         => 'CSV earning report exporting',
                'type'          => 'Fix',
                'description'   => 'There were an issue with CSV report exporting from back end',
            ),
            array(
                'title'         => 'Unable to delete vendor form admin panel',
                'type'          => 'Fix',
                'description'   => 'Admin was unable to delete a vendor from admin panel',
            ),
            array(
                'title'         => 'Seller setup wizard is missing during email verification',
                'type'          => 'Fix',
                'description'   => 'Seller setup wizard after a seller is verified by email was missing',
            ),
            array(
                'title'         => 'Subscription Free pack visibility',
                'type'          => 'Fix',
                'description'   => 'Hide subscription product type from back end when a seller can access the back end',
            ),
            array(
                'title'         => 'Disable back end access for vendor staff',
                'type'          => 'Improvement',
                'description'   => 'Disable back end access for vendor staff for security perpose',
            ),
            array(
                'title'         => 'Updated deprecated functions',
                'type'          => 'Improvement',
                'description'   => 'Updated some deprecated functions',
            ),
            array(
                'title'         => 'Statement calculation',
                'type'          => 'Improvement',
                'description'   => 'Statement calculation',
            ),
            array(
                'title'         => 'Reduction of \'dokan\' text from staff permission',
                'type'          => 'Improvement',
                'description'   => 'Reduction of \'dokan\' text from staff permission',
            ),
            array(
                'title'         => 'Various UI, UX improvement',
                'type'          => 'Improvement',
                'description'   => 'Various UI, UX improvement',
            ),
        ),
    ),
);

function _dokan_changelog_content( $content ) {
    $content = wpautop( $content, true );

    return $content;
}
?>

<div class="wrap dokan-whats-new">
    <h1>What's New in Dokan?</h1>

    <div class="wedevs-changelog-wrapper">

        <?php foreach ( $changelog as $release ) { ?>
            <div class="wedevs-changelog">
                <div class="wedevs-changelog-version">
                    <h3><?php echo esc_html( $release['version'] ); ?></h3>
                    <p class="released">
                        (<?php echo human_time_diff( time(), strtotime( $release['released'] ) ); ?> ago)
                    </p>
                </div>
                <div class="wedevs-changelog-history">
                    <ul>
                        <?php foreach ( $release['changes'] as $change ) { ?>
                            <li>
                                <h4>
                                    <span class="title"><?php echo esc_html( $change['title'] ); ?></span>
                                    <span class="label <?php echo strtolower( $change['type'] ); ?>"><?php echo esc_html( $change['type'] ); ?></span>
                                </h4>

                                <div class="description">
                                    <?php echo _dokan_changelog_content( $change['description'] ); ?>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

</div>
<?php
$versions = get_option( 'dokan_whats_new_versions', array() );

if ( ! in_array( DOKAN_PRO_PLUGIN_VERSION, $versions, true ) ) {
    $versions[] = DOKAN_PRO_PLUGIN_VERSION;
}

update_option( 'dokan_whats_new_versions', $versions );
?>
<style type="text/css">

.error, .udpated, .info, .notice {
    display: none;
}

.dokan-whats-new h1 {
    text-align: center;
    margin-top: 20px;
    font-size: 30px;
}

.wedevs-changelog {
    display: flex;
    max-width: 920px;
    border: 1px solid #e5e5e5;
    padding: 12px 20px 20px 20px;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}

.wedevs-changelog-wrapper .wedevs-support-help {

}

.wedevs-changelog .wedevs-changelog-version {
    width: 360px;
}

.wedevs-changelog .wedevs-changelog-version .released {
    font-style: italic;
}

.wedevs-changelog .wedevs-changelog-history {
    width: 100%;
    font-size: 14px;
}

.wedevs-changelog .wedevs-changelog-history li {
    margin-bottom: 30px;
}

.wedevs-changelog .wedevs-changelog-history h4 {
    margin: 0 0 10px 0;
    font-size: 1.3em;
    line-height: 26px;
}

.wedevs-changelog .wedevs-changelog-history p {
    font-size: 14px;
    line-height: 1.5;
}

.wedevs-changelog .wedevs-changelog-history img,
.wedevs-changelog .wedevs-changelog-history iframe {
    margin-top: 30px;
    max-width: 100%;
}

.wedevs-changelog-history span.label {
    margin-left: 10px;
    position: relative;
    color: #fff;
    border-radius: 20px;
    padding: 0 8px;
    font-size: 12px;
    height: 20px;
    line-height: 19px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    font-weight: normal;
}

span.label.new {
    background: #3778ff;
    border: 1px solid #3778ff;
}

span.label.improvement {
    background: #3aaa55;
    border: 1px solid #3aaa
}

span.label.fix {
    background: #ff4772;
    border: 1px solid #ff4772;
}

</style>
