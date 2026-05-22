<?php

namespace addons\server_market\model;

use think\Db;

class ServerMarketModel
{
    const STATUS_PENDING = 0;
    const STATUS_LISTED = 1;
    const STATUS_LOCKED = 2;
    const STATUS_SOLD = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_REJECTED = 5;
    const STATUS_OFFLINE = 6;
    const IP_STATUS_NORMAL = 1;
    const IP_STATUS_ABNORMAL = 2;

    protected $settingsCache;
    protected $expiredChecked = false;

    public function install()
    {
        $prefix = $this->tablePrefix();

        Db::execute("CREATE TABLE IF NOT EXISTS `{$prefix}server_market_listing` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `listing_no` varchar(40) NOT NULL DEFAULT '',
            `host_id` int(10) unsigned NOT NULL DEFAULT '0',
            `seller_uid` int(10) unsigned NOT NULL DEFAULT '0',
            `buyer_uid` int(10) unsigned NOT NULL DEFAULT '0',
            `product_id` int(10) unsigned NOT NULL DEFAULT '0',
            `title` varchar(120) NOT NULL DEFAULT '',
            `description` text,
            `ip_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'IP状态：1正常 2异常',
            `price` decimal(12,2) NOT NULL DEFAULT '0.00',
            `buyer_price` decimal(12,2) NOT NULL DEFAULT '0.00',
            `fee_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
            `fee_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `seller_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `currency_id` int(10) unsigned NOT NULL DEFAULT '0',
            `currency_code` varchar(10) NOT NULL DEFAULT '',
            `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
            `expire_time` int(10) unsigned NOT NULL DEFAULT '0',
            `review_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
            `review_time` int(10) unsigned NOT NULL DEFAULT '0',
            `admin_note` varchar(255) NOT NULL DEFAULT '',
            `reject_reason` varchar(255) NOT NULL DEFAULT '',
            `host_snapshot` text,
            `create_time` int(10) unsigned NOT NULL DEFAULT '0',
            `update_time` int(10) unsigned NOT NULL DEFAULT '0',
            `sold_time` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `listing_no` (`listing_no`),
            KEY `host_status` (`host_id`,`status`),
            KEY `seller_status` (`seller_uid`,`status`),
            KEY `buyer_uid` (`buyer_uid`),
            KEY `status_time` (`status`,`create_time`),
            KEY `status_expire` (`status`,`expire_time`),
            KEY `status_product` (`status`,`product_id`,`create_time`),
            KEY `status_price` (`status`,`price`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='服务器交易市场挂牌';");

        Db::execute("CREATE TABLE IF NOT EXISTS `{$prefix}server_market_trade` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `trade_no` varchar(40) NOT NULL DEFAULT '',
            `listing_id` int(10) unsigned NOT NULL DEFAULT '0',
            `host_id` int(10) unsigned NOT NULL DEFAULT '0',
            `seller_uid` int(10) unsigned NOT NULL DEFAULT '0',
            `buyer_uid` int(10) unsigned NOT NULL DEFAULT '0',
            `price` decimal(12,2) NOT NULL DEFAULT '0.00',
            `buyer_pay_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `seller_credit_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `fee_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `fee_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
            `seller_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
            `seller_currency_code` varchar(10) NOT NULL DEFAULT '',
            `buyer_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
            `buyer_currency_code` varchar(10) NOT NULL DEFAULT '',
            `old_order_id` int(10) unsigned NOT NULL DEFAULT '0',
            `old_product_id` int(10) unsigned NOT NULL DEFAULT '0',
            `buyer_account_id` int(10) unsigned NOT NULL DEFAULT '0',
            `seller_account_id` int(10) unsigned NOT NULL DEFAULT '0',
            `host_snapshot` text,
            `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
            `create_time` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `trade_no` (`trade_no`),
            KEY `listing_id` (`listing_id`),
            KEY `host_id` (`host_id`),
            KEY `seller_uid` (`seller_uid`),
            KEY `buyer_uid` (`buyer_uid`),
            KEY `create_time` (`create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='服务器交易市场成交记录';");

        Db::execute("CREATE TABLE IF NOT EXISTS `{$prefix}server_market_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `listing_id` int(10) unsigned NOT NULL DEFAULT '0',
            `trade_id` int(10) unsigned NOT NULL DEFAULT '0',
            `host_id` int(10) unsigned NOT NULL DEFAULT '0',
            `uid` int(10) unsigned NOT NULL DEFAULT '0',
            `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
            `action` varchar(40) NOT NULL DEFAULT '',
            `description` varchar(255) NOT NULL DEFAULT '',
            `data` text,
            `ip` varchar(64) NOT NULL DEFAULT '',
            `create_time` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `listing_id` (`listing_id`),
            KEY `trade_id` (`trade_id`),
            KEY `host_id` (`host_id`),
            KEY `uid` (`uid`),
            KEY `admin_id` (`admin_id`),
            KEY `action` (`action`),
            KEY `create_time` (`create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='服务器交易市场审计日志';");

        Db::execute("CREATE TABLE IF NOT EXISTS `{$prefix}server_market_setting` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(80) NOT NULL DEFAULT '',
            `value` text,
            `update_time` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='服务器交易市场设置';");

        $this->ensureListingColumns();
        $this->ensureTradeColumns();
        $this->ensureLogColumns();
        $this->ensureSettingColumns();
        $this->ensureDefaultSettings();
        $this->repairPersistentData();
        $this->recoverActiveListingsAfterInstall();
        $this->ensureUniqueIndex($prefix . 'server_market_listing', 'listing_no', 'CREATE UNIQUE INDEX `listing_no` ON `' . $prefix . 'server_market_listing` (`listing_no`)');
        $this->ensureIndex($prefix . 'server_market_listing', 'status_expire', 'CREATE INDEX `status_expire` ON `' . $prefix . 'server_market_listing` (`status`,`expire_time`)');
        $this->ensureIndex($prefix . 'server_market_listing', 'status_product', 'CREATE INDEX `status_product` ON `' . $prefix . 'server_market_listing` (`status`,`product_id`,`create_time`)');
        $this->ensureIndex($prefix . 'server_market_listing', 'status_price', 'CREATE INDEX `status_price` ON `' . $prefix . 'server_market_listing` (`status`,`price`)');
        $this->ensureUniqueIndex($prefix . 'server_market_trade', 'trade_no', 'CREATE UNIQUE INDEX `trade_no` ON `' . $prefix . 'server_market_trade` (`trade_no`)');
        $this->ensureIndex($prefix . 'server_market_trade', 'create_time', 'CREATE INDEX `create_time` ON `' . $prefix . 'server_market_trade` (`create_time`)');
        $this->ensureIndex($prefix . 'server_market_trade', 'product_status_time', 'CREATE INDEX `product_status_time` ON `' . $prefix . 'server_market_trade` (`old_product_id`,`status`,`create_time`)');
        $this->ensureIndex($prefix . 'server_market_trade', 'product_currency_status_time', 'CREATE INDEX `product_currency_status_time` ON `' . $prefix . 'server_market_trade` (`old_product_id`,`seller_currency_code`,`status`,`create_time`)');
        $this->ensureIndex($prefix . 'server_market_log', 'action', 'CREATE INDEX `action` ON `' . $prefix . 'server_market_log` (`action`)');
        $this->ensureIndex($prefix . 'server_market_log', 'create_time', 'CREATE INDEX `create_time` ON `' . $prefix . 'server_market_log` (`create_time`)');
        return true;
    }

    public function uninstall($dropData = false)
    {
        if (!$dropData) {
            return true;
        }
        $prefix = $this->tablePrefix();
        Db::execute("DROP TABLE IF EXISTS `{$prefix}server_market_log`");
        Db::execute("DROP TABLE IF EXISTS `{$prefix}server_market_trade`");
        Db::execute("DROP TABLE IF EXISTS `{$prefix}server_market_listing`");
        Db::execute("DROP TABLE IF EXISTS `{$prefix}server_market_setting`");
        return true;
    }

    public function dashboard()
    {
        $this->expireOldListings();
        return [
            'pending' => Db::name('server_market_listing')->where('status', self::STATUS_PENDING)->count(),
            'listed' => Db::name('server_market_listing')->where('status', self::STATUS_LISTED)->count(),
            'sold' => Db::name('server_market_listing')->where('status', self::STATUS_SOLD)->count(),
            'offline' => Db::name('server_market_listing')->whereIn('status', [self::STATUS_CANCELLED, self::STATUS_REJECTED, self::STATUS_OFFLINE])->count(),
            'turnover' => sprintf('%.2f', Db::name('server_market_trade')->where('status', 1)->sum('price')),
        ];
    }

    public function settings()
    {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }
        $this->ensureDefaultSettings();
        $rows = Db::name('server_market_setting')->column('value', 'name');
        $defaults = $this->defaultSettings();
        $this->settingsCache = array_merge($defaults, $rows ?: []);
        $this->settingsCache['allow_statuses'] = trim(strip_tags($this->settingsCache['allow_statuses'] ?? 'Active,Suspended'));
        $this->settingsCache['trade_cooldown_minutes'] = (string)max(0, min(525600, intval($this->settingsCache['trade_cooldown_minutes'] ?? 0)));
        $this->settingsCache['buyer_notice'] = mb_substr(trim(strip_tags($this->settingsCache['buyer_notice'] ?? '')), 0, 500, 'UTF-8');
        $this->settingsCache['buyer_notice_text'] = $this->htmlText($this->settingsCache['buyer_notice']);
        return $this->settingsCache;
    }

    public function saveSettings(array $param, $adminId = 0)
    {
        $settings = [
            'enabled' => empty($param['enabled']) ? '0' : '1',
            'auto_review' => empty($param['auto_review']) ? '0' : '1',
            'fee_percent' => sprintf('%.2f', max(0, min(100, floatval($param['fee_percent'] ?? 0)))),
            'min_price' => sprintf('%.2f', max(0, floatval($param['min_price'] ?? 0))),
            'max_price' => sprintf('%.2f', max(0, floatval($param['max_price'] ?? 0))),
            'expire_days' => (string)max(0, intval($param['expire_days'] ?? 0)),
            'trade_cooldown_minutes' => (string)max(0, min(525600, intval($param['trade_cooldown_minutes'] ?? 0))),
            'allow_statuses' => trim(strip_tags($param['allow_statuses'] ?? 'Active,Suspended')),
            'buyer_notice' => mb_substr(trim(strip_tags($param['buyer_notice'] ?? '')), 0, 500, 'UTF-8'),
        ];
        if ($settings['allow_statuses'] === '') {
            $settings['allow_statuses'] = 'Active,Suspended';
        }

        if (floatval($settings['max_price']) > 0 && floatval($settings['max_price']) < floatval($settings['min_price'])) {
            return ['status' => 400, 'msg' => '最高价格不能低于最低价格'];
        }

        foreach ($settings as $name => $value) {
            $this->setSetting($name, $value);
        }
        $this->settingsCache = null;
        $this->writeLog(0, 0, 0, 0, $adminId, 'setting', '更新交易市场设置', $settings);
        return ['status' => 200, 'msg' => '保存成功'];
    }

    public function publicListings(array $filter = [], $page = 1, $limit = 12)
    {
        $this->expireOldListings();
        $query = $this->listingQuery()
            ->where('l.status', self::STATUS_LISTED)
            ->where(function ($q) {
                $q->where('l.expire_time', 0)->whereOr('l.expire_time', '>', time());
            });

        $this->applyListingFilter($query, $filter, false);
        $countQuery = clone $query;
        $count = $countQuery->count();
        $list = $query->order('l.id', 'desc')->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item = $this->appendListingDisplay($item);
        }
        return ['list' => $list, 'count' => $count];
    }

    public function publicProductOptions($selectedProductId = 0, $limit = 120)
    {
        $this->expireOldListings();
        $selectedProductId = max(0, intval($selectedProductId));
        $limit = min(300, max(20, intval($limit)));
        $baseQuery = Db::name('server_market_listing')->alias('l')
            ->field('l.product_id,MAX(p.name) AS product_name,COUNT(*) AS listing_count')
            ->leftJoin('products p', 'p.id=l.product_id')
            ->where('l.status', self::STATUS_LISTED)
            ->where('l.product_id', '>', 0)
            ->where(function ($q) {
                $q->where('l.expire_time', 0)->whereOr('l.expire_time', '>', time());
            })
            ->group('l.product_id');
        $rows = $baseQuery
            ->order('listing_count', 'desc')
            ->limit($limit + 1)
            ->select()
            ->toArray();
        $limited = count($rows) > $limit;
        if ($limited) {
            $rows = array_slice($rows, 0, $limit);
        }
        if ($selectedProductId > 0) {
            $selectedExists = false;
            foreach ($rows as $row) {
                if (intval($row['product_id'] ?? 0) === $selectedProductId) {
                    $selectedExists = true;
                    break;
                }
            }
            if (!$selectedExists) {
                $selected = Db::name('server_market_listing')->alias('l')
                    ->field('l.product_id,MAX(p.name) AS product_name,COUNT(*) AS listing_count')
                    ->leftJoin('products p', 'p.id=l.product_id')
                    ->where('l.status', self::STATUS_LISTED)
                    ->where('l.product_id', $selectedProductId)
                    ->where(function ($q) {
                        $q->where('l.expire_time', 0)->whereOr('l.expire_time', '>', time());
                    })
                    ->group('l.product_id')
                    ->find();
                if (!empty($selected)) {
                    $rows[] = $selected;
                }
            }
        }

        $options = [];
        foreach ($rows as $row) {
            $id = intval($row['product_id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $name = trim((string)($row['product_name'] ?? ''));
            $label = $name !== '' ? $name : '产品 #' . $id;
            $options[] = [
                'id' => $id,
                'name' => $label,
                'name_text' => $this->htmlText($label),
                'listing_count' => intval($row['listing_count'] ?? 0),
            ];
        }
        usort($options, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });
        return ['list' => $options, 'limited' => $limited, 'limit' => $limit];
    }

    public function adminListings(array $filter = [], $page = 1, $limit = 20)
    {
        $this->expireOldListings();
        $query = $this->listingQuery();
        $this->applyListingFilter($query, $filter, true);
        if (isset($filter['status']) && $filter['status'] !== '') {
            $query->where('l.status', intval($filter['status']));
        }
        $countQuery = clone $query;
        $count = $countQuery->count();
        $list = $query->order('l.id', 'desc')->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item = $this->appendListingDisplay($item);
        }
        return ['list' => $list, 'count' => $count];
    }

    public function trades(array $filter = [], $page = 1, $limit = 20)
    {
        $query = Db::name('server_market_trade')->alias('t')
            ->field('t.*,h.domain,p.name product_name,s.username seller_name,b.username buyer_name')
            ->leftJoin('host h', 'h.id=t.host_id')
            ->leftJoin('products p', 'p.id=t.old_product_id')
            ->leftJoin('clients s', 's.id=t.seller_uid')
            ->leftJoin('clients b', 'b.id=t.buyer_uid');

        if (!empty($filter['uid'])) {
            $uid = intval($filter['uid']);
            $query->where(function ($q) use ($uid) {
                $q->where('t.seller_uid', $uid)->whereOr('t.buyer_uid', $uid);
            });
        }
        if (!empty($filter['keyword'])) {
            $keyword = trim($filter['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('t.trade_no', 'like', "%{$keyword}%")
                    ->whereOr('h.domain', 'like', "%{$keyword}%")
                    ->whereOr('p.name', 'like', "%{$keyword}%");
            });
        }

        $countQuery = clone $query;
        $count = $countQuery->count();
        $list = $query->order('t.id', 'desc')->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['trade_no_text'] = $this->htmlText($item['trade_no'] ?? '');
            $item['price_text'] = $this->moneyDisplayText($item['price'], $item['seller_currency_code']);
            $item['buyer_pay_text'] = $this->moneyDisplayText($item['buyer_pay_amount'], $item['buyer_currency_code']);
            $item['seller_credit_text'] = $this->moneyDisplayText($item['seller_credit_amount'], $item['seller_currency_code']);
            $item['fee_text'] = $this->moneyDisplayText($item['fee_amount'], $item['seller_currency_code']);
            $item['fee_rate_text'] = number_format(floatval($item['fee_rate'] ?? 0), 2);
            $item['seller_currency_code_text'] = $this->htmlText($item['seller_currency_code'] ?? '');
            $item['product_name_text'] = $this->htmlText($item['product_name'] ?? '');
            $item['domain_text'] = $this->htmlText($item['domain'] ?? '');
            $item['seller_name_text'] = $this->htmlText($item['seller_name'] ?? '');
            $item['buyer_name_text'] = $this->htmlText($item['buyer_name'] ?? '');
        }
        return ['list' => $list, 'count' => $count];
    }

    public function productPriceHistory($productId, $currencyCode = '', $limit = 12)
    {
        $productId = intval($productId);
        $limit = min(30, max(1, intval($limit)));
        if ($productId <= 0) {
            return $this->emptyPriceHistory($currencyCode);
        }

        $query = Db::name('server_market_trade')
            ->where('old_product_id', $productId)
            ->where('status', 1);
        if ($currencyCode !== '') {
            $query->where('seller_currency_code', $currencyCode);
        }

        $summary = (clone $query)
            ->field('COUNT(*) AS trade_count,AVG(price) AS avg_price,MIN(price) AS min_price,MAX(price) AS max_price')
            ->find() ?: [];
        $rows = (clone $query)
            ->field('id,trade_no,price,seller_currency_code,create_time')
            ->order('create_time', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
        $rows = array_reverse($rows);

        $points = [];
        foreach ($rows as $row) {
            $code = $row['seller_currency_code'] ?: $currencyCode;
            $points[] = [
                'date' => !empty($row['create_time']) ? date('m-d', intval($row['create_time'])) : '-',
                'time' => !empty($row['create_time']) ? date('Y-m-d H:i', intval($row['create_time'])) : '-',
                'price' => round(floatval($row['price']), 2),
                'price_text' => $this->moneyText($row['price'], $code),
            ];
        }

        $latest = !empty($rows) ? $rows[count($rows) - 1] : [];
        return [
            'count' => intval($summary['trade_count'] ?? 0),
            'avg_value' => round(floatval($summary['avg_price'] ?? 0), 2),
            'min_value' => round(floatval($summary['min_price'] ?? 0), 2),
            'max_value' => round(floatval($summary['max_price'] ?? 0), 2),
            'avg_text' => $this->moneyDisplayText(floatval($summary['avg_price'] ?? 0), $currencyCode),
            'avg_text_attr' => $this->moneyDisplayText(floatval($summary['avg_price'] ?? 0), $currencyCode),
            'min_text' => $this->moneyDisplayText(floatval($summary['min_price'] ?? 0), $currencyCode),
            'max_text' => $this->moneyDisplayText(floatval($summary['max_price'] ?? 0), $currencyCode),
            'latest_text' => !empty($latest) ? $this->moneyDisplayText($latest['price'], $latest['seller_currency_code'] ?: $currencyCode) : '-',
            'chart_json' => $this->htmlText(json_encode($points, JSON_UNESCAPED_UNICODE)),
            'points' => $points,
        ];
    }

    public function logs(array $filter = [], $page = 1, $limit = 20)
    {
        $query = Db::name('server_market_log')->alias('a')
            ->field('a.*,c.username,u.user_login')
            ->leftJoin('clients c', 'c.id=a.uid')
            ->leftJoin('user u', 'u.id=a.admin_id');

        $this->applyLogFilter($query, $filter);

        $countQuery = clone $query;
        $count = $countQuery->count();
        $list = $query->order('a.id', 'desc')->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['action_text'] = $this->htmlText($item['action'] ?? '');
            $item['description_text'] = $this->htmlText($item['description'] ?? '');
            $item['ip_text'] = $this->htmlText($item['ip'] ?? '');
            $item['username_text'] = $this->htmlText($item['username'] ?? '');
            $item['user_login_text'] = $this->htmlText($item['user_login'] ?? '');
        }
        return ['list' => $list, 'count' => $count];
    }

    public function deleteLog($id, $adminId = 0)
    {
        $id = intval($id);
        if ($id <= 0) {
            return ['status' => 400, 'msg' => '请选择要删除的日志'];
        }
        $log = Db::name('server_market_log')->where('id', $id)->find();
        if (empty($log)) {
            return ['status' => 404, 'msg' => '日志不存在或已被删除'];
        }
        $deleted = Db::name('server_market_log')->where('id', $id)->delete();
        if (intval($deleted) < 1) {
            return ['status' => 409, 'msg' => '日志状态已变化，请刷新后重试'];
        }
        return ['status' => 200, 'msg' => '日志已删除'];
    }

    public function clearLogs(array $filter = [], $adminId = 0)
    {
        try {
            $query = Db::name('server_market_log');
            $this->applyLogFilter($query, $filter, '');
            $countQuery = clone $query;
            $count = intval($countQuery->count());
            if ($count < 1) {
                return ['status' => 404, 'msg' => '没有可删除的日志'];
            }
            if (!$this->hasLogFilter($filter)) {
                $query->where('id', '>', 0);
            }
            $deleted = intval($query->delete());
            return ['status' => 200, 'msg' => '已删除 ' . $deleted . ' 条日志'];
        } catch (\Exception $e) {
            return ['status' => 500, 'msg' => '删除日志失败，请稍后重试'];
        }
    }

    public function myListings($uid, $page = 1, $limit = 20)
    {
        $this->expireOldListings();
        $query = $this->listingQuery()->where('l.seller_uid', intval($uid));
        $countQuery = clone $query;
        $count = $countQuery->count();
        $list = $query->order('l.id', 'desc')->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item = $this->appendListingDisplay($item);
        }
        return ['list' => $list, 'count' => $count];
    }

    public function eligibleHosts($uid, $limit = 300, $withMeta = false)
    {
        $this->expireOldListings();
        $limit = min(500, max(20, intval($limit)));
        $statuses = $this->allowedStatuses();
        $fields = ['h.id', 'h.domain', 'h.domainstatus', 'h.billingcycle', 'h.amount', 'h.nextduedate', 'h.productid'];
        foreach (['dedicatedip', 'assignedips', 'os'] as $field) {
            if ($this->tableHasField('host', $field)) {
                $fields[] = 'h.' . $field;
            }
        }
        $fields[] = 'p.name product_name';
        if ($this->tableHasField('products', 'pay_method')) {
            $fields[] = 'p.pay_method payment_type';
        }
        $query = Db::name('host')->alias('h')
            ->field(implode(',', $fields))
            ->leftJoin('products p', 'p.id=h.productid')
            ->where('h.uid', intval($uid));
        if (!empty($statuses)) {
            $query->whereIn('h.domainstatus', $statuses);
        }
        $hosts = $query->order('h.id', 'desc')->limit($limit + 1)->select()->toArray();
        $limited = count($hosts) > $limit;
        if ($limited) {
            $hosts = array_slice($hosts, 0, $limit);
        }
        if (empty($hosts)) {
            return $withMeta ? ['list' => [], 'limited' => false, 'limit' => $limit] : [];
        }

        $hostIds = array_values(array_filter(array_unique(array_map('intval', array_column($hosts, 'id')))));
        $activeRows = Db::name('server_market_listing')
            ->field('host_id')
            ->whereIn('host_id', $hostIds)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_LISTED, self::STATUS_LOCKED])
            ->select()
            ->toArray();
        $activeSet = array_flip(array_map('intval', array_column($activeRows, 'host_id')));

