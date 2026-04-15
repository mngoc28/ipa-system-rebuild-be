<?php

return [
    'groups' => [
        'mail' => [
            [
                'key' => 'smtp_host',
                'label' => 'SMTP Host',
                'default_value' => 'smtp.ipa.danang.gov.vn',
                'is_secret' => false,
            ],
            [
                'key' => 'smtp_port',
                'label' => 'SMTP Port',
                'default_value' => '587',
                'is_secret' => false,
            ],
            [
                'key' => 'smtp_security',
                'label' => 'Bảo mật',
                'default_value' => 'TLS',
                'is_secret' => false,
                'options' => ['TLS', 'SSL', 'None'],
            ],
        ],
        'zalo' => [
            [
                'key' => 'zalo_app_id',
                'label' => 'Zalo App ID',
                'default_value' => '',
                'is_secret' => false,
            ],
            [
                'key' => 'zalo_secret',
                'label' => 'Secret Key',
                'default_value' => '',
                'is_secret' => true,
            ],
        ],
    ],
];