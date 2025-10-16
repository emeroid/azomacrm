<?php


return [
    'builder' => [
        'text' => [
            'title' => 'Text',
            'icon' => '',
            'type' => 'text',
            'decription' => '',
            'properties' => [
                'label' => 'Text Field',
                'placeholder' => 'Enter Text',
                'required' => false
            ]
        ],
        'email' => [
            'title' => 'Email',
            'icon' => '',
            'type' => 'email',
            'decription' => '',
            'properties' => [
                'label' => 'Text Field',
                'placeholder' => 'Enter Email',
                'required' => false
            ]
        ],
        'number' => [
            'title' => 'Number',
            'icon' => '',
            'type' => 'numeric',
            'decription' => '',
            'properties' => [
                'label' => 'Text Field',
                'placeholder' => 'Enter Phone Number',
                'required' => false,
                'min' => 1,
                'max' => 100
            ]
        ],
        'select' => [
            'title' => 'Number',
            'icon' => '',
            'type' => 'numeric',
            'decription' => '',
            'properties' => [
                'label' => 'Text Field',
                'placeholder' => 'Enter Phone Number',
                'required' => false,
                'min' => 1,
                'max' => 100
            ]
        ]
    ],

    'default_template_fields' => [
        'text' => [
            'type' => 'text',
            'properties' => [
                'label' => 'Full Name',
                'placeholder' => 'Enter Full name',
                'required' => true
            ]
        ],
        'number' => [
            'type' => 'number',
            'properties' => [
                'label' => 'Mobile Number',
                'placeholder' => 'Enter Your Mobile Number',
                'required' => true
            ]
        ],
        'textarea' => [
            'type' => 'textarea',
            'properties' => [
                'label' => 'Home/Business Address',
                'placeholder' => 'Enter Your Address',
                'required' => true
            ]
        ],
        'select' => [
            'type' => 'select',
            'properties' => [
                'label' => 'Select State',
                'options' => [
                    "Abia",
                    "Adamawa",
                    "Akwa Ibom",
                    "Anambra",
                    "Bauchi",
                    "Bayelsa",
                    "Benue",
                    "Borno",
                    "Cross River",
                    "Delta",
                    "Ebonyi",
                    "Edo",
                    "Ekiti",
                    "Enugu",
                    "Gombe",
                    "Imo",
                    "Jigawa",
                    "Kaduna",
                    "Kano",
                    "Katsina",
                    "Kebbi",
                    "Kogi",
                    "Kwara",
                    "Lagos",
                    "Nasarawa",
                    "Niger",
                    "Ogun",
                    "Ondo",
                    "Osun",
                    "Oyo",
                    "Plateau",
                    "Rivers",
                    "Sokoto",
                    "Taraba",
                    "Yobe",
                    "Zamfara"
                ],
                'required' => true
            ]
        ],
    ]

];