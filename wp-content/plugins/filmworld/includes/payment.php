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
| Membership Plans
|--------------------------------------------------------------------------
*/

function filmworld_get_plans() {
    $plans = get_option('filmworld_plans', []);

    if (empty($plans)) {
        $plans = [
            'monthly' => [
                'name'        => 'یک ماهه',
                'price'       => 49000,
                'days'        => 30,
                'description' => 'دسترسی ۳۰ روزه به تمام فیلم‌ها و سریال‌ها',
            ],
            'quarterly' => [
                'name'        => 'سه ماهه',
                'price'       => 119000,
                'days'        => 90,
                'description' => 'دسترسی ۹۰ روزه به تمام فیلم‌ها و سریال‌ها',
            ],
            'yearly' => [
                'name'        => 'یک ساله',
                'price'       => 389000,
                'days'        => 365,
                'description' => 'دسترسی ۳۶۵ روزه به تمام فیلم‌ها و سریال‌ها',
            ],
        ];
    }

    return $plans;
}

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
            [
                'id'          => 'filmworld_gw_zibal_sandbox',
                'key'         => 'sandbox',
                'label'       => 'حالت تست',
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
            return new WP_Error('no_merchant', 'مرچنت کد زیبال تنظیم نشده است.');
        }

        $data = [
            'merchant'    => $merchant,
            'amount'      => (int) $amount,
            'callbackUrl' => $callback_url,
            'description' => $description,
            'orderId'     => time() . '-' . $user_id,
        ];

        $response = wp_remote_post('https://gateway.zibal.ir/v1/request', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($data),
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
        $msg = $body['message'] ?? 'خطا در ایجاد تراکنش زیبال (کد: ' . $code . ')';
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
        $msg = $body['message'] ?? 'خطا در تأیید پرداخت زیبال (کد: ' . $code . ')';
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
                'description' => 'در حالت تست تراکنش‌ها واقعی نیستند',
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

        $msg = $body['error_message'] ?? 'خطا در ایجاد تراکنش آی‌دی‌پی';
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

        $msg = $body['error_message'] ?? 'خطا در تأیید پرداخت آی‌دی‌پی';
        $code = $body['error_code'] ?? 'نامشخص';
        return new WP_Error('verify_failed', $msg . ' (کد: ' . $code . ')');
    }
}

/*
|--------------------------------------------------------------------------
| Nextpay Gateway
|--------------------------------------------------------------------------
*/

class FilmWorld_Gateway_Nextpay extends FilmWorld_Gateway {

    public function get_id() {
        return 'nextpay';
    }

    public function get_name() {
        return 'نکست‌پی (Nextpay)';
    }

    public function get_payment_url($token) {
        return 'https://nextpay.org/nx/gateway/payment/' . $token;
    }

