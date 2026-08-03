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

    'accepted' => ':attribute 欄位必須勾選。',
    'accepted_if' => '當 :other 為 :value 時，:attribute 欄位必須勾選。',
    'active_url' => ':attribute 欄位必須是有效的網址。',
    'after' => ':attribute 欄位必須是 :date 之後的日期。',
    'after_or_equal' => ':attribute 欄位必須是 :date 之後或相同的日期。',
    'alpha' => ':attribute 欄位只能包含字母。',
    'alpha_dash' => ':attribute 欄位只能包含字母、數字、連字號與底線。',
    'alpha_num' => ':attribute 欄位只能包含字母與數字。',
    'any_of' => ':attribute 欄位無效。',
    'array' => ':attribute 欄位必須是陣列。',
    'ascii' => ':attribute 欄位只能包含單位元組的英數字元與符號。',
    'before' => ':attribute 欄位必須是 :date 之前的日期。',
    'before_or_equal' => ':attribute 欄位必須是 :date 之前或相同的日期。',
    'between' => [
        'array' => ':attribute 欄位必須有 :min 到 :max 個項目。',
        'file' => ':attribute 欄位大小必須介於 :min 到 :max KB 之間。',
        'numeric' => ':attribute 欄位必須介於 :min 到 :max 之間。',
        'string' => ':attribute 欄位長度必須介於 :min 到 :max 個字元之間。',
    ],
    'boolean' => ':attribute 欄位必須是 true 或 false。',
    'can' => ':attribute 欄位包含未經授權的值。',
    'confirmed' => ':attribute 欄位的確認欄位不相符。',
    'contains' => ':attribute 欄位缺少必要的值。',
    'current_password' => '密碼不正確。',
    'date' => ':attribute 欄位必須是有效的日期。',
    'date_equals' => ':attribute 欄位必須是與 :date 相同的日期。',
    'date_format' => ':attribute 欄位必須符合 :format 格式。',
    'decimal' => ':attribute 欄位必須有 :decimal 位小數。',
    'declined' => ':attribute 欄位必須為拒絕。',
    'declined_if' => '當 :other 為 :value 時，:attribute 欄位必須為拒絕。',
    'different' => ':attribute 欄位與 :other 必須不同。',
    'digits' => ':attribute 欄位必須是 :digits 位數字。',
    'digits_between' => ':attribute 欄位位數必須介於 :min 到 :max 位之間。',
    'dimensions' => ':attribute 欄位的圖片尺寸無效。',
    'distinct' => ':attribute 欄位有重複的值。',
    'doesnt_contain' => ':attribute 欄位不得包含下列任何內容：:values。',
    'doesnt_end_with' => ':attribute 欄位不得以下列任何內容結尾：:values。',
    'doesnt_start_with' => ':attribute 欄位不得以下列任何內容開頭：:values。',
    'email' => ':attribute 欄位必須是有效的電子郵件地址。',
    'encoding' => ':attribute 欄位必須以 :encoding 編碼。',
    'ends_with' => ':attribute 欄位必須以下列其中之一結尾：:values。',
    'enum' => '所選的 :attribute 無效。',
    'exists' => '所選的 :attribute 無效。',
    'extensions' => ':attribute 欄位必須是下列副檔名之一：:values。',
    'file' => ':attribute 欄位必須是檔案。',
    'filled' => ':attribute 欄位不得為空。',
    'gt' => [
        'array' => ':attribute 欄位項目數必須大於 :value 個。',
        'file' => ':attribute 欄位大小必須大於 :value KB。',
        'numeric' => ':attribute 欄位必須大於 :value。',
        'string' => ':attribute 欄位長度必須大於 :value 個字元。',
    ],
    'gte' => [
        'array' => ':attribute 欄位項目數必須大於或等於 :value 個。',
        'file' => ':attribute 欄位大小必須大於或等於 :value KB。',
        'numeric' => ':attribute 欄位必須大於或等於 :value。',
        'string' => ':attribute 欄位長度必須大於或等於 :value 個字元。',
    ],
    'hex_color' => ':attribute 欄位必須是有效的十六進位顏色碼。',
    'image' => ':attribute 欄位必須是圖片。',
    'in' => '所選的 :attribute 無效。',
    'in_array' => ':attribute 欄位必須存在於 :other 中。',
    'in_array_keys' => ':attribute 欄位必須至少包含下列其中一個鍵值：:values。',
    'integer' => ':attribute 欄位必須是整數。',
    'ip' => ':attribute 欄位必須是有效的 IP 位址。',
    'ipv4' => ':attribute 欄位必須是有效的 IPv4 位址。',
    'ipv6' => ':attribute 欄位必須是有效的 IPv6 位址。',
    'json' => ':attribute 欄位必須是有效的 JSON 字串。',
    'list' => ':attribute 欄位必須是清單。',
    'lowercase' => ':attribute 欄位必須是小寫。',
    'lt' => [
        'array' => ':attribute 欄位項目數必須小於 :value 個。',
        'file' => ':attribute 欄位大小必須小於 :value KB。',
        'numeric' => ':attribute 欄位必須小於 :value。',
        'string' => ':attribute 欄位長度必須小於 :value 個字元。',
    ],
    'lte' => [
        'array' => ':attribute 欄位項目數不得多於 :value 個。',
        'file' => ':attribute 欄位大小必須小於或等於 :value KB。',
        'numeric' => ':attribute 欄位必須小於或等於 :value。',
        'string' => ':attribute 欄位長度必須小於或等於 :value 個字元。',
    ],
    'mac_address' => ':attribute 欄位必須是有效的 MAC 位址。',
    'max' => [
        'array' => ':attribute 欄位項目數不得多於 :max 個。',
        'file' => ':attribute 欄位大小不得大於 :max KB。',
        'numeric' => ':attribute 欄位不得大於 :max。',
        'string' => ':attribute 欄位長度不得多於 :max 個字元。',
    ],
    'max_digits' => ':attribute 欄位位數不得多於 :max 位。',
    'mimes' => ':attribute 欄位必須是下列檔案類型之一：:values。',
    'mimetypes' => ':attribute 欄位必須是下列檔案類型之一：:values。',
    'min' => [
        'array' => ':attribute 欄位至少須有 :min 個項目。',
        'file' => ':attribute 欄位大小至少須為 :min KB。',
        'numeric' => ':attribute 欄位至少須為 :min。',
        'string' => ':attribute 欄位長度至少須為 :min 個字元。',
    ],
    'min_digits' => ':attribute 欄位位數至少須為 :min 位。',
    'missing' => ':attribute 欄位必須不存在。',
    'missing_if' => '當 :other 為 :value 時，:attribute 欄位必須不存在。',
    'missing_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須不存在。',
    'missing_with' => '當 :values 存在時，:attribute 欄位必須不存在。',
    'missing_with_all' => '當 :values 皆存在時，:attribute 欄位必須不存在。',
    'multiple_of' => ':attribute 欄位必須是 :value 的倍數。',
    'not_in' => '所選的 :attribute 無效。',
    'not_regex' => ':attribute 欄位格式無效。',
    'numeric' => ':attribute 欄位必須是數字。',
    'password' => [
        'letters' => ':attribute 欄位必須至少包含一個字母。',
        'mixed' => ':attribute 欄位必須至少包含一個大寫字母及一個小寫字母。',
        'numbers' => ':attribute 欄位必須至少包含一個數字。',
        'symbols' => ':attribute 欄位必須至少包含一個符號。',
        'uncompromised' => '您輸入的 :attribute 已出現在資料外洩紀錄中，請更換其他 :attribute。',
    ],
    'present' => ':attribute 欄位必須存在。',
    'present_if' => '當 :other 為 :value 時，:attribute 欄位必須存在。',
    'present_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須存在。',
    'present_with' => '當 :values 存在時，:attribute 欄位必須存在。',
    'present_with_all' => '當 :values 皆存在時，:attribute 欄位必須存在。',
    'prohibited' => ':attribute 欄位為禁止使用。',
    'prohibited_if' => '當 :other 為 :value 時，:attribute 欄位為禁止使用。',
    'prohibited_if_accepted' => '當 :other 為已同意時，:attribute 欄位為禁止使用。',
    'prohibited_if_declined' => '當 :other 為已拒絕時，:attribute 欄位為禁止使用。',
    'prohibited_unless' => '除非 :other 在 :values 之中，否則 :attribute 欄位為禁止使用。',
    'prohibits' => ':attribute 欄位禁止 :other 同時存在。',
    'regex' => ':attribute 欄位格式無效。',
    'required' => ':attribute 欄位為必填。',
    'required_array_keys' => ':attribute 欄位必須包含下列項目：:values。',
    'required_if' => '當 :other 為 :value 時，:attribute 欄位為必填。',
    'required_if_accepted' => '當 :other 為已同意時，:attribute 欄位為必填。',
    'required_if_declined' => '當 :other 為已拒絕時，:attribute 欄位為必填。',
    'required_unless' => '除非 :other 在 :values 之中，否則 :attribute 欄位為必填。',
    'required_with' => '當 :values 存在時，:attribute 欄位為必填。',
    'required_with_all' => '當 :values 皆存在時，:attribute 欄位為必填。',
    'required_without' => '當 :values 不存在時，:attribute 欄位為必填。',
    'required_without_all' => '當 :values 皆不存在時，:attribute 欄位為必填。',
    'same' => ':attribute 欄位必須與 :other 相符。',
    'size' => [
        'array' => ':attribute 欄位必須包含 :size 個項目。',
        'file' => ':attribute 欄位大小必須是 :size KB。',
        'numeric' => ':attribute 欄位必須是 :size。',
        'string' => ':attribute 欄位長度必須是 :size 個字元。',
    ],
    'starts_with' => ':attribute 欄位必須以下列其中之一開頭：:values。',
    'string' => ':attribute 欄位必須是字串。',
    'timezone' => ':attribute 欄位必須是有效的時區。',
    'unique' => ':attribute 已被使用。',
    'uploaded' => ':attribute 上傳失敗。',
    'uppercase' => ':attribute 欄位必須是大寫。',
    'url' => ':attribute 欄位必須是有效的網址。',
    'ulid' => ':attribute 欄位必須是有效的 ULID。',
    'uuid' => ':attribute 欄位必須是有效的 UUID。',

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
        'name' => '姓名',
        'email' => '電子郵件地址',
        'password' => '密碼',
        'password_confirmation' => '確認密碼',
        'quantity' => '數量',
        'current_password' => '目前密碼',
        'remember' => '記住我',
    ],

];
