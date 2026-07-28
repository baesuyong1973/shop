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

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeには:dateより後の日付を指定してください。',
    'after_or_equal' => ':attributeには:date以降の日付を指定してください。',
    'alpha' => ':attributeはアルファベットのみ使用できます。',
    'alpha_dash' => ':attributeはアルファベットと数字、ダッシュ(-)、アンダースコア(_)が使用できます。',
    'alpha_num' => ':attributeはアルファベットと数字が使用できます。',
    'any_of' => ':attributeは無効です。',
    'array' => ':attributeは配列を選択してください。',
    'ascii' => ':attributeは半角の英数字と記号のみ使用できます。',
    'before' => ':attributeには:dateより前の日付を指定してください。',
    'before_or_equal' => ':attributeには:date以前の日付を指定してください。',
    'between' => [
        'array' => ':attributeは:min個から:max個までの間で指定してください。',
        'file' => ':attributeは:minKBから:maxKBまでの間で指定してください。',
        'numeric' => ':attributeは:minから:maxまでの間で指定してください。',
        'string' => ':attributeは:min文字から:max文字までの間で指定してください。',
    ],
    'boolean' => ':attributeはtrueかfalseを指定してください。',
    'can' => ':attributeに許可されていない値が含まれています。',
    'confirmed' => ':attributeと確認用の値が一致しません。',
    'contains' => ':attributeに必要な値が含まれていません。',
    'current_password' => 'パスワードが間違っています。',
    'date' => ':attributeには有効な日付を指定してください。',
    'date_equals' => ':attributeには:dateと同じ日付を指定してください。',
    'date_format' => ':attributeの形式は:formatと一致させてください。',
    'decimal' => ':attributeは小数点以下:decimal桁で指定してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherには、異なる値を指定してください。',
    'digits' => ':attributeは:digits桁で指定してください。',
    'digits_between' => ':attributeは:min桁から:max桁までの間で指定してください。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeには重複した値が含まれています。',
    'doesnt_contain' => ':attributeには次のいずれも含めないでください: :values。',
    'doesnt_end_with' => ':attributeは次のいずれかで終わってはいけません: :values。',
    'doesnt_start_with' => ':attributeは次のいずれかで始まってはいけません: :values。',
    'email' => ':attributeには、有効なメールアドレスを指定してください。',
    'encoding' => ':attributeは:encodingでエンコードしてください。',
    'ends_with' => ':attributeは次のいずれかで終わる必要があります: :values。',
    'enum' => '選択された:attributeは無効です。',
    'exists' => '選択された:attributeは無効です。',
    'extensions' => ':attributeは次のいずれかの拡張子である必要があります: :values。',
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeに値を指定してください。',
    'gt' => [
        'array' => ':attributeは:value個より多く指定してください。',
        'file' => ':attributeは:valueKBより大きいサイズを指定してください。',
        'numeric' => ':attributeは:valueより大きい値を指定してください。',
        'string' => ':attributeは:value文字より長く指定してください。',
    ],
    'gte' => [
        'array' => ':attributeは:value個以上指定してください。',
        'file' => ':attributeは:valueKB以上のサイズを指定してください。',
        'numeric' => ':attributeは:value以上の値を指定してください。',
        'string' => ':attributeは:value文字以上で指定してください。',
    ],
    'hex_color' => ':attributeには有効な16進数カラーコードを指定してください。',
    'image' => ':attributeには画像を指定してください。',
    'in' => '選択された:attributeは無効です。',
    'in_array' => ':attributeは:otherに含まれていません。',
    'in_array_keys' => ':attributeには次のいずれかのキーを含めてください: :values。',
    'integer' => ':attributeは整数で指定してください。',
    'ip' => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json' => ':attributeには、有効なJSON文字列を指定してください。',
    'list' => ':attributeはリストである必要があります。',
    'lowercase' => ':attributeは小文字で指定してください。',
    'lt' => [
        'array' => ':attributeは:value個より少なく指定してください。',
        'file' => ':attributeは:valueKBより小さいサイズを指定してください。',
        'numeric' => ':attributeは:valueより小さい値を指定してください。',
        'string' => ':attributeは:value文字より短く指定してください。',
    ],
    'lte' => [
        'array' => ':attributeは:value個以下で指定してください。',
        'file' => ':attributeは:valueKB以下のサイズを指定してください。',
        'numeric' => ':attributeは:value以下の値を指定してください。',
        'string' => ':attributeは:value文字以下で指定してください。',
    ],
    'mac_address' => ':attributeには、有効なMACアドレスを指定してください。',
    'max' => [
        'array' => ':attributeは:max個以下にしてください。',
        'file' => ':attributeは:maxKB以下のサイズにしてください。',
        'numeric' => ':attributeは:max以下の数を指定してください。',
        'string' => ':attributeは:max文字以下で指定してください。',
    ],
    'max_digits' => ':attributeは:max桁以下にしてください。',
    'mimes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'mimetypes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'min' => [
        'array' => ':attributeは:min個以上にしてください。',
        'file' => ':attributeは:minKB以上のサイズにしてください。',
        'numeric' => ':attributeは:min以上の数を指定してください。',
        'string' => ':attributeは:min文字以上で指定してください。',
    ],
    'min_digits' => ':attributeは:min桁以上にしてください。',
    'missing' => ':attributeが存在しないようにしてください。',
    'missing_if' => ':otherが:valueの場合、:attributeが存在しないようにしてください。',
    'missing_unless' => ':otherが:valueでない限り、:attributeが存在しないようにしてください。',
    'missing_with' => ':valuesが存在する場合、:attributeが存在しないようにしてください。',
    'missing_with_all' => ':valuesが存在する場合、:attributeが存在しないようにしてください。',
    'multiple_of' => ':attributeは:valueの倍数である必要があります。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeには、数値を指定してください。',
    'password' => [
        'letters' => ':attributeには、少なくとも1文字のアルファベットを含めてください。',
        'mixed' => ':attributeには、少なくとも1つの大文字と小文字を含めてください。',
        'numbers' => ':attributeには、少なくとも1つの数字を含めてください。',
        'symbols' => ':attributeには、少なくとも1つの記号を含めてください。',
        'uncompromised' => '入力された:attributeは漏洩したデータに含まれています。別の:attributeを指定してください。',
    ],
    'present' => ':attributeが存在していません。',
    'present_if' => ':otherが:valueの場合、:attributeが存在している必要があります。',
    'present_unless' => ':otherが:valueでない限り、:attributeが存在している必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeが存在している必要があります。',
    'present_with_all' => ':valuesが存在する場合、:attributeが存在している必要があります。',
    'prohibited' => ':attributeは許可されていません。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは許可されません。',
    'prohibited_if_accepted' => ':otherが承認された場合、:attributeは許可されません。',
    'prohibited_if_declined' => ':otherが拒否された場合、:attributeは許可されません。',
    'prohibited_unless' => ':otherが:valuesに含まれていない限り、:attributeは許可されません。',
    'prohibits' => ':attributeが存在する場合、:otherは許可されません。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeを入力してください。',
    'required_array_keys' => ':attributeには次のキーの項目が必要です: :values。',
    'required_if' => ':otherが:valueの場合、:attributeを入力してください。',
    'required_if_accepted' => ':otherが承認された場合、:attributeを入力してください。',
    'required_if_declined' => ':otherが拒否された場合、:attributeを入力してください。',
    'required_unless' => ':otherが:valuesに含まれていない場合、:attributeを入力してください。',
    'required_with' => ':valuesが存在する場合、:attributeを入力してください。',
    'required_with_all' => ':valuesが存在する場合、:attributeを入力してください。',
    'required_without' => ':valuesが存在しない場合、:attributeを入力してください。',
    'required_without_all' => ':valuesが全て存在しない場合、:attributeを入力してください。',
    'same' => ':attributeと:otherは、同じ値にしてください。',
    'size' => [
        'array' => ':attributeは:size個にしてください。',
        'file' => ':attributeのサイズは:sizeKBにしてください。',
        'numeric' => ':attributeは:sizeにしてください。',
        'string' => ':attributeは:size文字にしてください。',
    ],
    'starts_with' => ':attributeは次のいずれかで始まる必要があります: :values。',
    'string' => ':attributeには、文字列を指定してください。',
    'timezone' => ':attributeには、有効なタイムゾーンを指定してください。',
    'unique' => ':attributeはすでに使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字で指定してください。',
    'url' => ':attributeの形式が無効です。',
    'ulid' => ':attributeには、有効なULIDを指定してください。',
    'uuid' => ':attributeには、有効なUUIDを指定してください。',

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
        'name' => '氏名',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'quantity' => '数量',
        'current_password' => '現在のパスワード',
        'remember' => 'ログイン状態を保持する',
    ],

];
