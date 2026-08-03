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

    'accepted' => 'Dapat tanggapin ang field na :attribute.',
    'accepted_if' => 'Dapat tanggapin ang field na :attribute kapag ang :other ay :value.',
    'active_url' => 'Dapat maging valid na URL ang field na :attribute.',
    'after' => 'Dapat maging petsa pagkatapos ng :date ang field na :attribute.',
    'after_or_equal' => 'Dapat maging petsa pagkatapos o katumbas ng :date ang field na :attribute.',
    'alpha' => 'Dapat maglaman lamang ng mga titik ang field na :attribute.',
    'alpha_dash' => 'Dapat maglaman lamang ng mga titik, numero, gitling, at underscore ang field na :attribute.',
    'alpha_num' => 'Dapat maglaman lamang ng mga titik at numero ang field na :attribute.',
    'any_of' => 'Hindi valid ang field na :attribute.',
    'array' => 'Dapat maging array ang field na :attribute.',
    'ascii' => 'Dapat maglaman lamang ng single-byte alphanumeric na karakter at simbolo ang field na :attribute.',
    'before' => 'Dapat maging petsa bago ang :date ang field na :attribute.',
    'before_or_equal' => 'Dapat maging petsa bago o katumbas ng :date ang field na :attribute.',
    'between' => [
        'array' => 'Dapat magkaroon ng :min hanggang :max item ang field na :attribute.',
        'file' => 'Dapat nasa pagitan ng :min at :max kilobytes ang field na :attribute.',
        'numeric' => 'Dapat nasa pagitan ng :min at :max ang field na :attribute.',
        'string' => 'Dapat nasa pagitan ng :min at :max character ang field na :attribute.',
    ],
    'boolean' => 'Dapat true o false ang field na :attribute.',
    'can' => 'Naglalaman ang field na :attribute ng hindi awtorisadong value.',
    'confirmed' => 'Hindi tumutugma ang kumpirmasyon ng field na :attribute.',
    'contains' => 'Kulang ng kinakailangang value ang field na :attribute.',
    'current_password' => 'Mali ang password.',
    'date' => 'Dapat maging valid na petsa ang field na :attribute.',
    'date_equals' => 'Dapat maging petsa na katumbas ng :date ang field na :attribute.',
    'date_format' => 'Dapat tumugma sa format na :format ang field na :attribute.',
    'decimal' => 'Dapat magkaroon ng :decimal decimal places ang field na :attribute.',
    'declined' => 'Dapat tanggihan ang field na :attribute.',
    'declined_if' => 'Dapat tanggihan ang field na :attribute kapag ang :other ay :value.',
    'different' => 'Dapat magkaiba ang field na :attribute at :other.',
    'digits' => 'Dapat :digits digit ang field na :attribute.',
    'digits_between' => 'Dapat nasa pagitan ng :min at :max digit ang field na :attribute.',
    'dimensions' => 'May hindi valid na dimensyon ng larawan ang field na :attribute.',
    'distinct' => 'May duplicate na value ang field na :attribute.',
    'doesnt_contain' => 'Hindi dapat maglaman ang field na :attribute ng alinman sa mga sumusunod: :values.',
    'doesnt_end_with' => 'Hindi dapat magtapos ang field na :attribute sa alinman sa mga sumusunod: :values.',
    'doesnt_start_with' => 'Hindi dapat magsimula ang field na :attribute sa alinman sa mga sumusunod: :values.',
    'email' => 'Dapat maging valid na email address ang field na :attribute.',
    'encoding' => 'Dapat naka-encode sa :encoding ang field na :attribute.',
    'ends_with' => 'Dapat magtapos ang field na :attribute sa isa sa mga sumusunod: :values.',
    'enum' => 'Hindi valid ang napiling :attribute.',
    'exists' => 'Hindi valid ang napiling :attribute.',
    'extensions' => 'Dapat magkaroon ang field na :attribute ng isa sa mga sumusunod na extension: :values.',
    'file' => 'Dapat maging file ang field na :attribute.',
    'filled' => 'Dapat magkaroon ng value ang field na :attribute.',
    'gt' => [
        'array' => 'Dapat magkaroon ng higit sa :value item ang field na :attribute.',
        'file' => 'Dapat mas malaki sa :value kilobytes ang field na :attribute.',
        'numeric' => 'Dapat mas malaki sa :value ang field na :attribute.',
        'string' => 'Dapat mas mahaba sa :value character ang field na :attribute.',
    ],
    'gte' => [
        'array' => 'Dapat magkaroon ng :value item o higit pa ang field na :attribute.',
        'file' => 'Dapat mas malaki sa o katumbas ng :value kilobytes ang field na :attribute.',
        'numeric' => 'Dapat mas malaki sa o katumbas ng :value ang field na :attribute.',
        'string' => 'Dapat mas mahaba sa o katumbas ng :value character ang field na :attribute.',
    ],
    'hex_color' => 'Dapat maging valid na hexadecimal color ang field na :attribute.',
    'image' => 'Dapat maging larawan ang field na :attribute.',
    'in' => 'Hindi valid ang napiling :attribute.',
    'in_array' => 'Dapat mayroon ang field na :attribute sa :other.',
    'in_array_keys' => 'Dapat maglaman ang field na :attribute ng kahit isa sa mga sumusunod na key: :values.',
    'integer' => 'Dapat maging integer ang field na :attribute.',
    'ip' => 'Dapat maging valid na IP address ang field na :attribute.',
    'ipv4' => 'Dapat maging valid na IPv4 address ang field na :attribute.',
    'ipv6' => 'Dapat maging valid na IPv6 address ang field na :attribute.',
    'json' => 'Dapat maging valid na JSON string ang field na :attribute.',
    'list' => 'Dapat maging listahan ang field na :attribute.',
    'lowercase' => 'Dapat lowercase ang field na :attribute.',
    'lt' => [
        'array' => 'Dapat magkaroon ng mas kaunti sa :value item ang field na :attribute.',
        'file' => 'Dapat mas mababa sa :value kilobytes ang field na :attribute.',
        'numeric' => 'Dapat mas mababa sa :value ang field na :attribute.',
        'string' => 'Dapat mas maikli sa :value character ang field na :attribute.',
    ],
    'lte' => [
        'array' => 'Hindi dapat lumampas sa :value item ang field na :attribute.',
        'file' => 'Dapat mas mababa sa o katumbas ng :value kilobytes ang field na :attribute.',
        'numeric' => 'Dapat mas mababa sa o katumbas ng :value ang field na :attribute.',
        'string' => 'Dapat mas maikli sa o katumbas ng :value character ang field na :attribute.',
    ],
    'mac_address' => 'Dapat maging valid na MAC address ang field na :attribute.',
    'max' => [
        'array' => 'Hindi dapat magkaroon ng higit sa :max item ang field na :attribute.',
        'file' => 'Hindi dapat lumampas sa :max kilobytes ang field na :attribute.',
        'numeric' => 'Hindi dapat lumampas sa :max ang field na :attribute.',
        'string' => 'Hindi dapat lumampas sa :max character ang field na :attribute.',
    ],
    'max_digits' => 'Hindi dapat magkaroon ng higit sa :max digit ang field na :attribute.',
    'mimes' => 'Dapat maging file na may uri: :values ang field na :attribute.',
    'mimetypes' => 'Dapat maging file na may uri: :values ang field na :attribute.',
    'min' => [
        'array' => 'Dapat magkaroon ng hindi bababa sa :min item ang field na :attribute.',
        'file' => 'Dapat hindi bababa sa :min kilobytes ang field na :attribute.',
        'numeric' => 'Dapat hindi bababa sa :min ang field na :attribute.',
        'string' => 'Dapat hindi bababa sa :min character ang field na :attribute.',
    ],
    'min_digits' => 'Dapat hindi bababa sa :min digit ang field na :attribute.',
    'missing' => 'Dapat mawala ang field na :attribute.',
    'missing_if' => 'Dapat mawala ang field na :attribute kapag ang :other ay :value.',
    'missing_unless' => 'Dapat mawala ang field na :attribute maliban kung ang :other ay :value.',
    'missing_with' => 'Dapat mawala ang field na :attribute kapag naroroon ang :values.',
    'missing_with_all' => 'Dapat mawala ang field na :attribute kapag naroroon ang lahat ng :values.',
    'multiple_of' => 'Dapat maging multiple ng :value ang field na :attribute.',
    'not_in' => 'Hindi valid ang napiling :attribute.',
    'not_regex' => 'Hindi valid ang format ng field na :attribute.',
    'numeric' => 'Dapat maging numero ang field na :attribute.',
    'password' => [
        'letters' => 'Dapat maglaman ang field na :attribute ng kahit isang titik.',
        'mixed' => 'Dapat maglaman ang field na :attribute ng kahit isang malaking titik at isang maliit na titik.',
        'numbers' => 'Dapat maglaman ang field na :attribute ng kahit isang numero.',
        'symbols' => 'Dapat maglaman ang field na :attribute ng kahit isang simbolo.',
        'uncompromised' => 'Ang ibinigay na :attribute ay lumitaw sa isang data leak. Mangyaring pumili ng ibang :attribute.',
    ],
    'present' => 'Dapat naroroon ang field na :attribute.',
    'present_if' => 'Dapat naroroon ang field na :attribute kapag ang :other ay :value.',
    'present_unless' => 'Dapat naroroon ang field na :attribute maliban kung ang :other ay :value.',
    'present_with' => 'Dapat naroroon ang field na :attribute kapag naroroon ang :values.',
    'present_with_all' => 'Dapat naroroon ang field na :attribute kapag naroroon ang lahat ng :values.',
    'prohibited' => 'Ipinagbabawal ang field na :attribute.',
    'prohibited_if' => 'Ipinagbabawal ang field na :attribute kapag ang :other ay :value.',
    'prohibited_if_accepted' => 'Ipinagbabawal ang field na :attribute kapag tinanggap ang :other.',
    'prohibited_if_declined' => 'Ipinagbabawal ang field na :attribute kapag tinanggihan ang :other.',
    'prohibited_unless' => 'Ipinagbabawal ang field na :attribute maliban kung ang :other ay nasa :values.',
    'prohibits' => 'Ipinagbabawal ng field na :attribute ang pagkakaroon ng :other.',
    'regex' => 'Hindi valid ang format ng field na :attribute.',
    'required' => 'Kinakailangan ang field na :attribute.',
    'required_array_keys' => 'Dapat maglaman ang field na :attribute ng mga entry para sa: :values.',
    'required_if' => 'Kinakailangan ang field na :attribute kapag ang :other ay :value.',
    'required_if_accepted' => 'Kinakailangan ang field na :attribute kapag tinanggap ang :other.',
    'required_if_declined' => 'Kinakailangan ang field na :attribute kapag tinanggihan ang :other.',
    'required_unless' => 'Kinakailangan ang field na :attribute maliban kung ang :other ay nasa :values.',
    'required_with' => 'Kinakailangan ang field na :attribute kapag naroroon ang :values.',
    'required_with_all' => 'Kinakailangan ang field na :attribute kapag naroroon ang lahat ng :values.',
    'required_without' => 'Kinakailangan ang field na :attribute kapag wala ang :values.',
    'required_without_all' => 'Kinakailangan ang field na :attribute kapag wala ni isa sa :values.',
    'same' => 'Dapat tumugma ang field na :attribute sa :other.',
    'size' => [
        'array' => 'Dapat maglaman ng :size item ang field na :attribute.',
        'file' => 'Dapat :size kilobytes ang field na :attribute.',
        'numeric' => 'Dapat :size ang field na :attribute.',
        'string' => 'Dapat :size character ang field na :attribute.',
    ],
    'starts_with' => 'Dapat magsimula ang field na :attribute sa isa sa mga sumusunod: :values.',
    'string' => 'Dapat maging string ang field na :attribute.',
    'timezone' => 'Dapat maging valid na timezone ang field na :attribute.',
    'unique' => 'Nakuha na ang :attribute.',
    'uploaded' => 'Nabigo ang pag-upload ng :attribute.',
    'uppercase' => 'Dapat uppercase ang field na :attribute.',
    'url' => 'Dapat maging valid na URL ang field na :attribute.',
    'ulid' => 'Dapat maging valid na ULID ang field na :attribute.',
    'uuid' => 'Dapat maging valid na UUID ang field na :attribute.',

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
        'name' => 'pangalan',
        'email' => 'email address',
        'password' => 'password',
        'password_confirmation' => 'kumpirmasyon ng password',
        'quantity' => 'dami',
        'current_password' => 'kasalukuyang password',
        'remember' => 'tandaan ako',
    ],

];