        $invoiceRows = Db::name('invoices')->alias('a')
            ->field('b.rel_id')
            ->leftJoin('invoice_items b', 'a.id=b.invoice_id')
            ->whereIn('b.rel_id', $hostIds)
            ->where('b.delete_time', 0)
            ->where('a.status', 'Unpaid')
            ->where('a.delete_time', 0)
            ->select()
            ->toArray();
        $invoiceSet = array_flip(array_map('intval', array_column($invoiceRows, 'rel_id')));

        $orderRows = Db::name('host')->alias('h')
            ->field('h.id')
            ->leftJoin('orders o', 'h.orderid=o.id')
            ->leftJoin('invoices i', 'o.invoiceid=i.id')
            ->whereIn('h.id', $hostIds)
            ->where('o.delete_time', 0)
            ->where('i.status', 'Unpaid')
            ->where('i.type', '<>', 'credit_limit')
            ->where('i.delete_time', 0)
            ->select()
            ->toArray();
        $orderSet = array_flip(array_map('intval', array_column($orderRows, 'id')));

        $upgradeSet = [];
        if ($this->tableExists('upgrades')) {
            $upgradeRows = Db::name('upgrades')
                ->field('relid')
                ->whereIn('relid', $hostIds)
                ->where('status', '<>', 'Completed')
                ->select()
                ->toArray();
            $upgradeSet = array_flip(array_map('intval', array_column($upgradeRows, 'relid')));
        }
        $cooldownMap = $this->hostCooldownMap($hostIds);

