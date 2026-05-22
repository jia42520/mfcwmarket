<?php

return [
    [
        'name' => '服务器交易',
        'url' => '',
        'fa_icon' => 'bx bx-store',
        'lang' => [
            'chinese' => '服务器交易',
            'chinese_tw' => '伺服器交易',
            'english' => 'Server Market',
        ],
        'child' => [
            [
                'name' => '交易市场',
                'url' => 'ServerMarket://Index/index',
                'fa_icon' => '',
                'lang' => [
                    'chinese' => '交易市场',
                    'chinese_tw' => '交易市場',
                    'english' => 'Market',
                ],
                'child' => [],
            ],
            [
                'name' => '我要出售',
                'url' => 'ServerMarket://Index/sell',
                'fa_icon' => '',
                'lang' => [
                    'chinese' => '我要出售',
                    'chinese_tw' => '我要出售',
                    'english' => 'Sell',
                ],
                'child' => [],
            ],
            [
                'name' => '我的交易',
                'url' => 'ServerMarket://Index/my',
                'fa_icon' => '',
                'lang' => [
                    'chinese' => '我的交易',
                    'chinese_tw' => '我的交易',
                    'english' => 'My Trades',
                ],
                'child' => [],
            ],
        ],
    ],
];
