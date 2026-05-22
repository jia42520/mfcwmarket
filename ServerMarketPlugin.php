<?php

namespace addons\server_market;

use app\admin\lib\Plugin;
use addons\server_market\model\ServerMarketModel;

class ServerMarketPlugin extends Plugin
{
    public $hasAdmin = 1;

    public $info = [
        'name' => 'ServerMarket',
        'title' => '服务器交易市场',
        'description' => '面向智简魔方财务系统的服务器交易市场插件，提供客户自助挂牌、后台审核、余额结算、自动过户、历史成交价格图表和审计管理能力。',
        'status' => 1,
        'author' => '光锥云',
        'version' => '1.0',
        'module' => 'addons',
        'lang' => [
            'chinese' => '服务器交易市场',
            'chinese_tw' => '伺服器交易市場',
            'english' => 'Server Market',
        ],
    ];

    public function install()
    {
        $this->loadModel();
        return (new ServerMarketModel())->install();
    }

    public function uninstall()
    {
        $this->loadModel();
        return (new ServerMarketModel())->uninstall(false);
    }

    public function update()
    {
        $this->loadModel();
        return (new ServerMarketModel())->install();
    }

    private function loadModel()
    {
        $file = __DIR__ . '/model/ServerMarketModel.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
