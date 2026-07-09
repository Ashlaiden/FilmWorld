<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Create Custom Payments Table
|--------------------------------------------------------------------------
*/

function filmworld_maybe_create_payments_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'filmworld_payments';
    $current_version = get_option('filmworld_payments_db_version', '0');

    if ($current_version === '1.0') {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
        gateway varchar(50) NOT NULL DEFAULT '',
        plan_key varchar(50) NOT NULL DEFAULT '',
        plan_name varchar(200) NOT NULL DEFAULT '',
        amount bigint(20) UNSIGNED NOT NULL DEFAULT 0,
        authority varchar(200) NOT NULL DEFAULT '',
        ref_id varchar(200) NOT NULL DEFAULT '',
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        paid_at datetime DEFAULT NULL,
        ip_address varchar(45) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY status (status),
        KEY gateway (gateway),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('filmworld_payments_db_version', '1.0');

    // Migrate old user-meta payments
    filmworld_migrate_old_payments();
}

function filmworld_migrate_old_payments() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'filmworld_payments';

    if (get_option('filmworld_payments_migrated')) {
        return;
    }

    $users = get_users(['number' => 500]);
    $inserted = 0;

    foreach ($users as $user) {
        $payments = get_user_meta($user->ID, 'filmworld_payments', true);
        if (!is_array($payments) || empty($payments)) {
            continue;
        }

        foreach ($payments as $p) {
            if ($p['status'] !== 'success') {
                continue;
            }

            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE authority = %s AND ref_id = %s",
                $p['authority'] ?? '',
                $p['ref_id'] ?? ''
            ));

            if ($exists) {
                continue;
            }

            $wpdb->insert($table_name, [
                'user_id'    => $user->ID,
                'gateway'    => $p['gateway'] ?? 'zarinpal',
                'plan_key'   => $p['plan'] ?? '',
                'plan_name'  => $p['plan_name'] ?? '',
                'amount'     => $p['amount'] ?? 0,
                'authority'  => $p['authority'] ?? '',
                'ref_id'     => $p['ref_id'] ?? '',
                'status'     => 'success',
                'created_at' => $p['date'] ?? current_time('mysql'),
                'paid_at'    => $p['date'] ?? current_time('mysql'),
            ]);

            $inserted++;
        }
    }

    if ($inserted > 0) {
        update_option('filmworld_payments_migrated', '1');
    }
}

add_action('admin_init', 'filmworld_maybe_create_payments_table');

/*
|--------------------------------------------------------------------------
| Payment Gateway Abstract Class
|--------------------------------------------------------------------------
*/

abstract class FilmWorld_Gateway {

    abstract public function get_id();
    abstract public function get_name();
    abstract public function get_payment_url($token);
    abstract public function get_settings_fields();
    abstract public function create_payment($amount, $description, $callback_url, $user_id);
    abstract public function verify_payment($amount, $authority);

    /**
     * Get a gateway-specific setting value
     */
    public function get_setting($field_key) {
        return get_option('filmworld_gw_' . $this->get_id() . '_' . $field_key, '');
    }

    /**
     * Check if gateway is properly configured
     */
    public function is_configured() {
        $fields = $this->get_settings_fields();
        foreach ($fields as $field) {
            if (!empty($field['required']) && empty(get_option($field['id']))) {
                return false;
            }
        }
        return true;
    }
}

/*
|--------------------------------------------------------------------------
| Zarinpal Gateway
|--------------------------------------------------------------------------
*/

class FilmWorld_Gateway_Zarinpal extends FilmWorld_Gateway {

    public function get_id() {
        return 'zarinpal';
    }

    public function get_name() {
        return 'زرین‌پال';
    }

    public function get_payment_url($authority) {
        $sandbox = $this->get_setting('sandbox') === 'yes';
        return ($sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/'
        ) . $authority;
    }

