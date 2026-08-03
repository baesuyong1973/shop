<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute फिल्ड स्वीकार गरिनुपर्छ।',
    'accepted_if' => 'जब :other, :value हुन्छ, :attribute फिल्ड स्वीकार गरिनुपर्छ।',
    'active_url' => ':attribute फिल्ड एउटा मान्य URL हुनुपर्छ।',
    'after' => ':attribute फिल्ड :date पछिको मिति हुनुपर्छ।',
    'after_or_equal' => ':attribute फिल्ड :date पछि वा बराबरको मिति हुनुपर्छ।',
    'alpha' => ':attribute फिल्डमा केवल अक्षरहरू मात्र हुनुपर्छ।',
    'alpha_dash' => ':attribute फिल्डमा केवल अक्षर, अंक, ड्यास र अन्डरस्कोर मात्र हुनुपर्छ।',
    'alpha_num' => ':attribute फिल्डमा केवल अक्षर र अंक मात्र हुनुपर्छ।',
    'any_of' => ':attribute फिल्ड अमान्य छ।',
    'array' => ':attribute फिल्ड एउटा array हुनुपर्छ।',
    'ascii' => ':attribute फिल्डमा केवल सिङ्गल-बाइट अक्षरांकीय क्यारेक्टर र चिन्हहरू मात्र हुनुपर्छ।',
    'before' => ':attribute फिल्ड :date भन्दा अगाडिको मिति हुनुपर्छ।',
    'before_or_equal' => ':attribute फिल्ड :date भन्दा अगाडि वा बराबरको मिति हुनुपर्छ।',
    'between' => [
        'array' => ':attribute फिल्डमा :min देखि :max वस्तुहरू हुनुपर्छ।',
        'file' => ':attribute फिल्ड :min देखि :max किलोबाइटको बीचमा हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड :min देखि :max को बीचमा हुनुपर्छ।',
        'string' => ':attribute फिल्ड :min देखि :max अक्षरहरूको बीचमा हुनुपर्छ।',
    ],
    'boolean' => ':attribute फिल्ड true वा false हुनुपर्छ।',
    'can' => ':attribute फिल्डमा अनधिकृत मान समावेश छ।',
    'confirmed' => ':attribute फिल्डको पुष्टि मेल खाँदैन।',
    'contains' => ':attribute फिल्डमा आवश्यक मान छुटेको छ।',
    'current_password' => 'पासवर्ड गलत छ।',
    'date' => ':attribute फिल्ड एउटा मान्य मिति हुनुपर्छ।',
    'date_equals' => ':attribute फिल्ड :date सँग बराबरको मिति हुनुपर्छ।',
    'date_format' => ':attribute फिल्ड :format ढाँचासँग मेल खानुपर्छ।',
    'decimal' => ':attribute फिल्डमा :decimal दशमलव स्थानहरू हुनुपर्छ।',
    'declined' => ':attribute फिल्ड अस्वीकार गरिनुपर्छ।',
    'declined_if' => 'जब :other, :value हुन्छ, :attribute फिल्ड अस्वीकार गरिनुपर्छ।',
    'different' => ':attribute र :other फरक हुनुपर्छ।',
    'digits' => ':attribute फिल्ड :digits अंकको हुनुपर्छ।',
    'digits_between' => ':attribute फिल्ड :min देखि :max अंकको बीचमा हुनुपर्छ।',
    'dimensions' => ':attribute फिल्डको छविको आकार अमान्य छ।',
    'distinct' => ':attribute फिल्डमा नक्कली मान छ।',
    'doesnt_contain' => ':attribute फिल्डमा निम्न मध्ये कुनै पनि मान हुनु हुँदैन: :values।',
    'doesnt_end_with' => ':attribute फिल्ड निम्न मध्ये कुनैले पनि अन्त्य हुनु हुँदैन: :values।',
    'doesnt_start_with' => ':attribute फिल्ड निम्न मध्ये कुनैले पनि सुरु हुनु हुँदैन: :values।',
    'email' => ':attribute फिल्ड एउटा मान्य इमेल ठेगाना हुनुपर्छ।',
    'encoding' => ':attribute फिल्ड :encoding मा इनकोड गरिएको हुनुपर्छ।',
    'ends_with' => ':attribute फिल्ड निम्न मध्ये कुनै एउटाले अन्त्य हुनुपर्छ: :values।',
    'enum' => 'चयन गरिएको :attribute अमान्य छ।',
    'exists' => 'चयन गरिएको :attribute अमान्य छ।',
    'extensions' => ':attribute फिल्डमा निम्न मध्ये कुनै एउटा एक्सटेन्सन हुनुपर्छ: :values।',
    'file' => ':attribute फिल्ड एउटा फाइल हुनुपर्छ।',
    'filled' => ':attribute फिल्डमा मान हुनुपर्छ।',
    'gt' => [
        'array' => ':attribute फिल्डमा :value वस्तुहरू भन्दा बढी हुनुपर्छ।',
        'file' => ':attribute फिल्ड :value किलोबाइट भन्दा ठूलो हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड :value भन्दा ठूलो हुनुपर्छ।',
        'string' => ':attribute फिल्ड :value अक्षर भन्दा लामो हुनुपर्छ।',
    ],
    'gte' => [
        'array' => ':attribute फिल्डमा :value वा सोभन्दा बढी वस्तुहरू हुनुपर्छ।',
        'file' => ':attribute फिल्ड :value किलोबाइट भन्दा ठूलो वा बराबर हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड :value भन्दा ठूलो वा बराबर हुनुपर्छ।',
        'string' => ':attribute फिल्ड :value अक्षर भन्दा लामो वा बराबर हुनुपर्छ।',
    ],
    'hex_color' => ':attribute फिल्ड एउटा मान्य हेक्साडेसिमल रङ हुनुपर्छ।',
    'image' => ':attribute फिल्ड एउटा छवि हुनुपर्छ।',
    'in' => 'चयन गरिएको :attribute अमान्य छ।',
    'in_array' => ':attribute फिल्ड :other मा अवस्थित हुनुपर्छ।',
    'in_array_keys' => ':attribute फिल्डमा निम्न मध्ये कम्तिमा एउटा कुञ्जी हुनुपर्छ: :values।',
    'integer' => ':attribute फिल्ड एउटा इन्टिजर हुनुपर्छ।',
    'ip' => ':attribute फिल्ड एउटा मान्य IP ठेगाना हुनुपर्छ।',
    'ipv4' => ':attribute फिल्ड एउटा मान्य IPv4 ठेगाना हुनुपर्छ।',
    'ipv6' => ':attribute फिल्ड एउटा मान्य IPv6 ठेगाना हुनुपर्छ।',
    'json' => ':attribute फिल्ड एउटा मान्य JSON स्ट्रिङ हुनुपर्छ।',
    'list' => ':attribute फिल्ड एउटा सूची हुनुपर्छ।',
    'lowercase' => ':attribute फिल्ड सानो अक्षरमा हुनुपर्छ।',
    'lt' => [
        'array' => ':attribute फिल्डमा :value वस्तुहरू भन्दा कम हुनुपर्छ।',
        'file' => ':attribute फिल्ड :value किलोबाइट भन्दा सानो हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड :value भन्दा सानो हुनुपर्छ।',
        'string' => ':attribute फिल्ड :value अक्षर भन्दा छोटो हुनुपर्छ।',
    ],
    'lte' => [
        'array' => ':attribute फिल्डमा :value वस्तुहरू भन्दा बढी हुनु हुँदैन।',
        'file' => ':attribute फिल्ड :value किलोबाइट भन्दा सानो वा बराबर हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड :value भन्दा सानो वा बराबर हुनुपर्छ।',
        'string' => ':attribute फिल्ड :value अक्षर भन्दा छोटो वा बराबर हुनुपर्छ।',
    ],
    'mac_address' => ':attribute फिल्ड एउटा मान्य MAC ठेगाना हुनुपर्छ।',
    'max' => [
        'array' => ':attribute फिल्डमा :max वस्तुहरू भन्दा बढी हुनु हुँदैन।',
        'file' => ':attribute फिल्ड :max किलोबाइट भन्दा ठूलो हुनु हुँदैन।',
        'numeric' => ':attribute फिल्ड :max भन्दा ठूलो हुनु हुँदैन।',
        'string' => ':attribute फिल्ड :max अक्षर भन्दा लामो हुनु हुँदैन।',
    ],
    'max_digits' => ':attribute फिल्डमा :max अंक भन्दा बढी हुनु हुँदैन।',
    'mimes' => ':attribute फिल्ड यी प्रकारको फाइल हुनुपर्छ: :values।',
    'mimetypes' => ':attribute फिल्ड यी प्रकारको फाइल हुनुपर्छ: :values।',
    'min' => [
        'array' => ':attribute फिल्डमा कम्तिमा :min वस्तुहरू हुनुपर्छ।',
        'file' => ':attribute फिल्ड कम्तिमा :min किलोबाइट हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड कम्तिमा :min हुनुपर्छ।',
        'string' => ':attribute फिल्डमा कम्तिमा :min अक्षरहरू हुनुपर्छ।',
    ],
    'min_digits' => ':attribute फिल्डमा कम्तिमा :min अंकहरू हुनुपर्छ।',
    'missing' => ':attribute फिल्ड अनुपस्थित हुनुपर्छ।',
    'missing_if' => 'जब :other, :value हुन्छ, :attribute फिल्ड अनुपस्थित हुनुपर्छ।',
    'missing_unless' => ':other, :value नभएसम्म :attribute फिल्ड अनुपस्थित हुनुपर्छ।',
    'missing_with' => 'जब :values उपस्थित हुन्छ, :attribute फिल्ड अनुपस्थित हुनुपर्छ।',
    'missing_with_all' => 'जब सबै :values उपस्थित हुन्छन्, :attribute फिल्ड अनुपस्थित हुनुपर्छ।',
    'multiple_of' => ':attribute फिल्ड :value को गुणज हुनुपर्छ।',
    'not_in' => 'चयन गरिएको :attribute अमान्य छ।',
    'not_regex' => ':attribute फिल्डको ढाँचा अमान्य छ।',
    'numeric' => ':attribute फिल्ड एउटा संख्या हुनुपर्छ।',
    'password' => [
        'letters' => ':attribute फिल्डमा कम्तिमा एउटा अक्षर हुनुपर्छ।',
        'mixed' => ':attribute फिल्डमा कम्तिमा एउटा ठूलो र एउटा सानो अक्षर हुनुपर्छ।',
        'numbers' => ':attribute फिल्डमा कम्तिमा एउटा अंक हुनुपर्छ।',
        'symbols' => ':attribute फिल्डमा कम्तिमा एउटा चिन्ह हुनुपर्छ।',
        'uncompromised' => 'दिइएको :attribute डाटा लीकमा देखा परेको छ। कृपया फरक :attribute छान्नुहोस्।',
    ],
    'present' => ':attribute फिल्ड उपस्थित हुनुपर्छ।',
    'present_if' => 'जब :other, :value हुन्छ, :attribute फिल्ड उपस्थित हुनुपर्छ।',
    'present_unless' => ':other, :value नभएसम्म :attribute फिल्ड उपस्थित हुनुपर्छ।',
    'present_with' => 'जब :values उपस्थित हुन्छ, :attribute फिल्ड उपस्थित हुनुपर्छ।',
    'present_with_all' => 'जब सबै :values उपस्थित हुन्छन्, :attribute फिल्ड उपस्थित हुनुपर्छ।',
    'prohibited' => ':attribute फिल्ड निषेधित छ।',
    'prohibited_if' => 'जब :other, :value हुन्छ, :attribute फिल्ड निषेधित छ।',
    'prohibited_if_accepted' => 'जब :other स्वीकार गरिन्छ, :attribute फिल्ड निषेधित छ।',
    'prohibited_if_declined' => 'जब :other अस्वीकार गरिन्छ, :attribute फिल्ड निषेधित छ।',
    'prohibited_unless' => ':other, :values मा नभएसम्म :attribute फिल्ड निषेधित छ।',
    'prohibits' => ':attribute फिल्डले :other को उपस्थितिलाई निषेध गर्छ।',
    'regex' => ':attribute फिल्डको ढाँचा अमान्य छ।',
    'required' => ':attribute फिल्ड आवश्यक छ।',
    'required_array_keys' => ':attribute फिल्डमा निम्नका लागि प्रविष्टिहरू हुनुपर्छ: :values।',
    'required_if' => 'जब :other, :value हुन्छ, :attribute फिल्ड आवश्यक छ।',
    'required_if_accepted' => 'जब :other स्वीकार गरिन्छ, :attribute फिल्ड आवश्यक छ।',
    'required_if_declined' => 'जब :other अस्वीकार गरिन्छ, :attribute फिल्ड आवश्यक छ।',
    'required_unless' => ':other, :values मा नभएसम्म :attribute फिल्ड आवश्यक छ।',
    'required_with' => 'जब :values उपस्थित हुन्छ, :attribute फिल्ड आवश्यक छ।',
    'required_with_all' => 'जब सबै :values उपस्थित हुन्छन्, :attribute फिल्ड आवश्यक छ।',
    'required_without' => 'जब :values उपस्थित हुँदैन, :attribute फिल्ड आवश्यक छ।',
    'required_without_all' => 'जब :values मध्ये कुनै पनि उपस्थित हुँदैन, :attribute फिल्ड आवश्यक छ।',
    'same' => ':attribute फिल्ड :other सँग मेल खानुपर्छ।',
    'size' => [
        'array' => ':attribute फिल्डमा :size वस्तुहरू हुनुपर्छ।',
        'file' => ':attribute फिल्ड :size किलोबाइटको हुनुपर्छ।',
        'numeric' => ':attribute फिल्ड :size हुनुपर्छ।',
        'string' => ':attribute फिल्डमा :size अक्षरहरू हुनुपर्छ।',
    ],
    'starts_with' => ':attribute फिल्ड निम्न मध्ये कुनै एउटाले सुरु हुनुपर्छ: :values।',
    'string' => ':attribute फिल्ड एउटा स्ट्रिङ हुनुपर्छ।',
    'timezone' => ':attribute फिल्ड एउटा मान्य समय क्षेत्र हुनुपर्छ।',
    'unique' => ':attribute पहिले नै लिइसकिएको छ।',
    'uploaded' => ':attribute अपलोड गर्न असफल भयो।',
    'uppercase' => ':attribute फिल्ड ठूलो अक्षरमा हुनुपर्छ।',
    'url' => ':attribute फिल्ड एउटा मान्य URL हुनुपर्छ।',
    'ulid' => ':attribute फिल्ड एउटा मान्य ULID हुनुपर्छ।',
    'uuid' => ':attribute फिल्ड एउटा मान्य UUID हुनुपर्छ।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'नाम',
        'email' => 'इमेल ठेगाना',
        'password' => 'पासवर्ड',
        'password_confirmation' => 'पासवर्ड पुष्टि',
        'quantity' => 'परिमाण',
        'current_password' => 'हालको पासवर्ड',
        'remember' => 'मलाई सम्झनुहोस्',
    ],

];