        foreach ($hosts as &$host) {
            $id = intval($host['id']);
            $reasons = [];
            if (isset($activeSet[$id])) {
                $reasons[] = '已有未完成挂牌';
            }
            if (isset($invoiceSet[$id]) || isset($orderSet[$id])) {
                $reasons[] = '存在未支付账单';
            }
            if (isset($upgradeSet[$id])) {
                $reasons[] = '存在未完成升降级';
            }
            if (!empty($cooldownMap[$id])) {
                $reasons[] = $cooldownMap[$id]['msg'];
            }
            $unsupportedReason = $this->marketUnsupportedReason($host);
            if ($unsupportedReason !== '') {
                $reasons[] = $unsupportedReason;
            }
            $host['can_sell'] = empty($reasons) ? 1 : 0;
            $host = $this->appendHostDisplay($host);
            $host['sell_block_reason_text'] = $this->htmlText(implode('，', $reasons));
        }
        return $withMeta ? ['list' => $hosts, 'limited' => $limited, 'limit' => $limit] : $hosts;
    }

    public function getListing($id)
    {
        $this->expireOldListings();
        $item = $this->listingQuery()
            ->where('l.id', intval($id))
            ->where('l.status', self::STATUS_LISTED)
            ->where(function ($q) {
                $q->where('l.expire_time', 0)->whereOr('l.expire_time', '>', time());
            })
            ->find();
        return $item ? $this->appendListingDisplay($item) : [];
    }

    public function createListing($uid, array $param)
    {
        $setting = $this->settings();
        if (empty($setting['enabled'])) {
            return ['status' => 400, 'msg' => '交易市场暂未开启'];
        }

        $hostId = intval($param['host_id'] ?? 0);
        $price = round(floatval($param['price'] ?? 0), 2);
        $ipStatus = intval($param['ip_status'] ?? 0);
        $title = trim(strip_tags($this->redactIpText($param['title'] ?? '')));
        $description = $this->listingDescriptionText($param['description'] ?? '');

        if ($hostId <= 0) {
            return ['status' => 400, 'msg' => '请选择要出售的服务器'];
        }
        if ($price <= 0) {
            return ['status' => 400, 'msg' => '出售价格必须大于 0'];
        }
        if (!in_array($ipStatus, [self::IP_STATUS_NORMAL, self::IP_STATUS_ABNORMAL], true)) {
            return ['status' => 400, 'msg' => '请选择 IP 是否正常'];
        }
        if (floatval($setting['min_price']) > 0 && $price < floatval($setting['min_price'])) {
            return ['status' => 400, 'msg' => '出售价格不能低于最低限制'];
        }
        if (floatval($setting['max_price']) > 0 && $price > floatval($setting['max_price'])) {
            return ['status' => 400, 'msg' => '出售价格不能高于最高限制'];
        }
        if ($title !== '' && mb_strlen($title, 'UTF-8') > 120) {
            return ['status' => 400, 'msg' => '标题不能超过 120 个字符'];
        }

        $guard = $this->canListHost($hostId, $uid);
        if ($guard['status'] !== 200) {
            return $guard;
        }

        $client = Db::name('clients')->field('id,currency')->where('id', intval($uid))->find();
        if (empty($client)) {
            return ['status' => 400, 'msg' => '当前用户不存在'];
        }
        $currency = $this->currencyById(intval($client['currency'] ?? 0));
        $now = time();
        $expireDays = intval($setting['expire_days']);
        $status = !empty($setting['auto_review']) ? self::STATUS_LISTED : self::STATUS_PENDING;
        $this->ensureListingColumns();

        Db::startTrans();
        try {
            $host = Db::name('host')->where('id', $hostId)->where('uid', intval($uid))->lock(true)->find();
            if (empty($host)) {
                throw new \Exception('服务器归属已变化，请刷新后重试');
            }
            $guard = $this->canListHost($hostId, $uid, 0, false);
            if ($guard['status'] !== 200) {
                throw new \Exception($guard['msg']);
            }
            $host['product_name'] = Db::name('products')->where('id', intval($host['productid'] ?? 0))->value('name') ?: '';
            $host = $this->appendHostDisplay($host);
            if ($title === '') {
                $title = $host['auto_title'];
            }
            if ($description === '') {
                $description = $host['auto_description'];
            }
            $description = $this->listingDescriptionText($description);

            $listingId = Db::name('server_market_listing')->insertGetId([
                'listing_no' => $this->makeUniqueNo('server_market_listing', 'listing_no', 'SM'),
                'host_id' => $hostId,
                'seller_uid' => intval($uid),
                'buyer_uid' => 0,
                'product_id' => intval($host['productid'] ?? 0),
                'title' => $title,
                'description' => $description,
                'ip_status' => $ipStatus,
                'price' => sprintf('%.2f', $price),
                'buyer_price' => '0.00',
                'fee_rate' => sprintf('%.2f', floatval($setting['fee_percent'])),
                'fee_amount' => '0.00',
                'seller_amount' => '0.00',
                'currency_id' => intval($currency['id']),
                'currency_code' => $currency['code'],
                'status' => $status,
                'expire_time' => $expireDays > 0 ? $now + $expireDays * 86400 : 0,
                'host_snapshot' => json_encode($this->hostSnapshot($host), JSON_UNESCAPED_UNICODE),
                'create_time' => $now,
                'update_time' => $now,
            ]);
            $this->writeLog($listingId, 0, $hostId, intval($uid), 0, 'create', $status === self::STATUS_LISTED ? '发布挂牌并自动上架' : '提交挂牌等待审核', ['price' => $price, 'ip_status' => $this->ipStatusText($ipStatus), 'remaining_time' => $host['remaining_time_text']]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => $status === self::STATUS_LISTED ? '发布成功，已上架' : '提交成功，等待管理员审核', 'id' => $listingId];
    }

    public function reviewListing($id, $approved, $note = '', $adminId = 0)
    {
        $id = intval($id);
        $note = $this->noteText($note);
        $listing = Db::name('server_market_listing')->where('id', $id)->find();
        if (empty($listing)) {
            return ['status' => 400, 'msg' => '挂牌不存在'];
        }
        if (!in_array(intval($listing['status']), [self::STATUS_PENDING, self::STATUS_OFFLINE, self::STATUS_REJECTED])) {
            return ['status' => 400, 'msg' => '当前状态不允许审核'];
        }

        if ($approved) {
            $guard = $this->canListHost(intval($listing['host_id']), intval($listing['seller_uid']), intval($listing['id']));
            if ($guard['status'] !== 200) {
                return $guard;
            }
            $status = self::STATUS_LISTED;
            $desc = '审核通过并上架';
        } else {
            $status = self::STATUS_REJECTED;
            $desc = '审核拒绝';
        }

        $update = [
            'status' => $status,
            'admin_note' => trim($note),
            'reject_reason' => $approved ? '' : trim($note),
            'review_admin_id' => intval($adminId),
            'review_time' => time(),
            'update_time' => time(),
        ];
        if ($approved && !empty($listing['expire_time']) && intval($listing['expire_time']) <= time()) {
            $update['expire_time'] = $this->freshExpireTime();
        }
        $affected = Db::name('server_market_listing')
            ->where('id', $id)
            ->where('status', intval($listing['status']))
            ->update($update);
        if (intval($affected) < 1) {
            return ['status' => 409, 'msg' => '挂牌状态已变化，请刷新后重试'];
        }
        $this->writeLog($id, 0, intval($listing['host_id']), intval($listing['seller_uid']), intval($adminId), $approved ? 'approve' : 'reject', $desc, ['note' => $note]);
        return ['status' => 200, 'msg' => '操作成功'];
    }

    public function setListingStatus($id, $status, $operatorUid = 0, $adminId = 0, $note = '')
    {
        $id = intval($id);
        $status = intval($status);
        $note = $this->noteText($note);
        $listing = Db::name('server_market_listing')->where('id', $id)->find();
        if (empty($listing)) {
            return ['status' => 400, 'msg' => '挂牌不存在'];
        }
        if (intval($listing['status']) === self::STATUS_SOLD) {
            return ['status' => 400, 'msg' => '已成交挂牌不能修改状态'];
        }
        if ($operatorUid > 0 && intval($listing['seller_uid']) !== intval($operatorUid)) {
            return ['status' => 403, 'msg' => '只能操作自己的挂牌'];
        }
        if (!in_array($status, [self::STATUS_CANCELLED, self::STATUS_OFFLINE, self::STATUS_LISTED])) {
            return ['status' => 400, 'msg' => '状态不正确'];
        }
        if ($status === self::STATUS_LISTED) {
            $guard = $this->canListHost(intval($listing['host_id']), intval($listing['seller_uid']), intval($listing['id']));
            if ($guard['status'] !== 200) {
                return $guard;
            }
        }

        $update = [
            'status' => $status,
            'admin_note' => trim($note),
            'update_time' => time(),
        ];
        if ($status === self::STATUS_LISTED && !empty($listing['expire_time']) && intval($listing['expire_time']) <= time()) {
            $update['expire_time'] = $this->freshExpireTime();
        }
        $affected = Db::name('server_market_listing')
            ->where('id', $id)
            ->where('status', intval($listing['status']))
            ->update($update);
        if (intval($affected) < 1) {
            return ['status' => 409, 'msg' => '挂牌状态已变化，请刷新后重试'];
        }
        $action = $status === self::STATUS_LISTED ? 'online' : ($status === self::STATUS_CANCELLED ? 'cancel' : 'offline');
        $this->writeLog($id, 0, intval($listing['host_id']), intval($listing['seller_uid']), intval($adminId), $action, $this->statusText($status), ['note' => $note]);
        return ['status' => 200, 'msg' => '操作成功'];
    }

    public function buyListing($listingId, $buyerUid)
    {
        $setting = $this->settings();
        if (empty($setting['enabled'])) {
            return ['status' => 400, 'msg' => '交易市场暂未开启'];
        }

        $tradeId = 0;
        $tradeNo = $this->makeUniqueNo('server_market_trade', 'trade_no', 'TR');
        $listing = [];
        $hostBillingSnapshot = [];
        Db::startTrans();
        try {
            $listing = Db::name('server_market_listing')->where('id', intval($listingId))->lock(true)->find();
            if (empty($listing)) {
                throw new \Exception('挂牌不存在');
            }
            if (intval($listing['status']) !== self::STATUS_LISTED) {
                throw new \Exception('该挂牌当前不可购买');
            }
            if (!empty($listing['expire_time']) && intval($listing['expire_time']) <= time()) {
                Db::name('server_market_listing')->where('id', intval($listing['id']))->update(['status' => self::STATUS_OFFLINE, 'update_time' => time()]);
                $this->writeLog(intval($listing['id']), 0, intval($listing['host_id']), intval($listing['seller_uid']), 0, 'expire', '挂牌到期自动下架');
                Db::commit();
                return ['status' => 400, 'msg' => '该挂牌已过期'];
            }
            if (intval($listing['seller_uid']) === intval($buyerUid)) {
                throw new \Exception('不能购买自己的服务器');
            }

            $host = Db::name('host')->where('id', intval($listing['host_id']))->lock(true)->find();
            if (empty($host) || intval($host['uid']) !== intval($listing['seller_uid'])) {
                throw new \Exception('服务器归属已变化，交易已终止');
            }
            $guard = $this->canListHost(intval($listing['host_id']), intval($listing['seller_uid']), intval($listing['id']), false, false);
            if ($guard['status'] !== 200) {
                throw new \Exception($guard['msg']);
            }
            $hostBillingSnapshot = $this->hostBillingSnapshot($host);

            $clientRows = Db::name('clients')
                ->whereIn('id', [intval($listing['seller_uid']), intval($buyerUid)])
                ->order('id', 'asc')
                ->lock(true)
                ->select()
                ->toArray();
            $clients = [];
            foreach ($clientRows as $row) {
                $clients[intval($row['id'])] = $row;
            }
            $seller = $clients[intval($listing['seller_uid'])] ?? [];
            $buyer = $clients[intval($buyerUid)] ?? [];
            if (empty($seller) || empty($buyer)) {
                throw new \Exception('买家或卖家不存在');
            }

            $price = round(floatval($listing['price']), 2);
            $feeRate = round(floatval($listing['fee_rate']), 2);
            $feeAmount = round($price * $feeRate / 100, 2);
            $sellerNet = round($price - $feeAmount, 2);
            if ($sellerNet < 0) {
                $sellerNet = 0;
            }

            $listingCurrency = $this->currencyById(intval($listing['currency_id']));
            $buyerCurrency = $this->currencyById(intval($buyer['currency'] ?? 0));
            $sellerCurrency = $this->currencyById(intval($seller['currency'] ?? 0));
            $buyerPay = $this->convertAmount($price, intval($listingCurrency['id']), intval($buyerCurrency['id']));
            $sellerGross = $this->convertAmount($price, intval($listingCurrency['id']), intval($sellerCurrency['id']));
            $sellerFee = $this->convertAmount($feeAmount, intval($listingCurrency['id']), intval($sellerCurrency['id']));
            $sellerCredit = max(0, round($sellerGross - $sellerFee, 2));

            $buyerBalance = round(floatval($buyer['credit']), 2);
            if ($buyerBalance + 0.0001 < $buyerPay) {
                throw new \Exception('余额不足，需支付 ' . $this->moneyText($buyerPay, $buyerCurrency['code']) . '，当前余额 ' . $this->moneyText($buyerBalance, $buyerCurrency['code']));
            }
            if ($price <= 0 || $buyerPay <= 0) {
                throw new \Exception('交易金额异常，已终止购买');
            }

            $buyerUpdated = Db::name('clients')->where('id', intval($buyerUid))->where('credit', '>=', $buyerPay)->setDec('credit', $buyerPay);
            if (empty($buyerUpdated)) {
                throw new \Exception('余额扣款失败，请刷新后重试');
            }
            if (function_exists('credit_log')) {
                credit_log(['uid' => intval($buyerUid), 'desc' => '购买服务器交易市场挂牌 #' . intval($listing['id']), 'amount' => -$buyerPay, 'relid' => intval($listing['id'])]);
            }

            if ($sellerCredit > 0) {
                $sellerUpdated = Db::name('clients')->where('id', intval($listing['seller_uid']))->setInc('credit', $sellerCredit);
                if (empty($sellerUpdated)) {
                    throw new \Exception('卖家余额入账失败，请稍后重试');
                }
            }
            if (function_exists('credit_log')) {
                credit_log(['uid' => intval($listing['seller_uid']), 'desc' => '服务器交易市场成交入账 #' . intval($listing['id']), 'amount' => $sellerCredit, 'relid' => intval($listing['id'])]);
            }

            $buyerAccountId = $this->insertAccount([
                'uid' => intval($buyerUid),
                'currency' => $buyerCurrency['code'],
                'gateway' => 'credit',
                'create_time' => time(),
                'update_time' => time(),
                'pay_time' => time(),
                'amount_in' => 0,
                'fees' => 0,
                'amount_out' => $buyerPay,
                'rate' => 1,
                'trans_id' => $tradeNo . '-BUY',
                'invoice_id' => 0,
                'refund' => 0,
                'delete_time' => 0,
                'description' => '服务器交易市场购买 Host ID:' . intval($listing['host_id']),
            ]);

            $sellerAccountId = $this->insertAccount([
                'uid' => intval($listing['seller_uid']),
                'currency' => $sellerCurrency['code'],
                'gateway' => 'credit',
                'create_time' => time(),
                'update_time' => time(),
                'pay_time' => time(),
                'amount_in' => $sellerGross,
                'fees' => $sellerFee,
                'amount_out' => 0,
                'rate' => 1,
                'trans_id' => $tradeNo . '-SELL',
                'invoice_id' => 0,
                'refund' => 0,
                'delete_time' => 0,
                'description' => '服务器交易市场出售 Host ID:' . intval($listing['host_id']),
            ]);
            if (empty($buyerAccountId) || empty($sellerAccountId)) {
                throw new \Exception('交易流水写入失败，请稍后重试');
            }

            $hostUpdate = [
                'uid' => intval($buyerUid),
                'update_time' => time(),
            ];
            $hostUpdate = array_merge($hostUpdate, $this->hostBillingRestoreData($hostBillingSnapshot));
            if ($this->tableHasField('host', 'user_cate_id')) {
                $hostUpdate['user_cate_id'] = 0;
            }
            $moved = Db::name('host')->where('id', intval($listing['host_id']))->where('uid', intval($listing['seller_uid']))->update($hostUpdate);
            if (intval($moved) !== 1) {
                throw new \Exception('服务器过户失败，请重试');
            }

            if ($this->tableExists('transfer')) {
                Db::name('transfer')->where('host_id', intval($listing['host_id']))->where('status', 0)->update(['status' => 3, 'update_time' => time()]);
            }

            $tradeId = Db::name('server_market_trade')->insertGetId([
                'trade_no' => $tradeNo,
                'listing_id' => intval($listing['id']),
                'host_id' => intval($listing['host_id']),
                'seller_uid' => intval($listing['seller_uid']),
                'buyer_uid' => intval($buyerUid),
                'price' => sprintf('%.2f', $price),
                'buyer_pay_amount' => sprintf('%.2f', $buyerPay),
                'seller_credit_amount' => sprintf('%.2f', $sellerCredit),
                'fee_amount' => sprintf('%.2f', $sellerFee),
                'fee_rate' => sprintf('%.2f', $feeRate),
                'seller_currency_id' => intval($sellerCurrency['id']),
                'seller_currency_code' => $sellerCurrency['code'],
                'buyer_currency_id' => intval($buyerCurrency['id']),
                'buyer_currency_code' => $buyerCurrency['code'],
                'old_order_id' => intval($host['orderid'] ?? 0),
                'old_product_id' => intval($host['productid'] ?? 0),
                'buyer_account_id' => intval($buyerAccountId),
                'seller_account_id' => intval($sellerAccountId),
                'host_snapshot' => json_encode($this->hostSnapshot($host), JSON_UNESCAPED_UNICODE),
                'status' => 1,
                'create_time' => time(),
            ]);

            Db::name('server_market_listing')->where('id', intval($listing['id']))->update([
                'buyer_uid' => intval($buyerUid),
                'buyer_price' => sprintf('%.2f', $buyerPay),
                'fee_amount' => sprintf('%.2f', $feeAmount),
                'seller_amount' => sprintf('%.2f', $sellerNet),
                'status' => self::STATUS_SOLD,
                'sold_time' => time(),
                'update_time' => time(),
            ]);

            $this->writeLog(intval($listing['id']), $tradeId, intval($listing['host_id']), intval($buyerUid), 0, 'buy', '购买成交', ['trade_no' => $tradeNo, 'buyer_pay' => $buyerPay]);
            $this->writeLog(intval($listing['id']), $tradeId, intval($listing['host_id']), intval($listing['seller_uid']), 0, 'sell', '出售成交', ['trade_no' => $tradeNo, 'seller_credit' => $sellerCredit, 'fee' => $sellerFee]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        $desc = sprintf('服务器交易市场成交 - Trade:%s - Listing ID:%d - Host ID:%d', $tradeNo, intval($listingId), intval($listing['host_id'] ?? 0));
        try {
            if (function_exists('active_log_final')) {
                active_log_final($desc, intval($buyerUid), 2, intval($listing['host_id'] ?? 0), 1);
                active_log_final($desc, intval($listing['seller_uid'] ?? 0), 2, intval($listing['host_id'] ?? 0), 1);
            }
        } catch (\Exception $e) {
            $this->writeLog(intval($listing['id'] ?? 0), $tradeId, intval($listing['host_id'] ?? 0), intval($buyerUid), 0, 'activity_log_error', '成交活动日志写入异常', ['error' => $e->getMessage()]);
        }
        try {
            \think\facade\Hook::listen('transfer_service', ['hostid' => intval($listing['host_id'] ?? 0), 'uid' => intval($listing['seller_uid'] ?? 0), 'transfer_uid' => intval($buyerUid)]);
        } catch (\Exception $e) {
            $this->writeLog(intval($listing['id'] ?? 0), $tradeId, intval($listing['host_id'] ?? 0), intval($buyerUid), 0, 'transfer_hook_error', '过户Hook执行异常', ['error' => $e->getMessage()]);
        }

        $restoreResult = $this->restoreHostBilling(intval($listing['host_id'] ?? 0), $hostBillingSnapshot, intval($buyerUid));
        if (!empty($restoreResult['restored'])) {
            $this->writeLog(intval($listing['id'] ?? 0), $tradeId, intval($listing['host_id'] ?? 0), intval($buyerUid), 0, 'billing_restore', '过户后恢复服务计费字段', $restoreResult);
        } elseif (!empty($restoreResult['error'])) {
            $this->writeLog(intval($listing['id'] ?? 0), $tradeId, intval($listing['host_id'] ?? 0), intval($buyerUid), 0, 'billing_restore_error', '过户后计费字段校验异常', $restoreResult);
        }

        return ['status' => 200, 'msg' => '购买成功，服务器已过户', 'trade_id' => $tradeId, 'trade_no' => $tradeNo];
    }

    public function canListHost($hostId, $uid, $ignoreListingId = 0, $refreshExpired = true, $checkCooldown = true)
    {
        $fields = ['h.id', 'h.uid', 'h.domainstatus', 'h.productid', 'h.orderid', 'h.nextduedate'];
        if ($this->tableHasField('host', 'billingcycle')) {
            $fields[] = 'h.billingcycle';
        }
        if ($this->tableHasField('products', 'pay_method')) {
            $fields[] = 'p.pay_method payment_type';
        }
        $host = Db::name('host')->alias('h')
            ->field(implode(',', $fields))
            ->leftJoin('products p', 'p.id=h.productid')
            ->where('h.id', intval($hostId))
            ->find();
        if (empty($host)) {
            return ['status' => 400, 'msg' => '服务器不存在'];
        }
        if (intval($host['uid']) !== intval($uid)) {
            return ['status' => 400, 'msg' => '该服务器不属于当前用户'];
        }
        $statuses = $this->allowedStatuses();
        if (!empty($statuses) && !in_array($host['domainstatus'], $statuses)) {
            return ['status' => 400, 'msg' => '当前服务器状态不允许出售'];
        }
        $unsupportedReason = $this->marketUnsupportedReason($host);
        if ($unsupportedReason !== '') {
            return ['status' => 400, 'msg' => $unsupportedReason];
        }
        $active = $this->activeListingForHost($hostId, $ignoreListingId, $refreshExpired);
        if (!empty($active)) {
            return ['status' => 400, 'msg' => '该服务器已有未完成挂牌'];
        }
        if ($checkCooldown) {
            $cooldown = $this->hostCooldown($hostId);
            if (!empty($cooldown)) {
                return ['status' => 400, 'msg' => $cooldown['msg']];
            }
        }
        if ($this->hasBlockingInvoice($hostId)) {
            return ['status' => 400, 'msg' => '该服务器存在未支付账单或未完成升降级，暂不能出售'];
        }
        return ['status' => 200, 'host' => $host];
    }

    public function statusMap()
    {
        return [
            self::STATUS_PENDING => '待审核',
            self::STATUS_LISTED => '出售中',
            self::STATUS_LOCKED => '交易锁定',
            self::STATUS_SOLD => '已成交',
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_REJECTED => '已拒绝',
            self::STATUS_OFFLINE => '已下架',
        ];
    }

    public function statusText($status)
    {
        $map = $this->statusMap();
        return $map[intval($status)] ?? '未知';
    }

    public function ipStatusText($status)
    {
        $map = [
            self::IP_STATUS_NORMAL => '正常',
            self::IP_STATUS_ABNORMAL => '异常',
        ];
        return $map[intval($status)] ?? '未确认';
    }

    public function domainStatusText($status)
    {
        $value = trim((string)$status);
        if ($value === '') {
            return '未知';
        }
        $map = [
            'Pending' => '待开通',
            'Active' => '正常',
            'Suspended' => '已暂停',
            'Cancelled' => '已取消',
            'Terminated' => '已终止',
            'Fraud' => '欺诈',
            'Completed' => '已完成',
            'Deleted' => '已删除',
            'Failed' => '开通失败',
        ];
        return $map[$value] ?? $this->htmlText($value);
    }

    public function billingCycleText($cycle)
    {
        $value = trim((string)$cycle);
        if ($value === '') {
            return '未知';
        }
        $key = strtolower(str_replace(['-', '_'], ' ', $value));
        $key = preg_replace('/\s+/', ' ', $key);
        $map = [
            'free account' => '免费',
            'free' => '免费',
            'one time' => '一次性',
            'onetime' => '一次性',
            'monthly' => '月付',
            'quarterly' => '季付',
            'semi annually' => '半年付',
            'semi annual' => '半年付',
            'semiannual' => '半年付',
            'semiannually' => '半年付',
            'annually' => '年付',
            'biennially' => '两年付',
            'triennially' => '三年付',
            'hourly' => '小时付',
            'daily' => '日付',
            'weekly' => '周付',
        ];
        return $map[$key] ?? $this->htmlText($value);
    }

    public function remainingTimeText($nextDueDate)
    {
        $next = $this->timeValue($nextDueDate);
        if ($next <= 0) {
            return '未知';
        }
        $seconds = $next - time();
        if ($seconds <= 0) {
            return '已到期';
        }
        $days = (int)floor($seconds / 86400);
        if ($days >= 1) {
            return '约 ' . $days . ' 天';
        }
        $hours = max(1, (int)ceil($seconds / 3600));
        return '约 ' . $hours . ' 小时';
    }

    public function moneyText($amount, $currencyCode)
    {
        $currency = $this->currencyByCode($currencyCode);
        return ($currency['prefix'] ?? '') . number_format(floatval($amount), 2) . ($currency['suffix'] ?? '');
    }

    public function moneyDisplayText($amount, $currencyCode)
    {
        return $this->htmlText($this->moneyText($amount, $currencyCode));
    }

    private function listingQuery()
    {
        return Db::name('server_market_listing')->alias('l')
            ->field('l.*,h.domain,h.domainstatus,h.billingcycle,h.amount,h.nextduedate,p.name product_name,s.username seller_name,b.username buyer_name,cu.prefix,cu.suffix')
            ->leftJoin('host h', 'h.id=l.host_id')
            ->leftJoin('products p', 'p.id=l.product_id')
            ->leftJoin('clients s', 's.id=l.seller_uid')
            ->leftJoin('clients b', 'b.id=l.buyer_uid')
            ->leftJoin('currencies cu', 'cu.id=l.currency_id');
    }

    private function applyListingFilter($query, array $filter, $includePrivateHostFields = false)
    {
        if (!empty($filter['keyword'])) {
            $keyword = trim($filter['keyword']);
            if (!$includePrivateHostFields && $this->containsIpText($keyword)) {
                $query->where('l.id', 0);
                return;
            }
            $query->where(function ($q) use ($keyword, $includePrivateHostFields) {
                $q->where('l.title', 'like', "%{$keyword}%")
                    ->whereOr('p.name', 'like', "%{$keyword}%")
                    ->whereOr('l.listing_no', 'like', "%{$keyword}%");
                if ($includePrivateHostFields) {
                    $q->whereOr('h.domain', 'like', "%{$keyword}%");
                }
            });
        }
        if (!empty($filter['product_id'])) {
            $query->where('l.product_id', intval($filter['product_id']));
        }
        if (!empty($filter['seller_uid'])) {
            $query->where('l.seller_uid', intval($filter['seller_uid']));
        }
        if (!empty($filter['host_id'])) {
            $query->where('l.host_id', intval($filter['host_id']));
        }
        if (!empty($filter['min_price'])) {
            $query->where('l.price', '>=', floatval($filter['min_price']));
        }
        if (!empty($filter['max_price'])) {
            $query->where('l.price', '<=', floatval($filter['max_price']));
        }
    }

    private function applyLogFilter($query, array $filter, $alias = 'a')
    {
        $prefix = $alias !== '' ? $alias . '.' : '';
        if (!empty($filter['listing_id'])) {
            $query->where($prefix . 'listing_id', intval($filter['listing_id']));
        }
        if (!empty($filter['host_id'])) {
            $query->where($prefix . 'host_id', intval($filter['host_id']));
        }
        if (!empty($filter['action'])) {
            $query->where($prefix . 'action', trim($filter['action']));
        }
    }

    private function hasLogFilter(array $filter)
    {
        return !empty($filter['listing_id']) || !empty($filter['host_id']) || trim((string)($filter['action'] ?? '')) !== '';
    }
  
    private function appendListingDisplay(array $item)
    {
        $item['status_text'] = $this->statusText($item['status']);
        $item['ip_status_text'] = $this->ipStatusText($item['ip_status'] ?? 0);
        $item['domainstatus_text'] = $this->domainStatusText($item['domainstatus'] ?? '');
        $item['billingcycle_text'] = $this->billingCycleText($item['billingcycle'] ?? '');
        $item['remaining_time_text'] = $this->remainingTimeText($item['nextduedate'] ?? 0);
        $item['product_name_text'] = $this->htmlText($item['product_name'] ?? '');
        $item['domain_text'] = $this->htmlText($item['domain'] ?? '');
        $item['domain_public_text'] = trim((string)($item['domain'] ?? '')) !== '' ? $this->htmlText('已隐藏') : '';
        $item['seller_name_text'] = $this->htmlText($item['seller_name'] ?? '');
        $item['buyer_name_text'] = $this->htmlText($item['buyer_name'] ?? '');
        $item['listing_no_text'] = $this->htmlText($item['listing_no'] ?? '');
        $item['title_text'] = $this->htmlText($this->redactIpText($item['title'] ?? ''));
        $item['description_text'] = $this->htmlText($this->redactIpText($item['description'] ?? ''));
        $item['currency_prefix_text'] = $this->htmlText($item['prefix'] ?? '');
        $item['currency_suffix_text'] = $this->htmlText($item['suffix'] ?? '');
        $item['price_text'] = $item['currency_prefix_text'] . number_format(floatval($item['price']), 2) . $item['currency_suffix_text'];
        $item['amount_text'] = $item['currency_prefix_text'] . number_format(floatval($item['amount'] ?? 0), 2) . $item['currency_suffix_text'];
        $item['fee_text'] = $item['currency_prefix_text'] . number_format(floatval($item['fee_amount']), 2) . $item['currency_suffix_text'];
        $item['seller_amount_text'] = $item['currency_prefix_text'] . number_format(floatval($item['seller_amount']), 2) . $item['currency_suffix_text'];
        $item['fee_rate_text'] = number_format(floatval($item['fee_rate'] ?? 0), 2);
        $item['nextduedate_text'] = $this->dateText($item['nextduedate'] ?? 0);
        $item['expire_time_text'] = !empty($item['expire_time']) ? date('Y-m-d H:i', intval($item['expire_time'])) : '长期有效';
        return $item;
    }

    private function appendHostDisplay(array $host)
    {
        $host['product_name'] = $host['product_name'] ?? '';
        $host['domain_text'] = $this->plainText($host['domain'] ?? '');
        $host['os_text'] = $this->plainText($host['os'] ?? '');
        $host['product_name_text'] = $this->plainText($host['product_name'] ?: '服务器');
        $host['domainstatus_text'] = $this->domainStatusText($host['domainstatus'] ?? '');
        $host['billingcycle_text'] = $this->billingCycleText($host['billingcycle'] ?? '');
        $host['nextduedate_text'] = $this->dateText($host['nextduedate'] ?? 0);
        $host['remaining_time_text'] = $this->remainingTimeText($host['nextduedate'] ?? 0);
        $host['auto_title'] = mb_substr($host['product_name_text'] . '转让 - 剩余' . $host['remaining_time_text'], 0, 120, 'UTF-8');
        $host['auto_description'] = $this->autoListingDescription($host);
        $host['domain_text'] = $this->htmlText($host['domain_text']);
        $host['product_name_text'] = $this->htmlText($host['product_name_text']);
        $host['auto_title_text'] = $this->htmlText($host['auto_title']);
        $host['auto_description_text'] = $this->htmlText($host['auto_description']);
        return $host;
    }

    private function autoListingDescription(array $host)
    {
        $lines = [
            '商品详情：' . $host['product_name_text'],
            '系统：' . ($host['os_text'] !== '' ? $host['os_text'] : '-'),
            '服务状态：' . $host['domainstatus_text'],
            '付款周期：' . $host['billingcycle_text'],
            '到期时间：' . $host['nextduedate_text'],
            '剩余时间：' . $host['remaining_time_text'],
        ];
        return implode("\n", $lines);
    }

    private function hasBlockingInvoice($hostId)
    {
        $renew = Db::name('invoices')->alias('a')
            ->leftJoin('invoice_items b', 'a.id=b.invoice_id')
            ->where('b.rel_id', intval($hostId))
            ->where('b.delete_time', 0)
            ->where('a.status', 'Unpaid')
            ->where('a.delete_time', 0)
            ->count();
        if ($renew > 0) {
            return true;
        }

        $order = Db::name('host')->alias('h')
            ->leftJoin('orders o', 'h.orderid=o.id')
            ->leftJoin('invoices i', 'o.invoiceid=i.id')
            ->where('h.id', intval($hostId))
            ->where('o.delete_time', 0)
            ->where('i.status', 'Unpaid')
            ->where('i.type', '<>', 'credit_limit')
            ->where('i.delete_time', 0)
            ->count();
        if ($order > 0) {
            return true;
        }

        if ($this->tableExists('upgrades')) {
            $upgrade = Db::name('upgrades')->where('relid', intval($hostId))->where('status', '<>', 'Completed')->count();
            if ($upgrade > 0) {
                return true;
            }
        }
        return false;
    }

    private function marketUnsupportedReason(array $host)
    {
        $billingCycle = strtolower(trim((string)($host['billingcycle'] ?? '')));
        if (in_array($billingCycle, ['ontrial', 'hour', 'hourly', 'day', 'daily'], true)) {
            return '试用、按小时或按天计费产品暂不支持交易';
        }
        $paymentType = strtolower(trim((string)($host['payment_type'] ?? $host['pay_method'] ?? '')));
        if ($paymentType === 'postpaid') {
            return '后付费产品暂不支持交易';
        }
        return '';
    }

    private function activeListingForHost($hostId, $ignoreListingId = 0, $refreshExpired = true)
    {
        if ($refreshExpired) {
            $this->expireOldListings();
        }
        $query = Db::name('server_market_listing')
            ->where('host_id', intval($hostId))
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_LISTED, self::STATUS_LOCKED]);
        if ($ignoreListingId > 0) {
            $query->where('id', '<>', intval($ignoreListingId));
        }
        return $query->find();
    }

    private function hostCooldown($hostId)
    {
        $map = $this->hostCooldownMap([intval($hostId)]);
        return $map[intval($hostId)] ?? [];
    }

    private function hostCooldownMap(array $hostIds)
    {
        $hostIds = array_values(array_filter(array_unique(array_map('intval', $hostIds))));
        $cooldownSeconds = $this->tradeCooldownSeconds();
        if ($cooldownSeconds <= 0 || empty($hostIds)) {
            return [];
        }

        try {
            $rows = Db::name('server_market_trade')
                ->field('host_id,MAX(create_time) AS last_trade_time')
                ->whereIn('host_id', $hostIds)
                ->where('status', 1)
                ->group('host_id')
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }

        $now = time();
        $map = [];
        foreach ($rows as $row) {
            $hostId = intval($row['host_id'] ?? 0);
            $lastTradeTime = intval($row['last_trade_time'] ?? 0);
            $unlockTime = $lastTradeTime + $cooldownSeconds;
            if ($hostId <= 0 || $lastTradeTime <= 0 || $unlockTime <= $now) {
                continue;
            }
            $remaining = $unlockTime - $now;
            $map[$hostId] = [
                'remaining' => $remaining,
                'unlock_time' => $unlockTime,
                'msg' => '交易冷却中，剩余' . $this->cooldownRemainingText($remaining),
            ];
        }
        return $map;
    }

    private function tradeCooldownSeconds()
    {
        return max(0, min(525600, intval($this->settings()['trade_cooldown_minutes'] ?? 0))) * 60;
    }

    private function cooldownRemainingText($seconds)
    {
        $seconds = max(0, intval($seconds));
        if ($seconds >= 86400) {
            $days = (int)floor($seconds / 86400);
            $hours = (int)ceil(($seconds % 86400) / 3600);
            return $days . '天' . ($hours > 0 ? $hours . '小时' : '');
        }
        if ($seconds >= 3600) {
            $hours = (int)floor($seconds / 3600);
            $minutes = (int)ceil(($seconds % 3600) / 60);
            return $hours . '小时' . ($minutes > 0 ? $minutes . '分钟' : '');
        }
        return max(1, (int)ceil($seconds / 60)) . '分钟';
    }

    private function allowedStatuses()
    {
        $raw = $this->settings()['allow_statuses'] ?? 'Active,Suspended';
        $list = array_filter(array_map('trim', explode(',', $raw)));
        return array_values(array_unique($list));
    }

    private function hostSnapshot(array $host)
    {
        $fields = ['id', 'uid', 'orderid', 'productid', 'serverid', 'domain', 'payment', 'firstpaymentamount', 'amount', 'billingcycle', 'nextduedate', 'domainstatus', 'create_time', 'update_time'];
        return array_intersect_key($host, array_flip($fields));
    }

    private function hostBillingSnapshot(array $host)
    {
        $snapshot = [];
        foreach ($this->hostBillingFields() as $field) {
            if (array_key_exists($field, $host) && $this->tableHasField('host', $field)) {
                $snapshot[$field] = $host[$field];
            }
        }
        return $snapshot;
    }

    private function hostBillingRestoreData(array $snapshot)
    {
        if (empty($snapshot)) {
            return [];
        }
        return $this->filterTableData('host', array_intersect_key($snapshot, array_flip($this->hostBillingFields())));
    }

    private function restoreHostBilling($hostId, array $snapshot, $expectedUid = 0)
    {
        $hostId = intval($hostId);
        $expectedUid = intval($expectedUid);
        $snapshot = $this->hostBillingRestoreData($snapshot);
        if ($hostId <= 0 || empty($snapshot)) {
            return ['restored' => []];
        }

        try {
            $fields = array_keys($snapshot);
            $selectFields = array_values(array_unique(array_merge(['id', 'uid'], $fields)));
            $current = Db::name('host')->field(implode(',', $selectFields))->where('id', $hostId)->find();
            if (empty($current)) {
                return ['restored' => [], 'error' => 'host_not_found'];
            }
            if ($expectedUid > 0 && intval($current['uid'] ?? 0) !== $expectedUid) {
                return ['restored' => [], 'skipped' => 'host_owner_changed'];
            }

            $restore = [];
            $before = [];
            foreach ($snapshot as $field => $value) {
                if (!array_key_exists($field, $current)) {
                    continue;
                }
                if ($this->billingValueChanged($field, $value, $current[$field])) {
                    $restore[$field] = $value;
                    $before[$field] = $current[$field];
                }
            }
            if (empty($restore)) {
                return ['restored' => []];
            }

            $update = $restore;
            if ($this->tableHasField('host', 'update_time')) {
                $update['update_time'] = time();
            }
            $query = Db::name('host')->where('id', $hostId);
            if ($expectedUid > 0) {
                $query->where('uid', $expectedUid);
            }
            $affected = $query->update($update);
            if (empty($affected)) {
                return ['restored' => [], 'error' => 'restore_update_failed', 'fields' => array_keys($restore)];
            }

            return [
                'restored' => array_keys($restore),
                'before' => $before,
                'after' => $restore,
            ];
        } catch (\Exception $e) {
            return ['restored' => [], 'error' => $e->getMessage()];
        }
    }

    private function hostBillingFields()
    {
        return ['firstpaymentamount', 'amount', 'billingcycle', 'payment', 'promoid'];
    }

    private function billingValueChanged($field, $expected, $actual)
    {
        if (in_array($field, ['firstpaymentamount', 'amount'], true) && is_numeric($expected) && is_numeric($actual)) {
            return round(floatval($expected), 2) !== round(floatval($actual), 2);
        }
        if (in_array($field, ['promoid', 'nextduedate', 'nextinvoicedate'], true) && is_numeric($expected) && is_numeric($actual)) {
            return intval($expected) !== intval($actual);
        }
        return (string)$expected !== (string)$actual;
    }

    private function insertAccount(array $data)
    {
        return Db::name('accounts')->insertGetId($this->filterTableData('accounts', $data));
    }

    private function convertAmount($amount, $fromCurrencyId, $toCurrencyId)
    {
        $amount = floatval($amount);
        if ($fromCurrencyId <= 0 || $toCurrencyId <= 0 || $fromCurrencyId === $toCurrencyId) {
            return round($amount, 2);
        }
        if (function_exists('convert_currency')) {
            return round(floatval(convert_currency($amount, $fromCurrencyId, $toCurrencyId)), 2);
        }
        $fromRate = floatval(Db::name('currencies')->where('id', $fromCurrencyId)->value('rate')) ?: 1;
        $toRate = floatval(Db::name('currencies')->where('id', $toCurrencyId)->value('rate')) ?: 1;
        return round($amount / $fromRate * $toRate, 2);
    }

    private function currencyById($id)
    {
        $currency = [];
        if ($id > 0) {
            $currency = Db::name('currencies')->field('id,code,prefix,suffix,rate')->where('id', intval($id))->find() ?: [];
        }
        if (empty($currency)) {
            $currency = Db::name('currencies')->field('id,code,prefix,suffix,rate')->where('default', 1)->find() ?: [];
        }
        if (empty($currency)) {
            $currency = ['id' => 0, 'code' => 'CNY', 'prefix' => '', 'suffix' => '元', 'rate' => 1];
        }
        return $currency;
    }

    private function currencyByCode($code)
    {
        $currency = [];
        if ($code !== '') {
            $currency = Db::name('currencies')->field('id,code,prefix,suffix,rate')->where('code', $code)->find() ?: [];
        }
        return $currency ?: ['id' => 0, 'code' => $code, 'prefix' => '', 'suffix' => '', 'rate' => 1];
    }

    private function ensureDefaultSettings()
    {
        foreach ($this->defaultSettings() as $name => $value) {
            $exists = Db::name('server_market_setting')->where('name', $name)->find();
            if (empty($exists)) {
                Db::name('server_market_setting')->insert([
                    'name' => $name,
                    'value' => $value,
                    'update_time' => time(),
                ]);
            }
        }
    }

    private function defaultSettings()
    {
        return [
            'enabled' => '1',
            'auto_review' => '0',
            'fee_percent' => '5.00',
            'min_price' => '1.00',
            'max_price' => '0.00',
            'expire_days' => '30',
            'trade_cooldown_minutes' => '0',
            'allow_statuses' => 'Active,Suspended',
            'buyer_notice' => '购买将直接从账户余额扣款，成交后服务器会自动过户到您的账户。',
        ];
    }

    private function emptyPriceHistory($currencyCode = '')
    {
        return [
            'count' => 0,
            'avg_value' => 0,
            'min_value' => 0,
            'max_value' => 0,
            'avg_text' => $this->moneyDisplayText(0, $currencyCode),
            'avg_text_attr' => $this->moneyDisplayText(0, $currencyCode),
            'min_text' => $this->moneyDisplayText(0, $currencyCode),
            'max_text' => $this->moneyDisplayText(0, $currencyCode),
            'latest_text' => '-',
            'chart_json' => '[]',
            'points' => [],
        ];
    }

    private function freshExpireTime()
    {
        $days = intval($this->settings()['expire_days'] ?? 0);
        return $days > 0 ? time() + $days * 86400 : 0;
    }

    private function expireOldListings()
    {
        if ($this->expiredChecked) {
            return;
        }
        $this->expiredChecked = true;
        try {
            Db::name('server_market_listing')
                ->where('status', self::STATUS_LISTED)
                ->where('expire_time', '>', 0)
                ->where('expire_time', '<=', time())
                ->update(['status' => self::STATUS_OFFLINE, 'update_time' => time()]);
        } catch (\Exception $e) {
        }
    }

    private function repairPersistentData()
    {
        $now = time();
        $this->repairEmptyNumbers('server_market_listing', 'listing_no', 'SM');
        $this->repairDuplicateNumbers('server_market_listing', 'listing_no', 'SM');
        $this->repairEmptyNumbers('server_market_trade', 'trade_no', 'TR');
        $this->repairDuplicateNumbers('server_market_trade', 'trade_no', 'TR');
        $this->repairListingSensitiveText();
        try {
            Db::name('server_market_listing')
                ->whereNotIn('status', [
                    self::STATUS_PENDING,
                    self::STATUS_LISTED,
                    self::STATUS_LOCKED,
                    self::STATUS_SOLD,
                    self::STATUS_CANCELLED,
                    self::STATUS_REJECTED,
                    self::STATUS_OFFLINE,
                ])
                ->update(['status' => self::STATUS_OFFLINE, 'update_time' => $now, 'admin_note' => '重装恢复：异常状态已下架']);
        } catch (\Exception $e) {
        }
        try {
            Db::name('server_market_listing')
                ->where('status', self::STATUS_LISTED)
                ->where('expire_time', '>', 0)
                ->where('expire_time', '<=', $now)
                ->update(['status' => self::STATUS_OFFLINE, 'update_time' => $now, 'admin_note' => '重装恢复：过期挂牌已下架']);
        } catch (\Exception $e) {
        }
        try {
            Db::name('server_market_listing')
                ->where('status', self::STATUS_LOCKED)
                ->where('sold_time', 0)
                ->update(['status' => self::STATUS_OFFLINE, 'update_time' => $now, 'admin_note' => '重装恢复：锁定残留已下架']);
        } catch (\Exception $e) {
        }
    }

    private function recoverActiveListingsAfterInstall()
    {
        try {
            $rows = Db::name('server_market_listing')->alias('l')
                ->field('l.id,l.host_id,l.seller_uid,l.status,h.uid host_uid')
                ->leftJoin('host h', 'h.id=l.host_id')
                ->whereIn('l.status', [self::STATUS_PENDING, self::STATUS_LISTED])
                ->order('l.status', 'desc')
                ->order('l.id', 'asc')
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            return;
        }
        if (empty($rows)) {
            return;
        }

        $now = time();
        $activeByHost = [];
        $keptRows = [];
        foreach ($rows as $row) {
            $id = intval($row['id'] ?? 0);
            $hostId = intval($row['host_id'] ?? 0);
            $sellerUid = intval($row['seller_uid'] ?? 0);
            $hostUid = intval($row['host_uid'] ?? 0);
            if ($id <= 0 || $hostId <= 0 || $sellerUid <= 0 || $hostUid !== $sellerUid) {
                $this->offlineRecoveredListing($id, '重装恢复：服务器归属异常已下架', $now);
                continue;
            }
            if (isset($activeByHost[$hostId])) {
                $this->offlineRecoveredListing($id, '重装恢复：同一服务器重复活跃挂牌已下架', $now);
                continue;
            }
            $activeByHost[$hostId] = $id;
            $keptRows[] = $row;
        }

        foreach ($keptRows as $row) {
            $id = intval($row['id'] ?? 0);
            $hostId = intval($row['host_id'] ?? 0);
            $sellerUid = intval($row['seller_uid'] ?? 0);
            $guard = $this->canListHost($hostId, $sellerUid, $id, false);
            if ($guard['status'] !== 200) {
                $this->offlineRecoveredListing($id, '重装恢复：' . $guard['msg'], $now);
            }
        }
    }

    private function offlineRecoveredListing($id, $note, $time)
    {
        if (intval($id) <= 0) {
            return;
        }
        try {
            Db::name('server_market_listing')
                ->where('id', intval($id))
                ->whereIn('status', [self::STATUS_PENDING, self::STATUS_LISTED, self::STATUS_LOCKED])
                ->update(['status' => self::STATUS_OFFLINE, 'update_time' => intval($time), 'admin_note' => $note]);
        } catch (\Exception $e) {
        }
    }

    private function repairEmptyNumbers($table, $field, $prefix)
    {
        try {
            $rows = Db::name($table)->field('id')->where($field, '')->select()->toArray();
            foreach ($rows as $row) {
                Db::name($table)->where('id', intval($row['id']))->update([$field => $this->makeUniqueNo($table, $field, $prefix)]);
            }
        } catch (\Exception $e) {
        }
    }

    private function repairDuplicateNumbers($table, $field, $prefix)
    {
        try {
            $groups = Db::name($table)
                ->field($field . ',COUNT(*) AS repeat_count')
                ->where($field, '<>', '')
                ->group($field)
                ->having('repeat_count>1')
                ->select()
                ->toArray();
            foreach ($groups as $group) {
                $rows = Db::name($table)
                    ->field('id')
                    ->where($field, $group[$field])
                    ->order('id', 'asc')
                    ->select()
                    ->toArray();
                $keepFirst = true;
                foreach ($rows as $row) {
                    if ($keepFirst) {
                        $keepFirst = false;
                        continue;
                    }
                    Db::name($table)->where('id', intval($row['id']))->update([$field => $this->makeUniqueNo($table, $field, $prefix)]);
                }
            }
        } catch (\Exception $e) {
        }
    }

    private function repairListingSensitiveText()
    {
        try {
            $rows = Db::name('server_market_listing')
                ->field('id,title,description')
                ->whereIn('status', [self::STATUS_PENDING, self::STATUS_LISTED, self::STATUS_LOCKED])
                ->select()
                ->toArray();
            foreach ($rows as $row) {
                $update = [];
                $title = (string)($row['title'] ?? '');
                $safeTitle = mb_substr(trim(strip_tags($this->redactIpText($title))), 0, 120, 'UTF-8');
                if ($safeTitle !== $title) {
                    $update['title'] = $safeTitle;
                }
                $description = (string)($row['description'] ?? '');
                $safeDescription = $this->listingDescriptionText($description);
                if ($safeDescription !== $description) {
                    $update['description'] = $safeDescription;
                }
                if (!empty($update)) {
                    $update['update_time'] = time();
                    Db::name('server_market_listing')->where('id', intval($row['id']))->update($update);
                }
            }
        } catch (\Exception $e) {
        }
    }

    private function setSetting($name, $value)
    {
        $this->settingsCache = null;
        $exists = Db::name('server_market_setting')->where('name', $name)->find();
        $data = ['value' => $value, 'update_time' => time()];
        if (empty($exists)) {
            $data['name'] = $name;
            Db::name('server_market_setting')->insert($data);
        } else {
            Db::name('server_market_setting')->where('name', $name)->update($data);
        }
    }

    private function writeLog($listingId, $tradeId, $hostId, $uid, $adminId, $action, $description, array $data = [])
    {
        try {
            Db::name('server_market_log')->insert([
                'listing_id' => intval($listingId),
                'trade_id' => intval($tradeId),
                'host_id' => intval($hostId),
                'uid' => intval($uid),
                'admin_id' => intval($adminId),
                'action' => $action,
                'description' => mb_substr($description, 0, 255, 'UTF-8'),
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'ip' => function_exists('get_client_ip6') ? get_client_ip6() : '',
                'create_time' => time(),
            ]);
        } catch (\Exception $e) {
        }
    }

    private function makeNo($prefix)
    {
        return $prefix . date('ymdHis') . substr(str_replace('.', '', (string)microtime(true)), -6) . mt_rand(100, 999);
    }

    private function makeUniqueNo($table, $field, $prefix)
    {
        for ($i = 0; $i < 8; $i++) {
            $no = $this->makeNo($prefix);
            try {
                $exists = Db::name($table)->where($field, $no)->find();
                if (empty($exists)) {
                    return $no;
                }
            } catch (\Exception $e) {
                return $no;
            }
        }
        return $this->makeNo($prefix);
    }

    private function htmlText($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    private function noteText($value)
    {
        return mb_substr(trim(strip_tags((string)$value)), 0, 255, 'UTF-8');
    }

    private function listingDescriptionText($value)
    {
        return mb_substr(trim(strip_tags($this->redactIpText($value))), 0, 2000, 'UTF-8');
    }

    private function redactIpText($value, $replacement = '[IP已隐藏]')
    {
        $text = (string)$value;
        $text = preg_replace('/(?<![\d.])(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}(?![\d.])/', $replacement, $text);
        $text = preg_replace('/(?<![A-Fa-f0-9:])(?:[A-Fa-f0-9]{1,4}:){2,7}[A-Fa-f0-9]{0,4}(?![A-Fa-f0-9:])/', $replacement, $text);
        return $text;
    }

    private function containsIpText($value)
    {
        $text = (string)$value;
        if (preg_match('/(?<![\d.])(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}(?![\d.])/', $text)) {
            return true;
        }
        return preg_match('/(?<![A-Fa-f0-9:])(?:[A-Fa-f0-9]{1,4}:){2,7}[A-Fa-f0-9]{0,4}(?![A-Fa-f0-9:])/', $text) === 1;
    }

    private function plainText($value)
    {
        return trim(strip_tags((string)$value));
    }

    private function dateText($value)
    {
        $time = $this->timeValue($value);
        return $time > 0 ? date('Y-m-d', $time) : '-';
    }

    private function timeValue($value)
    {
        if (is_numeric($value)) {
            return intval($value);
        }
        $value = trim((string)$value);
        if ($value === '') {
            return 0;
        }
        $time = strtotime($value);
        return $time > 0 ? $time : 0;
    }

    private function tablePrefix()
    {
        $prefix = Db::getConfig('prefix');
        if (!$prefix && function_exists('config')) {
            $prefix = config('database.prefix');
        }
        return $prefix ?: 'shd_';
    }

    private function tableExists($table)
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        try {
            $rows = Db::query("SHOW TABLES LIKE '" . addslashes($this->tablePrefix() . $table) . "'");
            $cache[$table] = !empty($rows);
        } catch (\Exception $e) {
            $cache[$table] = false;
        }
        return $cache[$table];
    }

    private function ensureIndex($table, $index, $sql)
    {
        try {
            $exists = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = '" . addslashes($index) . "'");
            if (empty($exists)) {
                Db::execute($sql);
            }
        } catch (\Exception $e) {
        }
    }

    private function ensureUniqueIndex($table, $index, $sql)
    {
        try {
            $rows = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = '" . addslashes($index) . "'");
            if (empty($rows)) {
                Db::execute($sql);
                return;
            }
            $isUnique = false;
            foreach ($rows as $row) {
                if (isset($row['Non_unique']) && intval($row['Non_unique']) === 0) {
                    $isUnique = true;
                    break;
                }
            }
            if (!$isUnique) {
                Db::execute("ALTER TABLE `{$table}` DROP INDEX `" . str_replace('`', '``', $index) . "`");
                Db::execute($sql);
            }
        } catch (\Exception $e) {
        }
    }

    private function ensureListingColumns()
    {
        $table = $this->tablePrefix() . 'server_market_listing';
        $this->ensureColumn($table, 'listing_no', 'ALTER TABLE `' . $table . '` ADD `listing_no` varchar(40) NOT NULL DEFAULT \'\' AFTER `id`');
        $this->ensureColumn($table, 'host_id', 'ALTER TABLE `' . $table . '` ADD `host_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `listing_no`');
        $this->ensureColumn($table, 'seller_uid', 'ALTER TABLE `' . $table . '` ADD `seller_uid` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `host_id`');
        $this->ensureColumn($table, 'buyer_uid', 'ALTER TABLE `' . $table . '` ADD `buyer_uid` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `seller_uid`');
        $this->ensureColumn($table, 'product_id', 'ALTER TABLE `' . $table . '` ADD `product_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `buyer_uid`');
        $this->ensureColumn($table, 'title', 'ALTER TABLE `' . $table . '` ADD `title` varchar(120) NOT NULL DEFAULT \'\' AFTER `product_id`');
        $this->ensureColumn($table, 'description', 'ALTER TABLE `' . $table . '` ADD `description` text AFTER `title`');
        $this->ensureColumn($table, 'ip_status', 'ALTER TABLE `' . $table . '` ADD `ip_status` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'IP状态：1正常 2异常\' AFTER `description`');
        $this->ensureColumn($table, 'price', 'ALTER TABLE `' . $table . '` ADD `price` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `ip_status`');
        $this->ensureColumn($table, 'buyer_price', 'ALTER TABLE `' . $table . '` ADD `buyer_price` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `price`');
        $this->ensureColumn($table, 'fee_rate', 'ALTER TABLE `' . $table . '` ADD `fee_rate` decimal(8,2) NOT NULL DEFAULT \'0.00\' AFTER `buyer_price`');
        $this->ensureColumn($table, 'fee_amount', 'ALTER TABLE `' . $table . '` ADD `fee_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `fee_rate`');
        $this->ensureColumn($table, 'seller_amount', 'ALTER TABLE `' . $table . '` ADD `seller_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `fee_amount`');
        $this->ensureColumn($table, 'currency_id', 'ALTER TABLE `' . $table . '` ADD `currency_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `seller_amount`');
        $this->ensureColumn($table, 'currency_code', 'ALTER TABLE `' . $table . '` ADD `currency_code` varchar(10) NOT NULL DEFAULT \'\' AFTER `currency_id`');
        $this->ensureColumn($table, 'status', 'ALTER TABLE `' . $table . '` ADD `status` tinyint(2) unsigned NOT NULL DEFAULT \'0\' AFTER `currency_code`');
        $this->ensureColumn($table, 'expire_time', 'ALTER TABLE `' . $table . '` ADD `expire_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `status`');
        $this->ensureColumn($table, 'review_admin_id', 'ALTER TABLE `' . $table . '` ADD `review_admin_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `expire_time`');
        $this->ensureColumn($table, 'review_time', 'ALTER TABLE `' . $table . '` ADD `review_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `review_admin_id`');
        $this->ensureColumn($table, 'admin_note', 'ALTER TABLE `' . $table . '` ADD `admin_note` varchar(255) NOT NULL DEFAULT \'\' AFTER `review_time`');
        $this->ensureColumn($table, 'reject_reason', 'ALTER TABLE `' . $table . '` ADD `reject_reason` varchar(255) NOT NULL DEFAULT \'\' AFTER `admin_note`');
        $this->ensureColumn($table, 'host_snapshot', 'ALTER TABLE `' . $table . '` ADD `host_snapshot` text AFTER `reject_reason`');
        $this->ensureColumn($table, 'create_time', 'ALTER TABLE `' . $table . '` ADD `create_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `host_snapshot`');
        $this->ensureColumn($table, 'update_time', 'ALTER TABLE `' . $table . '` ADD `update_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `create_time`');
        $this->ensureColumn($table, 'sold_time', 'ALTER TABLE `' . $table . '` ADD `sold_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `update_time`');
    }

    private function ensureTradeColumns()
    {
        $table = $this->tablePrefix() . 'server_market_trade';
        $this->ensureColumn($table, 'trade_no', 'ALTER TABLE `' . $table . '` ADD `trade_no` varchar(40) NOT NULL DEFAULT \'\' AFTER `id`');
        $this->ensureColumn($table, 'listing_id', 'ALTER TABLE `' . $table . '` ADD `listing_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `trade_no`');
        $this->ensureColumn($table, 'host_id', 'ALTER TABLE `' . $table . '` ADD `host_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `listing_id`');
        $this->ensureColumn($table, 'seller_uid', 'ALTER TABLE `' . $table . '` ADD `seller_uid` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `host_id`');
        $this->ensureColumn($table, 'buyer_uid', 'ALTER TABLE `' . $table . '` ADD `buyer_uid` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `seller_uid`');
        $this->ensureColumn($table, 'price', 'ALTER TABLE `' . $table . '` ADD `price` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `buyer_uid`');
        $this->ensureColumn($table, 'buyer_pay_amount', 'ALTER TABLE `' . $table . '` ADD `buyer_pay_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `price`');
        $this->ensureColumn($table, 'seller_credit_amount', 'ALTER TABLE `' . $table . '` ADD `seller_credit_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `buyer_pay_amount`');
        $this->ensureColumn($table, 'fee_amount', 'ALTER TABLE `' . $table . '` ADD `fee_amount` decimal(12,2) NOT NULL DEFAULT \'0.00\' AFTER `seller_credit_amount`');
        $this->ensureColumn($table, 'fee_rate', 'ALTER TABLE `' . $table . '` ADD `fee_rate` decimal(8,2) NOT NULL DEFAULT \'0.00\' AFTER `fee_amount`');
        $this->ensureColumn($table, 'seller_currency_id', 'ALTER TABLE `' . $table . '` ADD `seller_currency_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `fee_rate`');
        $this->ensureColumn($table, 'seller_currency_code', 'ALTER TABLE `' . $table . '` ADD `seller_currency_code` varchar(10) NOT NULL DEFAULT \'\' AFTER `seller_currency_id`');
        $this->ensureColumn($table, 'buyer_currency_id', 'ALTER TABLE `' . $table . '` ADD `buyer_currency_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `seller_currency_code`');
        $this->ensureColumn($table, 'buyer_currency_code', 'ALTER TABLE `' . $table . '` ADD `buyer_currency_code` varchar(10) NOT NULL DEFAULT \'\' AFTER `buyer_currency_id`');
        $this->ensureColumn($table, 'old_order_id', 'ALTER TABLE `' . $table . '` ADD `old_order_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `buyer_currency_code`');
        $this->ensureColumn($table, 'old_product_id', 'ALTER TABLE `' . $table . '` ADD `old_product_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `old_order_id`');
        $this->ensureColumn($table, 'buyer_account_id', 'ALTER TABLE `' . $table . '` ADD `buyer_account_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `old_product_id`');
        $this->ensureColumn($table, 'seller_account_id', 'ALTER TABLE `' . $table . '` ADD `seller_account_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `buyer_account_id`');
        $this->ensureColumn($table, 'host_snapshot', 'ALTER TABLE `' . $table . '` ADD `host_snapshot` text AFTER `seller_account_id`');
        $this->ensureColumn($table, 'status', 'ALTER TABLE `' . $table . '` ADD `status` tinyint(1) unsigned NOT NULL DEFAULT \'1\' AFTER `host_snapshot`');
        $this->ensureColumn($table, 'create_time', 'ALTER TABLE `' . $table . '` ADD `create_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `status`');
    }

    private function ensureLogColumns()
    {
        $table = $this->tablePrefix() . 'server_market_log';
        $this->ensureColumn($table, 'listing_id', 'ALTER TABLE `' . $table . '` ADD `listing_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `id`');
        $this->ensureColumn($table, 'trade_id', 'ALTER TABLE `' . $table . '` ADD `trade_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `listing_id`');
        $this->ensureColumn($table, 'host_id', 'ALTER TABLE `' . $table . '` ADD `host_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `trade_id`');
        $this->ensureColumn($table, 'uid', 'ALTER TABLE `' . $table . '` ADD `uid` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `host_id`');
        $this->ensureColumn($table, 'admin_id', 'ALTER TABLE `' . $table . '` ADD `admin_id` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `uid`');
        $this->ensureColumn($table, 'action', 'ALTER TABLE `' . $table . '` ADD `action` varchar(40) NOT NULL DEFAULT \'\' AFTER `admin_id`');
        $this->ensureColumn($table, 'description', 'ALTER TABLE `' . $table . '` ADD `description` varchar(255) NOT NULL DEFAULT \'\' AFTER `action`');
        $this->ensureColumn($table, 'data', 'ALTER TABLE `' . $table . '` ADD `data` text AFTER `description`');
        $this->ensureColumn($table, 'ip', 'ALTER TABLE `' . $table . '` ADD `ip` varchar(64) NOT NULL DEFAULT \'\' AFTER `data`');
        $this->ensureColumn($table, 'create_time', 'ALTER TABLE `' . $table . '` ADD `create_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `ip`');
    }

    private function ensureSettingColumns()
    {
        $table = $this->tablePrefix() . 'server_market_setting';
        $this->ensureColumn($table, 'name', 'ALTER TABLE `' . $table . '` ADD `name` varchar(80) NOT NULL DEFAULT \'\' AFTER `id`');
        $this->ensureColumn($table, 'value', 'ALTER TABLE `' . $table . '` ADD `value` text AFTER `name`');
        $this->ensureColumn($table, 'update_time', 'ALTER TABLE `' . $table . '` ADD `update_time` int(10) unsigned NOT NULL DEFAULT \'0\' AFTER `value`');
    }

    private function ensureColumn($table, $field, $sql)
    {
        try {
            $exists = Db::query("SHOW COLUMNS FROM `{$table}` LIKE '" . addslashes($field) . "'");
            if (empty($exists)) {
                Db::execute($sql);
            }
        } catch (\Exception $e) {
        }
    }

    private function tableHasField($table, $field)
    {
        return in_array($field, $this->tableFields($table));
    }

    private function tableFields($table)
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        try {
            $rows = Db::query('SHOW COLUMNS FROM `' . $this->tablePrefix() . $table . '`');
            $fields = [];
            foreach ($rows as $row) {
                if (isset($row['Field'])) {
                    $fields[] = $row['Field'];
                }
            }
            $cache[$table] = $fields;
        } catch (\Exception $e) {
            $cache[$table] = [];
        }
        return $cache[$table];
    }

    private function filterTableData($table, array $data)
    {
        $fields = $this->tableFields($table);
        if (empty($fields)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($fields));
    }
}