    public function get_settings_fields() {
        return [
            [
                'id'          => 'filmworld_gw_zarinpal_merchant',
                'key'         => 'merchant',
                'label'       => 'مرچنت کد',
                'type'        => 'text',
                'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'description' => 'مرچنت کد دریافتی از پنل زرین‌پال',
                'required'    => true,
                'dir'         => 'ltr',
            ],
            [
                'id'          => 'filmworld_gw_zarinpal_sandbox',
                'key'         => 'sandbox',
                'label'       => 'حالت تست (Sandbox)',
                'type'        => 'select',
                'options'     => [
                    'yes' => 'بله - حالت تست',
                    'no'  => 'خیر - پرداخت واقعی',
                ],
                'description' => 'در حالت تست تراکنش‌ها واقعی نیستند',
                'required'    => false,
            ],
        ];
    }

    public function create_payment($amount, $description, $callback_url, $user_id) {
        $merchant = $this->get_setting('merchant');

        if (empty($merchant)) {
            return new WP_Error('no_merchant', 'مرچنت کد زرین‌پال تنظیم نشده است.');
        }

        $sandbox = $this->get_setting('sandbox') === 'yes';
        $url = $sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json';

        $response = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'merchant_id'  => $merchant,
                'amount'       => (int) $amount,
                'description'  => $description,
                'callback_url' => $callback_url,
                'metadata'     => ['user_id' => $user_id],
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('connection_error', 'خطا در اتصال به زرین‌پال.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['errors'])) {
            $code = $body['errors']['code'] ?? 'نامشخص';
            return new WP_Error('payment_error', 'خطای زرین‌پال (کد: ' . $code . ')');
        }

        if (isset($body['data']['code']) && $body['data']['code'] === 100) {
            return [
                'authority' => $body['data']['authority'],
                'url'       => $this->get_payment_url($body['data']['authority']),
            ];
        }

        $code = $body['data']['code'] ?? 'نامشخص';
        return new WP_Error('payment_failed', 'خطا در ایجاد تراکنش زرین‌پال (کد: ' . $code . ')');
    }

    public function verify_payment($amount, $authority) {
        $merchant = $this->get_setting('merchant');
        $sandbox = $this->get_setting('sandbox') === 'yes';

        $url = $sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://api.zarinpal.com/pg/v4/payment/verify.json';

        $response = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'merchant_id' => $merchant,
                'amount'      => (int) $amount,
                'authority'   => $authority,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('verify_error', 'خطا در اتصال به زرین‌پال.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['data']['code']) && $body['data']['code'] === 100) {
            return [
                'success'   => true,
                'ref_id'    => $body['data']['ref_id'],
                'authority' => $authority,
                'amount'    => $amount,
            ];
        }

        $code = $body['data']['code'] ?? 0;
        $messages = [
            101 => 'پرداخت قبلاً تأیید شده است.',
            102 => 'پرداخت لغو شده توسط کاربر.',
            103 => 'پرداخت ناموفق بوده است.',
        ];
        $msg = $messages[$code] ?? 'خطا در تأیید پرداخت (کد: ' . $code . ')';
        return new WP_Error('verify_failed', $msg);
    }
}



/*
|--------------------------------------------------------------------------
| Zibal Gateway
|--------------------------------------------------------------------------
*/

class FilmWorld_Gateway_Zibal extends FilmWorld_Gateway {

    public function get_id() {
        return 'zibal';
    }

    public function get_name() {
        return 'زیبال';
    }

    public function get_payment_url($trackId) {
        return 'https://gateway.zibal.ir/start/' . $trackId;
    }

    public function get_settings_fields() {
        return [
            [
                'id'          => 'filmworld_gw_zibal_merchant',
                'key'         => 'merchant',
                'label'       => 'مرچنت کد',
                'type'        => 'text',
                'placeholder' => 'مرچنت کد زیبال',
                'description' => 'مرچنت کد دریافتی از پنل زیبال',
                'required'    => true,
                'dir'         => 'ltr',
            ],
        ];
    }

