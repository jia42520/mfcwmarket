<?php

return [
    'basic' => [
        'title' => '基础设置',
        'type' => 'group',
        'options' => [
            'market' => [
                'title' => '交易市场',
                'type' => 'fieldset',
                'options' => [
                    'theme_color' => [
                        'title' => '主题色',
                        'type' => 'text',
                        'value' => '#16a34a',
                        'tip' => '前台与后台界面主色调。',
                    ],
                    'fee_percent' => [
                        'title' => '默认手续费百分比',
                        'type' => 'text',
                        'value' => '5.00',
                        'tip' => '成交后从卖家收入中扣除。',
                    ],
                ],
            ],
        ],
    ],
];