    public function get_settings_fields() {
        return [
            [
                'id'          => 'filmworld_gw_nextpay_api_key',
                'key'         => 'api_key',
                'label'       => 'API Key',
                'type'        => 'text',
                'placeholder' => 'کلید API نکست‌پی',
                'description' => 'کلید API دریافتی از پنل نکست‌پی',
                'required'    => true,
                'dir'         => 'ltr',
            ],
            [
                'id'          => 'filmworld_gw_nextpay_sandbox',
                'key'         => 'sandbox',
                'label'       => 'حالت تست',
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
        $api_key = $this->get_setting('api_key');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'کلید API نکست‌پی تنظیم نشده است.');
        }

        $data = [
            'api_key'      => $api_key,
            'amount'       => (int) $amount,
            'callback_uri' => $callback_url,
            'order_id'     => time() . '-' . $user_id,
            'user_id'      => $user_id,
            'description'  => $description,
        ];

        $sandbox = $this->get_setting('sandbox') === 'yes';
        if ($sandbox) {
            $data['sandbox'] = 1;
        }

        $response = wp_remote_post('https://nextpay.org/nx/gateway/token', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($data),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('connection_error', 'خطا در اتصال به نکست‌پی.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['code']) && $body['code'] === -1 && !empty($body['trans_id'])) {
            return [
                'authority' => $body['trans_id'],
                'url'       => $this->get_payment_url($body['trans_id']),
            ];
        }

        $msg = $body['message'] ?? 'خطا در ایجاد تراکنش نکست‌پی';
        return new WP_Error('payment_failed', $msg);
    }

    public function verify_payment($amount, $authority) {
        $api_key = $this->get_setting('api_key');

        $data = [
            'api_key'  => $api_key,
            'amount'   => (int) $amount,
            'trans_id' => $authority,
        ];

        $sandbox = $this->get_setting('sandbox') === 'yes';
        if ($sandbox) {
            $data['sandbox'] = 1;
        }

        $response = wp_remote_post('https://nextpay.org/nx/gateway/verify', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($data),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('verify_error', 'خطا در اتصال به نکست‌پی.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['code']) && $body['code'] === 0) {
            return [
                'success'   => true,
                'ref_id'    => (string) ($body['transaction_id'] ?? $authority),
                'authority' => $authority,
                'amount'    => $amount,
            ];
        }

        $msg = $body['message'] ?? 'خطا در تأیید پرداخت نکست‌پی';
        return new WP_Error('verify_failed', $msg);
    }
}

/*
|--------------------------------------------------------------------------
| Pay.ir Gateway
|--------------------------------------------------------------------------
*/

class FilmWorld_Gateway_Payir extends FilmWorld_Gateway {

    public function get_id() {
        return 'payir';
    }

    public function get_name() {
        return 'پی‌آی‌آر (Pay.ir)';
    }

    public function get_payment_url($token) {
        return 'https://pay.ir/pg/' . $token;
    }

    public function get_settings_fields() {
        return [
            [
                'id'          => 'filmworld_gw_payir_api_key',
                'key'         => 'api_key',
                'label'       => 'API Key',
                'type'        => 'text',
                'placeholder' => 'کلید API پی‌آی‌آر',
                'description' => 'کلید API دریافتی از پنل پی‌آی‌آر',
                'required'    => true,
                'dir'         => 'ltr',
            ],
            [
                'id'          => 'filmworld_gw_payir_sandbox',
                'key'         => 'sandbox',
                'label'       => 'حالت تست',
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
        $api_key = $this->get_setting('api_key');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'کلید API پی‌آی‌آر تنظیم نشده است.');
        }

        $response = wp_remote_post('https://pay.ir/pg/send', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'amount'      => (int) $amount,
                'description' => $description,
                'callback'    => $callback_url,
                'order_id'    => time() . '-' . $user_id,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('connection_error', 'خطا در اتصال به پی‌آی‌آر.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['token']) && empty($body['error'])) {
            return [
                'authority' => $body['token'],
                'url'       => $this->get_payment_url($body['token']),
            ];
        }

        $msg = $body['errorMessage'] ?? 'خطا در ایجاد تراکنش پی‌آی‌آر';
        return new WP_Error('payment_failed', $msg);
    }

    public function verify_payment($amount, $authority) {
        $api_key = $this->get_setting('api_key');

        $response = wp_remote_post('https://pay.ir/pg/verify', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'api_key' => $api_key,
                'token'   => $authority,
            ]),
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('verify_error', 'خطا در اتصال به پی‌آی‌آر.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['status']) && $body['status'] === 1) {
            return [
                'success'   => true,
                'ref_id'    => (string) ($body['transId'] ?? $authority),
                'authority' => $authority,
                'amount'    => $amount,
            ];
        }

        $msg = $body['errorMessage'] ?? 'خطا در تأیید پرداخت پی‌آی‌آر';
        return new WP_Error('verify_failed', $msg);
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
            'nextpay'  => new FilmWorld_Gateway_Nextpay(),
            'payir'    => new FilmWorld_Gateway_Payir(),
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
        if (isset($all[$id])) {
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

    // Validate plan
    $plans = filmworld_get_plans();
    if (!isset($plans[$plan_key])) {
        wp_send_json_error(['message' => 'پلن انتخاب شده معتبر نیست.']);
    }

    // Validate gateway
    $gateway = filmworld_get_gateway($gateway_id);
    if (!$gateway) {
        wp_send_json_error(['message' => 'درگاه پرداخت انتخاب شده معتبر نیست.']);
    }

    if (!$gateway->is_configured()) {
        wp_send_json_error(['message' => 'درگاه "' . $gateway->get_name() . '" به درستی تنظیم نشده است.']);
    }

    $plan    = $plans[$plan_key];
    $user_id = get_current_user_id();

    // Build callback URL with gateway identifier
    $callback = add_query_arg([
        'filmworld_payment' => '1',
        'plan'              => $plan_key,
        'gateway'           => $gateway_id,
    ], home_url('/account/'));

    $result = $gateway->create_payment(
        $plan['price'],
        'عضویت ' . $plan['name'] . ' - کاربر #' . $user_id,
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
        'price'     => $plan['price'],
        'time'      => time(),
    ]);

    // Create payment record in custom table
    global $wpdb;
    $table = $wpdb->prefix . 'filmworld_payments';
    $wpdb->insert($table, [
        'user_id'    => $user_id,
        'gateway'    => $gateway_id,
        'plan_key'   => $plan_key,
        'plan_name'  => $plan['name'],
        'amount'     => $plan['price'],
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

    // Detect authority from different gateway params
    $authority = sanitize_text_field(
        $_GET['Authority']  // Zarinpal
        ?? $_GET['trackId'] // Zibal
        ?? $_GET['id']      // IDPay
        ?? $_GET['token']   // Pay.ir
        ?? $_GET['trans_id'] // Nextpay
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
        case 'payir':
            if (($_GET['status'] ?? '') !== '1') $is_cancelled = true;
            break;
        case 'nextpay':
            if (empty($_GET['trans_id'])) $is_cancelled = true;
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

    // Activate membership
    $expire = time() + ($plan['days'] * 86400);
    update_user_meta($user_id, 'filmworld_plan', $plan_key);
    update_user_meta($user_id, 'filmworld_expire', $expire);

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
| Admin Settings Page (Multi-Gateway)
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

        // Save each gateway's settings
        $all_gateways = filmworld_get_all_gateways();
        foreach ($all_gateways as $gw) {
            foreach ($gw->get_settings_fields() as $field) {
                $value = sanitize_text_field($_POST[$field['id']] ?? '');
                update_option($field['id'], $value);
            }
        }

        // Save plans
        $plans     = [];
        $plan_keys = ['monthly', 'quarterly', 'yearly'];
        foreach ($plan_keys as $key) {
            $plans[$key] = [
                'name'        => sanitize_text_field($_POST["plan_{$key}_name"] ?? ''),
                'price'       => intval($_POST["plan_{$key}_price"] ?? 0),
                'days'        => intval($_POST["plan_{$key}_days"] ?? 0),
                'description' => sanitize_textarea_field($_POST["plan_{$key}_desc"] ?? ''),
            ];
        }
        update_option('filmworld_plans', $plans);

        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
    }

    $all_gateways = filmworld_get_all_gateways();
    $enabled      = get_option('filmworld_enabled_gateways', ['zarinpal']);
    $plans        = filmworld_get_plans();
    $plan_keys    = ['monthly', 'quarterly', 'yearly'];
    $plan_labels  = ['monthly' => 'یک ماهه', 'quarterly' => 'سه ماهه', 'yearly' => 'یک ساله'];
    ?>

    <div class="wrap">
        <h1>تنظیمات پرداخت FilmWorld</h1>

        <form method="post">
            <?php wp_nonce_field('filmworld_payment_settings'); ?>

            <!-- ====== Gateway Selection ====== -->
            <h2 class="title">درگاه‌های پرداخت</h2>
            <p class="description" style="margin-bottom:15px;">
                درگاه‌های مورد نظر خود را انتخاب و تنظیم کنید. کاربران هنگام خرید اشتراک می‌توانند درگاه دلخواه را انتخاب نمایند.
            </p>

            <table class="widefat striped" style="max-width:800px;">
                <thead>
                    <tr>
                        <th style="width:40px;">فعال</th>
                        <th>درگاه پرداخت</th>
                        <th style="width:120px;">وضعیت تنظیمات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_gateways as $id => $gw) :
                        $is_enabled    = in_array($id, $enabled);
                        $is_configured = $gw->is_configured();
                    ?>
                    <tr>
                        <td style="text-align:center;">
                            <input type="checkbox" name="enabled_gateways[]" value="<?php echo esc_attr($id); ?>" <?php checked($is_enabled); ?>>
                        </td>
                        <td>
                            <strong><?php echo esc_html($gw->get_name()); ?></strong>
                            <code style="color:#666;margin-right:5px;">(<?php echo esc_html($id); ?>)</code>
                        </td>
                        <td>
                            <?php if ($is_configured) : ?>
                                <span style="color:green;font-weight:bold;">&#10003; تنظیم شده</span>
                            <?php else : ?>
                                <span style="color:#999;">تنظیم نشده</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ====== Per-Gateway Settings ====== -->
            <h2 class="title" style="margin-top:30px;">تنظیمات هر درگاه</h2>

            <?php foreach ($all_gateways as $id => $gw) : ?>
            <div id="gateway-box-<?php echo esc_attr($id); ?>" style="border:1px solid #ccd0d4;padding:15px 20px;border-radius:4px;margin-bottom:15px;background:#fff;">
                <h3 style="margin-top:0;"><?php echo esc_html($gw->get_name()); ?></h3>
                <table class="form-table">
                    <?php foreach ($gw->get_settings_fields() as $field) :
                        $value = get_option($field['id'], '');
                    ?>
                    <tr>
                        <th><label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label></th>
                        <td>
                            <?php if ($field['type'] === 'select') : ?>
                            <select name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>">
                                <?php foreach ($field['options'] as $opt_val => $opt_label) : ?>
                                <option value="<?php echo esc_attr($opt_val); ?>" <?php selected($value, $opt_val); ?>>
                                    <?php echo esc_html($opt_label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php else : ?>
                            <input type="text" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                                   value="<?php echo esc_attr($value); ?>" class="regular-text"
                                   placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                   <?php echo !empty($field['dir']) ? 'dir="' . esc_attr($field['dir']) . '" style="text-align:left;"' : ''; ?>>
                            <?php endif; ?>
                            <?php if (!empty($field['description'])) : ?>
                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endforeach; ?>

            <!-- ====== Plan Configuration ====== -->
            <h2 class="title" style="margin-top:30px;">پلن‌های عضویت</h2>

            <?php foreach ($plan_keys as $key) : $p = $plans[$key] ?? []; ?>
            <div style="border:1px solid #ccd0d4;padding:15px 20px;border-radius:4px;margin-bottom:15px;background:#fff;">
                <h3 style="margin-top:0;"><?php echo esc_html($plan_labels[$key]); ?></h3>
                <table class="form-table">
                    <tr>
                        <th>نام پلن</th>
                        <td><input type="text" name="plan_<?php echo $key; ?>_name" value="<?php echo esc_attr($p['name'] ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>قیمت (تومان)</th>
                        <td><input type="number" name="plan_<?php echo $key; ?>_price" value="<?php echo esc_attr($p['price'] ?? 0); ?>" min="0"></td>
                    </tr>
                    <tr>
                        <th>مدت (روز)</th>
                        <td><input type="number" name="plan_<?php echo $key; ?>_days" value="<?php echo esc_attr($p['days'] ?? 0); ?>" min="1"></td>
                    </tr>
                    <tr>
                        <th>توضیحات</th>
                        <td><textarea name="plan_<?php echo $key; ?>_desc" rows="2" class="large-text"><?php echo esc_textarea($p['description'] ?? ''); ?></textarea></td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>

            <p class="submit">
                <button type="submit" name="filmworld_save_payment_settings" class="button button-primary">ذخیره تنظیمات</button>
            </p>
        </form>
    </div>

    <?php
}

/*
|--------------------------------------------------------------------------
| Admin Menu: Payment Settings
|--------------------------------------------------------------------------
*/

function filmworld_payment_admin_menu() {
    add_submenu_page(
        'filmworld-taxonomies',
        'تنظیمات پرداخت',
        'تنظیمات پرداخت',
        'manage_options',
        'filmworld-payment-settings',
        'filmworld_payment_settings_page'
    );
}
add_action('admin_menu', 'filmworld_payment_admin_menu');

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