    public function create_payment($amount, $description, $callback_url, $user_id) {
        $merchant = $this->get_setting('merchant');

        if (empty($merchant)) {
            return new WP_Error('no_merchant', 'مرچنت کد زیبال تنظیم نشده است.');
        }

        $response = wp_remote_post('https://gateway.zibal.ir/v1/request', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'merchant'    => $merchant,
                'amount'      => (int) $amount,
                'callbackUrl' => $callback_url,
                'description' => $description,
                'orderId'     => time() . '-' . $user_id,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('connection_error', 'خطا در اتصال به زیبال.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['result']) && $body['result'] === 100) {
            return [
                'authority' => (string) $body['trackId'],
                'url'       => $this->get_payment_url($body['trackId']),
            ];
        }

        $code = $body['result'] ?? 'نامشخص';
        $msg  = $body['message'] ?? 'خطا در ایجاد تراکنش زیبال (کد: ' . $code . ')';
        return new WP_Error('payment_failed', $msg);
    }

    public function verify_payment($amount, $authority) {
        $merchant = $this->get_setting('merchant');

        $response = wp_remote_post('https://gateway.zibal.ir/v1/verify', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'merchant' => $merchant,
                'trackId'  => (int) $authority,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('verify_error', 'خطا در اتصال به زیبال.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['result']) && $body['result'] === 100) {
            return [
                'success'   => true,
                'ref_id'    => (string) ($body['refNumber'] ?? $authority),
                'authority' => $authority,
                'amount'    => $amount,
            ];
        }

        $code = $body['result'] ?? 0;
        $msg  = $body['message'] ?? 'خطا در تأیید پرداخت زیبال (کد: ' . $code . ')';
        return new WP_Error('verify_failed', $msg);
    }
}

/*
|--------------------------------------------------------------------------
| IDPay Gateway
|--------------------------------------------------------------------------
*/

class FilmWorld_Gateway_IDPay extends FilmWorld_Gateway {

    public function get_id() {
        return 'idpay';
    }

    public function get_name() {
        return 'آی‌دی‌پی (IDPay)';
    }

    public function get_payment_url($id) {
        $sandbox = $this->get_setting('sandbox') === 'yes';
        return ($sandbox ? 'https://idpay.ir/p/sandbox/' : 'https://idpay.ir/p/') . $id;
    }

    public function get_settings_fields() {
        return [
            [
                'id'          => 'filmworld_gw_idpay_api_key',
                'key'         => 'api_key',
                'label'       => 'API Key',
                'type'        => 'text',
                'placeholder' => 'کلید API آی‌دی‌پی',
                'description' => 'کلید API دریافتی از پنل آی‌دی‌پی',
                'required'    => true,
                'dir'         => 'ltr',
            ],
            [
                'id'          => 'filmworld_gw_idpay_sandbox',
                'key'         => 'sandbox',
                'label'       => 'حالت تست (Sandbox)',
                'type'        => 'select',
                'options'     => [
                    'yes' => 'بله - حالت تست',
                    'no'  => 'خیر - پرداخت واقعی',
                ],
                'description' => 'در حالت تست هدر X-SANDBOX: 1 ارسال می‌شود و حساسیت آدرس سایت و IP برداشته می‌شود',
                'required'    => false,
            ],
        ];
    }

    public function create_payment($amount, $description, $callback_url, $user_id) {
        $api_key = $this->get_setting('api_key');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'کلید API آی‌دی‌پی تنظیم نشده است.');
        }

        $sandbox = $this->get_setting('sandbox') === 'yes';

        $response = wp_remote_post('https://api.idpay.ir/v1.1/payment', [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-KEY'    => $api_key,
                'X-SANDBOX'    => $sandbox ? '1' : '0',
            ],
            'body' => wp_json_encode([
                'amount'   => (int) $amount,
                'desc'     => $description,
                'callback' => $callback_url,
                'order_id' => time() . '-' . $user_id,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('connection_error', 'خطا در اتصال به آی‌دی‌پی.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['id']) && !empty($body['link'])) {
            return [
                'authority' => $body['id'],
                'url'       => $body['link'],
            ];
        }

        $msg  = $body['error_message'] ?? 'خطا در ایجاد تراکنش آی‌دی‌پی';
        $code = $body['error_code'] ?? 'نامشخص';
        return new WP_Error('payment_failed', $msg . ' (کد: ' . $code . ')');
    }

    public function verify_payment($amount, $authority) {
        $api_key = $this->get_setting('api_key');
        $sandbox = $this->get_setting('sandbox') === 'yes';

        $response = wp_remote_post('https://api.idpay.ir/v1.1/payment/verify', [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-KEY'    => $api_key,
                'X-SANDBOX'    => $sandbox ? '1' : '0',
            ],
            'body' => wp_json_encode([
                'id'     => $authority,
                'amount' => (int) $amount,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('verify_error', 'خطا در اتصال به آی‌دی‌پی.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['status']) && $body['status'] === 100) {
            return [
                'success'   => true,
                'ref_id'    => (string) ($body['payment']['track_id'] ?? $authority),
                'authority' => $authority,
                'amount'    => $amount,
            ];
        }

        $msg  = $body['error_message'] ?? 'خطا در تأیید پرداخت آی‌دی‌پی';
        $code = $body['error_code'] ?? 'نامشخص';
        return new WP_Error('verify_failed', $msg . ' (کد: ' . $code . ')');
    }
}

/*
|--------------------------------------------------------------------------
| Gateway Registry
|--------------------------------------------------------------------------
*/

function filmworld_get_all_gateways() {
    static $gateways = null;

    if ($gateways === null) {
        $gateways = [
            'zarinpal' => new FilmWorld_Gateway_Zarinpal(),
            'zibal'    => new FilmWorld_Gateway_Zibal(),
            'idpay'    => new FilmWorld_Gateway_IDPay(),
        ];
    }

    return $gateways;
}

function filmworld_get_enabled_gateways() {
    $all     = filmworld_get_all_gateways();
    $enabled = get_option('filmworld_enabled_gateways', ['zarinpal']);

    if (!is_array($enabled)) {
        $enabled = ['zarinpal'];
    }

    $result = [];
    foreach ($enabled as $id) {
        if (isset($all[$id]) && $all[$id]->is_configured()) {
            $result[$id] = $all[$id];
        }
    }

    return $result;
}

function filmworld_get_gateway($id) {
    $all = filmworld_get_all_gateways();
    return $all[$id] ?? null;
}

/*
|--------------------------------------------------------------------------
| AJAX: Initiate Payment (with gateway selection)
|--------------------------------------------------------------------------
*/

function filmworld_ajax_init_payment() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['redirect' => wp_login_url(get_permalink())]);
    }

    $plan_key   = sanitize_text_field($_POST['plan'] ?? '');
    $gateway_id = sanitize_text_field($_POST['gateway'] ?? '');
    $nonce      = $_POST['nonce'] ?? '';

    if (!wp_verify_nonce($nonce, 'filmworld_nonce')) {
        wp_send_json_error(['message' => 'خطای امنیتی. لطفاً دوباره تلاش کنید.']);
    }

    // Validate package
    $packages = filmworld_get_day_packages();
    if (!isset($packages[$plan_key])) {
        wp_send_json_error(['message' => 'بسته انتخاب شده معتبر نیست.']);
    }

    // Validate gateway
    $gateway = filmworld_get_gateway($gateway_id);
    if (!$gateway) {
        wp_send_json_error(['message' => 'درگاه پرداخت انتخاب شده معتبر نیست.']);
    }

    if (!$gateway->is_configured()) {
        wp_send_json_error(['message' => 'درگاه "' . $gateway->get_name() . '" به درستی تنظیم نشده است.']);
    }

    $pkg      = $packages[$plan_key];
    $user_id  = get_current_user_id();

    // Build callback URL with gateway identifier
    $callback = add_query_arg([
        'filmworld_payment' => '1',
        'plan'              => $plan_key,
        'gateway'           => $gateway_id,
    ], home_url('/account/'));

    $result = $gateway->create_payment(
        $pkg['price'],
        $pkg['days'] . ' روز اشتراک - کاربر #' . $user_id,
        $callback,
        $user_id
    );

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // Save pending payment in user meta
    update_user_meta($user_id, 'filmworld_pending_payment', [
        'authority' => $result['authority'],
        'plan'      => $plan_key,
        'gateway'   => $gateway_id,
        'price'     => $pkg['price'],
        'days'      => $pkg['days'],
        'time'      => time(),
    ]);

    // Create payment record in custom table
    global $wpdb;
    $table = $wpdb->prefix . 'filmworld_payments';
    $wpdb->insert($table, [
        'user_id'    => $user_id,
        'gateway'    => $gateway_id,
        'plan_key'   => $plan_key,
        'plan_name'  => $pkg['days'] . ' روز',
        'amount'     => $pkg['price'],
        'authority'  => $result['authority'],
        'status'     => 'pending',
        'created_at' => current_time('mysql'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    wp_send_json_success(['url' => $result['url']]);
}
add_action('wp_ajax_filmworld_init_payment', 'filmworld_ajax_init_payment');

/*
|--------------------------------------------------------------------------
| Payment Callback Handler (supports all gateways)
|--------------------------------------------------------------------------
*/

function filmworld_handle_payment_callback() {
    if (!isset($_GET['filmworld_payment'])) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_die('لطفاً ابتدا وارد حساب کاربری خود شوید.');
    }

    $pending = get_user_meta($user_id, 'filmworld_pending_payment', true);
    if (empty($pending)) {
        wp_die('تراکنش نامعتبر است یا قبلاً پردازش شده.');
    }

    // Detect authority from different gateway callbacks
    $authority = sanitize_text_field(
        $_GET['Authority']   // Zarinpal
        ?? $_GET['trackId']  // Zibal
        ?? $_GET['id']       // IDPay
        ?? $pending['authority']
    );

    $gateway_id = sanitize_text_field(
        $_GET['gateway']
        ?? $pending['gateway']
        ?? 'zarinpal'
    );

    // Verify authority matches pending payment
    if ($pending['authority'] !== $authority) {
        wp_die('تراکنش نامعتبر است.');
    }

    delete_user_meta($user_id, 'filmworld_pending_payment');

    // Determine if payment was cancelled by user
    $is_cancelled = false;
    $gateway = filmworld_get_gateway($gateway_id);

    switch ($gateway_id) {
        case 'zarinpal':
            if (($_GET['Status'] ?? '') !== 'OK') $is_cancelled = true;
            break;
        case 'zibal':
            if (($_GET['success'] ?? '') !== '1') $is_cancelled = true;
            break;
        case 'idpay':
            if (($_GET['status'] ?? '') !== '10') $is_cancelled = true;
            break;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'filmworld_payments';

    if ($is_cancelled) {
        $wpdb->update($table, ['status' => 'cancelled'], ['authority' => $authority, 'user_id' => $user_id]);
        wp_redirect(add_query_arg(['payment' => 'cancelled'], home_url('/account/')));
        exit;
    }

    // Get plan info
    $plan_key = sanitize_text_field($_GET['plan'] ?? $pending['plan']);
    $plans = filmworld_get_plans();
    $plan  = $plans[$plan_key] ?? null;

    if (!$plan) {
        $wpdb->update($table, ['status' => 'failed'], ['authority' => $authority, 'user_id' => $user_id]);
        wp_die('پلن نامعتبر.');
    }

    if (!$gateway) {
        $wpdb->update($table, ['status' => 'failed'], ['authority' => $authority, 'user_id' => $user_id]);
        wp_die('درگاه پرداخت نامعتبر.');
    }

    // Verify payment with gateway
    $result = $gateway->verify_payment($pending['price'], $authority);

    if (is_wp_error($result)) {
        $wpdb->update($table, ['status' => 'failed'], ['authority' => $authority, 'user_id' => $user_id]);
        wp_redirect(add_query_arg(['payment' => 'failed', 'msg' => urlencode($result->get_error_message())], home_url('/account/')));
        exit;
    }

    // Activate — add days (extends if active)
    $days = intval($pending['days'] ?? 0);
    if ($days > 0) {
        filmworld_add_days($user_id, $days);
    }

    // Update payment record to success
    $wpdb->update($table, [
        'status'  => 'success',
        'ref_id'  => $result['ref_id'],
        'paid_at' => current_time('mysql'),
    ], ['authority' => $authority, 'user_id' => $user_id]);

    // Also save to user meta for backward compatibility
    $payments = get_user_meta($user_id, 'filmworld_payments', true);
    if (!is_array($payments)) {
        $payments = [];
    }
    $payments[] = [
        'plan'      => $plan_key,
        'plan_name' => $plan['name'],
        'amount'    => $pending['price'],
        'ref_id'    => $result['ref_id'],
        'authority' => $authority,
        'gateway'   => $gateway_id,
        'date'      => current_time('mysql'),
        'status'    => 'success',
    ];
    update_user_meta($user_id, 'filmworld_payments', $payments);

    wp_redirect(add_query_arg(['payment' => 'success', 'ref' => $result['ref_id']], home_url('/account/')));
    exit;
}
add_action('template_redirect', 'filmworld_handle_payment_callback');

/*
|--------------------------------------------------------------------------
| Admin Settings Page
|--------------------------------------------------------------------------
*/

function filmworld_payment_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings
    if (isset($_POST['filmworld_save_payment_settings']) && check_admin_referer('filmworld_payment_settings')) {

        // Save enabled gateways
        $enabled = $_POST['enabled_gateways'] ?? [];
        if (!is_array($enabled)) {
            $enabled = [];
        }
        update_option('filmworld_enabled_gateways', array_map('sanitize_text_field', $enabled));

        // Save Zarinpal settings
        update_option('filmworld_gw_zarinpal_merchant', sanitize_text_field($_POST['filmworld_gw_zarinpal_merchant'] ?? ''));
        update_option('filmworld_gw_zarinpal_sandbox', sanitize_text_field($_POST['filmworld_gw_zarinpal_sandbox'] ?? 'no'));

        // Save Zibal settings
        update_option('filmworld_gw_zibal_merchant', sanitize_text_field($_POST['filmworld_gw_zibal_merchant'] ?? ''));

        // Save IDPay settings
        update_option('filmworld_gw_idpay_api_key', sanitize_text_field($_POST['filmworld_gw_idpay_api_key'] ?? ''));
        update_option('filmworld_gw_idpay_sandbox', sanitize_text_field($_POST['filmworld_gw_idpay_sandbox'] ?? 'no'));

        // Save day packages
        $pkg_keys  = sanitize_text_field($_POST['pkg_keys'] ?? '');
        $pkg_keys  = $pkg_keys ? explode(',', $pkg_keys) : [];
        $packages  = [];
        foreach ($pkg_keys as $key) {
            $key = trim($key);
            if (empty($key)) continue;
            if (!empty($_POST['pkg_delete']) && in_array($key, $_POST['pkg_delete'])) continue;
            $packages[$key] = [
                'days'  => intval($_POST["pkg_{$key}_days"] ?? 0),
                'price' => intval($_POST["pkg_{$key}_price"] ?? 0),
            ];
        }
        // Keep only packages with valid days
        $packages = array_filter($packages, function($p) { return $p['days'] > 0; });
        update_option('filmworld_day_packages', $packages);

        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
    }

    // Handle add new package
    if (isset($_POST['filmworld_add_package']) && check_admin_referer('filmworld_payment_settings')) {
        $new_days  = intval($_POST['new_pkg_days'] ?? 0);
        $new_price = intval($_POST['new_pkg_price'] ?? 0);
        if ($new_days > 0 && $new_price > 0) {
            $packages = get_option('filmworld_day_packages', []);
            $packages[$new_days] = ['days' => $new_days, 'price' => $new_price];
            update_option('filmworld_day_packages', $packages);
            echo '<div class="notice notice-success is-dismissible"><p>بسته جدید اضافه شد.</p></div>';
        }
    }

    $enabled         = get_option('filmworld_enabled_gateways', ['zarinpal']);
    if (!is_array($enabled)) $enabled = ['zarinpal'];
    $merchant_value  = get_option('filmworld_gw_zarinpal_merchant', '');
    $sandbox_value   = get_option('filmworld_gw_zarinpal_sandbox', 'no');
    $zibal_merchant  = get_option('filmworld_gw_zibal_merchant', '');
    $idpay_api_key   = get_option('filmworld_gw_idpay_api_key', '');
    $idpay_sandbox   = get_option('filmworld_gw_idpay_sandbox', 'no');
    $packages        = filmworld_get_day_packages();
    ?>

    <div class="wrap">
        <h1>تنظیمات پرداخت FilmWorld</h1>

        <form method="post">
            <?php wp_nonce_field('filmworld_payment_settings'); ?>

            <!-- ====== Zarinpal ====== -->
            <h2 class="title">درگاه پرداخت زرین‌پال</h2>

            <div style="border:1px solid #ccd0d4;padding:15px 20px;border-radius:4px;margin-bottom:15px;background:#fff;max-width:600px;">
                <p style="margin-bottom:12px;">
                    <label style="font-weight:bold;">
                        <input type="checkbox" name="enabled_gateways[]" value="zarinpal" <?php checked(in_array('zarinpal', $enabled)); ?>>
                        فعال بودن این درگاه
                    </label>
                    <?php if (!empty($merchant_value)) : ?>
                        <span style="color:green;margin-right:10px;">&#10003; تنظیم شده</span>
                    <?php else : ?>
                        <span style="color:#999;margin-right:10px;">تنظیم نشده</span>
                    <?php endif; ?>
                </p>
                <table class="form-table">
                    <tr>
                        <th><label for="filmworld_gw_zarinpal_merchant">مرچنت کد</label></th>
                        <td>
                            <input type="text" name="filmworld_gw_zarinpal_merchant" id="filmworld_gw_zarinpal_merchant"
                                   value="<?php echo esc_attr($merchant_value); ?>" class="regular-text"
                                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                   dir="ltr" style="text-align:left;">
                            <p class="description">مرچنت کد دریافتی از پنل زرین‌پال</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="filmworld_gw_zarinpal_sandbox">حالت تست (Sandbox)</label></th>
                        <td>
                            <select name="filmworld_gw_zarinpal_sandbox" id="filmworld_gw_zarinpal_sandbox">
                                <option value="yes" <?php selected($sandbox_value, 'yes'); ?>>بله - حالت تست</option>
                                <option value="no" <?php selected($sandbox_value, 'no'); ?>>خیر - پرداخت واقعی</option>
                            </select>
                            <p class="description">در حالت تست تراکنش‌ها واقعی نیستند</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ====== Zibal ====== -->
            <h2 class="title">درگاه پرداخت زیبال</h2>

            <div style="border:1px solid #ccd0d4;padding:15px 20px;border-radius:4px;margin-bottom:15px;background:#fff;max-width:600px;">
                <p style="margin-bottom:12px;">
                    <label style="font-weight:bold;">
                        <input type="checkbox" name="enabled_gateways[]" value="zibal" <?php checked(in_array('zibal', $enabled)); ?>>
                        فعال بودن این درگاه
                    </label>
                    <?php if (!empty($zibal_merchant)) : ?>
                        <span style="color:green;margin-right:10px;">&#10003; تنظیم شده</span>
                    <?php else : ?>
                        <span style="color:#999;margin-right:10px;">تنظیم نشده</span>
                    <?php endif; ?>
                </p>
                <table class="form-table">
                    <tr>
                        <th><label for="filmworld_gw_zibal_merchant">مرچنت کد</label></th>
                        <td>
                            <input type="text" name="filmworld_gw_zibal_merchant" id="filmworld_gw_zibal_merchant"
                                   value="<?php echo esc_attr($zibal_merchant); ?>" class="regular-text"
                                   placeholder="مرچنت کد زیبال"
                                   dir="ltr" style="text-align:left;">
                            <p class="description">مرچنت کد دریافتی از پنل زیبال</p>
                        </td>
                    </tr>
                </table>
                <p class="description" style="color:#999;">زیبال حالت تست جداگانه‌ای ندارد — با مرچنت واقعی تست می‌شود.</p>
            </div>

            <!-- ====== IDPay ====== -->
            <h2 class="title">درگاه پرداخت آی‌دی‌پی (IDPay)</h2>

            <div style="border:1px solid #ccd0d4;padding:15px 20px;border-radius:4px;margin-bottom:15px;background:#fff;max-width:600px;">
                <p style="margin-bottom:12px;">
                    <label style="font-weight:bold;">
                        <input type="checkbox" name="enabled_gateways[]" value="idpay" <?php checked(in_array('idpay', $enabled)); ?>>
                        فعال بودن این درگاه
                    </label>
                    <?php if (!empty($idpay_api_key)) : ?>
                        <span style="color:green;margin-right:10px;">&#10003; تنظیم شده</span>
                    <?php else : ?>
                        <span style="color:#999;margin-right:10px;">تنظیم نشده</span>
                    <?php endif; ?>
                </p>
                <table class="form-table">
                    <tr>
                        <th><label for="filmworld_gw_idpay_api_key">API Key</label></th>
                        <td>
                            <input type="text" name="filmworld_gw_idpay_api_key" id="filmworld_gw_idpay_api_key"
                                   value="<?php echo esc_attr($idpay_api_key); ?>" class="regular-text"
                                   placeholder="کلید API آی‌دی‌پی"
                                   dir="ltr" style="text-align:left;">
                            <p class="description">کلید API دریافتی از پنل آی‌دی‌پی</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="filmworld_gw_idpay_sandbox">حالت تست (Sandbox)</label></th>
                        <td>
                            <select name="filmworld_gw_idpay_sandbox" id="filmworld_gw_idpay_sandbox">
                                <option value="yes" <?php selected($idpay_sandbox, 'yes'); ?>>بله - حالت تست</option>
                                <option value="no" <?php selected($idpay_sandbox, 'no'); ?>>خیر - پرداخت واقعی</option>
                            </select>
                            <p class="description">در حالت تست هدر X-SANDBOX ارسال شده و حساسیت آدرس سایت و IP برداشته می‌شود</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ====== Day Packages ====== -->
            <h2 class="title" style="margin-top:30px;">بسته‌های اشتراک (روز)</h2>
            <p class="description" style="margin-bottom:15px;">هر بسته تعداد روز و قیمت مشخصی دارد. کاربران هنگام خرید یکی را انتخاب می‌کنند.</p>

            <input type="hidden" name="pkg_keys" value="<?php echo esc_attr(implode(',', array_keys($packages))); ?>">

            <table class="widefat striped" style="max-width:600px;">
                <thead>
                    <tr>
                        <th>تعداد روز</th>
                        <th>قیمت (تومان)</th>
                        <th style="width:60px;">حذف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $key => $pkg) : ?>
                    <tr>
                        <td>
                            <input type="number" name="pkg_<?php echo esc_attr($key); ?>_days" value="<?php echo esc_attr($pkg['days']); ?>" min="1" style="width:100px;">
                        </td>
                        <td>
                            <input type="number" name="pkg_<?php echo esc_attr($key); ?>_price" value="<?php echo esc_attr($pkg['price']); ?>" min="0" style="width:150px;">
                        </td>
                        <td>
                            <label style="color:#d63638;cursor:pointer;">
                                <input type="checkbox" name="pkg_delete[]" value="<?php echo esc_attr($key); ?>"> حذف
                            </label>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="submit" style="margin-top:15px;">
                <button type="submit" name="filmworld_save_payment_settings" class="button button-primary">ذخیره تنظیمات</button>
            </p>
        </form>

        <!-- Add new package -->
        <hr style="margin:30px 0;">
        <h3>افزودن بسته جدید</h3>
        <form method="post" style="display:flex;gap:10px;align-items:flex-end;">
            <?php wp_nonce_field('filmworld_payment_settings'); ?>
            <div>
                <label><strong>تعداد روز:</strong></label><br>
                <input type="number" name="new_pkg_days" placeholder="مثلاً 60" min="1" class="regular-text" style="width:120px;">
            </div>
            <div>
                <label><strong>قیمت (تومان):</strong></label><br>
                <input type="number" name="new_pkg_price" placeholder="مثلاً 79000" min="0" class="regular-text" style="width:150px;">
            </div>
            <button type="submit" name="filmworld_add_package" class="button">افزودن</button>
        </form>
    </div>

    <?php
}

/*
|--------------------------------------------------------------------------
| Admin Menu: Payment Settings
|--------------------------------------------------------------------------
*/

// Admin submenu is registered in admin-menu.php — no duplicate here

/*
|--------------------------------------------------------------------------
| AJAX: Get Enabled Gateways (for frontend)
|--------------------------------------------------------------------------
*/

function filmworld_ajax_get_enabled_gateways() {
    $enabled = filmworld_get_enabled_gateways();
    $list    = [];

    foreach ($enabled as $id => $gw) {
        $list[] = [
            'id'   => $id,
            'name' => $gw->get_name(),
        ];
    }

    wp_send_json_success($list);
}
add_action('wp_ajax_filmworld_get_enabled_gateways', 'filmworld_ajax_get_enabled_gateways');