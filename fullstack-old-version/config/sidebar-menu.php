<?php
    $menus = [
        'admin_groups'      => [
            'menu'          => 'MENU',
            'features'      => 'TÍNH NĂNG',
            'management'    => 'QUẢN LÝ',
        ],
        'admin' => [
            'dashboard' => [
                'route'         => 'admin.dashboard',
                'route_prefix'  => 'admin.dashboard',
                'x_icon_name'   => 'tachometer',
                'x_icon_prefix' => null,
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => null,
            ],
            'companys' => [
                'route'         => 'admin.companys.index',
                'route_prefix'  => [
                    'admin.companys.*',
                    'admin.companys.create',
                    'admin.companys.edit',
                ],
                'x_icon_name'   => 'building',
                'x_icon_prefix' => null,
                'text'          => 'Công ty',
                'is_admin'      => true,
                'group'         => 'menu',
            ],
            'events' => [
                'route'         => 'admin.events.index',
                'route_prefix'  => [
                    'admin.reports.*',
                    'admin.events.*',
                    'admin.reports.*',
                    'admin.clients.*',
                ],
                'x_icon_name'   => 'calendar-days',
                'x_icon_prefix' => null,
                'text'          => 'Sự kiện',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'menu',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.events.create',
                        'route_prefix'  => 'admin.events.create',
                        'text'          => 'Tạo sự kiện',
                        // 'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.events.index',
                        'route_prefix'  => [
                            'admin.events.index',
                            'admin.events.edit',
                            'admin.clients.*',
                            'admin.checkins.*',
                        ],
                        'text'          => 'Quản lý',
                        // 'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'report'    => [
                        'route'         => 'admin.reports.index',
                        'route_prefix'  => 'admin.reports.*',
                        'text'          => 'Báo cáo',
                        // 'is_admin'      => true,
                        'roles'         => [
                            'admin',
                            'user',
                        ],
                    ],
                ]
            ],
            'landing_pages' => [
                'route'         => 'admin.landing_pages.index',
                'route_prefix'  => [
                    'admin.landing_pages.*',
                ],
                'x_icon_name'   => 'feather',
                'x_icon_prefix' => null,
                'text'          => 'Đăng ký',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'features',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.landing_pages.create',
                        'route_prefix'  => 'admin.landing_pages.create',
                        'text'          => 'Tạo landing page',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.landing_pages.index',
                        'route_prefix'  => [
                            'admin.landing_pages.index',
                            'admin.landing_pages.edit',
                        ],
                        'text'          => 'Quản lý',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    // 'report'    => [
                    //     'route'         => 'admin.landing_pages.index',
                    //     'route_prefix'  => [
                    //         'admin.landing_pages.index',
                    //         'admin.landing_pages.edit',
                    //     ],
                    //     'text'          => 'Thống kê',
                    //     'roles'         => [
                    //         'admin',
                    //     ],
                    // ],
                ]
            ],
            'campaigns' => [
                'route'         => 'admin.campaigns.index',
                'route_prefix'  => [
                    'admin.campaigns.*',
                ],
                'x_icon_name'   => 'envelope',
                'x_icon_prefix' => null,
                'text'          => 'Email',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'features',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.campaigns.create',
                        'route_prefix'  => 'admin.campaigns.create',
                        'text'          => 'Gửi mail',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.campaigns.index',
                        'route_prefix'  => [
                            'admin.campaigns.index',
                            'admin.campaigns.edit',
                        ],
                        'text'          => 'Quản lý',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'templates'    => [
                        'route'         => 'admin.email_templates.index',
                        'route_prefix'  => [
                            'admin.email_templates.*',
                        ],
                        'text'          => 'Nội dung mail',
                        'roles'         => [
                            'admin',
                        ],
                    ],

                    // 'senders'    => [
                    //     'route'         => 'admin.email_senders.index',
                    //     'route_prefix'  => [
                    //         'admin.email_senders.*',
                    //     ],
                    //     'text'          => 'Email gửi',
                    //     'roles'         => [
                    //         'admin',
                    //     ],
                    // ],

                    // 'report'    => [
                    //     'route'         => 'admin.campaigns.index',
                    //     'route_prefix'  => [
                    //         'admin.campaigns.index',
                    //     ],
                    //     'text'          => 'Thống kê',
                    //     'roles'         => [
                    //         'admin',
                    //     ],
                    // ],
                ]
            ],
            'labels' => [
                'route'         => 'admin.labels.index',
                'route_prefix'  => [
                    'admin.labels.*',
                ],
                'x_icon_name'   => 'print',
                'x_icon_prefix' => null,
                'text'          => 'In tem',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'features',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.labels.create',
                        'route_prefix'  => 'admin.labels.create',
                        'text'          => 'Tạo mẫu in',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.labels.index',
                        'route_prefix'  => [
                            'admin.labels.index',
                            'admin.labels.edit',
                        ],
                        'text'          => 'Quản lý',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                ]
            ],
            'cards' => [
                'route'         => 'admin.cards.index',
                'route_prefix'  => [
                    'admin.cards.*',
                ],
                'x_icon_name'   => 'image',
                'x_icon_prefix' => null,
                'text'          => 'Thiệp',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'features',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.cards.create',
                        'route_prefix'  => 'admin.cards.create',
                        'text'          => 'Tạo thiệp',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.cards.index',
                        'route_prefix'  => [
                            'admin.cards.index',
                            'admin.cards.edit',
                        ],
                        'text'          => 'Quản lý',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                ]
            ],
            'lucky_draws' => [
                'route'         => 'admin.lucky_draws.index',
                'route_prefix'  => [
                    'admin.lucky_draws.*',
                ],
                'x_icon_name'   => 'dice',
                'x_icon_prefix' => null,
                'text'          => 'Quay số',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'features',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.lucky_draws.create',
                        'route_prefix'  => 'admin.lucky_draws.create',
                        'text'          => 'Tạo mới',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.lucky_draws.index',
                        'route_prefix'  => [
                            'admin.lucky_draws.index',
                            'admin.lucky_draws.edit',
                        ],
                        'text'          => 'Quản lý',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                ]
            ],
            'users' => [
                'route'         => 'admin.users.index',
                'route_prefix'  => [
                    'admin.users.*',
                ],
                'x_icon_name'   => 'user',
                'x_icon_prefix' => null,
                'text'          => 'Tài khoản',
                'is_admin'      => true,
                'roles'         => [
                    'admin',
                    'user',
                ],
                'group'         => 'management',
                'subMenus'      => [
                    'create'    => [
                        'route'         => 'admin.users.create',
                        'route_prefix'  => 'admin.users.create',
                        'text'          => 'Tạo tài khoản',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'index'    => [
                        'route'         => 'admin.users.index',
                        'route_prefix'  => [
                            'admin.users.index',
                            'admin.users.edit',
                        ],
                        'text'          => 'Quản lý',
                        'roles'         => [
                            'admin',
                        ],
                    ],
                ]
            ],
            /* end */
            'menu' => [
                'x_icon_name'   => 'box-open',
                'x_icon_prefix' => 'fa-solid',
                'route_prefix'  => [
                    'admin.companys.*',
                    'admin.events.*',
                ],
                'is_admin'      => true,
                'text'          => 'MENU',
                'roles'         => [
                    'admin',
                ],
                'subMenus'      => [
                    'companys' => [
                        'route'         => 'admin.companys.index',
                        'route_prefix'  => 'admin.companys.*',
                        'x_icon_name'   => 'building',
                        'x_icon_prefix' => null,
                        'text'          => 'Công ty',
                        'is_admin'      => true,
                    ],
                ]
            ],
            'features' => [
                'x_icon_name'   => 'box-open',
                'x_icon_prefix' => 'fa-solid',
                'route_prefix'  => [
                    'admin.campaigns.*',
                    'admin.landing_pages.*',
                ],
                'is_admin'      => true,
                'text'          => 'TÍNH NĂNG',
                'roles'         => [
                    'admin',
                ],
                'subMenus'      => [
                    'lucky_draws' => [
                        'route'         => 'admin.lucky_draws.index',
                        'route_prefix'  => [
                            'admin.lucky_draws.*',
                            'admin.lucky_draw_clients.*',
                            'admin.lucky_draw_rewards.*',
                        ],
                        'x_icon_name'   => 'dice',
                        'x_icon_prefix' => null,
                        'text'          => 'Quay số',
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                ],
            ],
            'management' => [
                'x_icon_name'   => 'box-open',
                'x_icon_prefix' => 'fa-solid',
                'route_prefix'  => [
                    'admin.users.*',
                    'admin.email_senders.*',
                ],
                'is_admin'      => true,
                'text'          => 'QUẢN LÝ',
                'roles'         => [
                    'admin',
                ],
                'subMenus'      => [
                    'users' => [
                        'route'         => 'admin.users.index',
                        'route_prefix'  => 'admin.users.*',
                        'x_icon_name'   => 'user',
                        'x_icon_prefix' => null,
                        'is_admin'      => true,
                        'roles'         => [
                            'admin',
                        ],
                    ],
                    'senders' => [
                        'route'         => 'admin.email_senders.index',
                        'route_prefix'  => 'admin.email_senders.*',
                        'x_icon_name'   => 'users',
                        'x_icon_prefix' => null,
                        'is_admin'      => true,
                        'text'          => 'Senders',
                        'roles'         => [],
                    ],
                ]
            ],
            'media' => [
                'route'         => 'admin.media.index',
                'route_prefix'  => 'admin.media.*',
                'x_icon_name'   => 'file',
                'x_icon_prefix' => 'fa-regular',
                'is_admin'      => true,
            ],
        ],
    ];

return $menus;
