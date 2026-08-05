import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm } from '@inertiajs/react';

const LOCALE_LABELS = {
    ja: '日本語',
    en: '英語',
    zh: '中国語（簡体字）',
    ko: '韓国語',
    th: 'タイ語',
    my: 'ミャンマー語',
    vi: 'ベトナム語',
    tl: 'フィリピン語',
    pt: 'ポルトガル語',
    ne: 'ネパール語',
    id: 'インドネシア語',
    'zh-TW': '中国語（繁体字・台湾）',
    hi: 'ヒンディー語',
    es: 'スペイン語',
    si: 'シンハラ語',
    km: 'クメール語',
    bn: 'ベンガル語',
    fr: 'フランス語',
    mn: 'モンゴル語',
    ur: 'ウルドゥー語',
};

export default function Locales({ supportedLocales = [], locales = [], status }) {
    const { data, setData, put, processing, errors } = useForm({
        locales,
    });

    const toggleLocale = (code) => {
        setData(
            'locales',
            data.locales.includes(code)
                ? data.locales.filter((l) => l !== code)
                : [...data.locales, code],
        );
    };

    const submit = (e) => {
        e.preventDefault();
        put(route('admin.settings.locales.update'));
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="言語設定" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        言語設定
                    </div>
                    <Link
                        href={route('admin.dashboard')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        ダッシュボードに戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <p className="text-sm text-gray-600">
                            ログイン・会員登録・パスワード再設定など、店舗に属さない画面で選択できる言語を設定します。
                        </p>

                        {status && (
                            <div className="mt-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                                {status}
                            </div>
                        )}

                        <form onSubmit={submit} className="mt-6 space-y-6">
                            <div>
                                <InputLabel value="選択できる言語" />
                                <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    {supportedLocales.map((code) => (
                                        <div
                                            key={code}
                                            className="flex items-center"
                                        >
                                            <Checkbox
                                                id={`locale-${code}`}
                                                checked={data.locales.includes(
                                                    code,
                                                )}
                                                onChange={() =>
                                                    toggleLocale(code)
                                                }
                                            />
                                            <InputLabel
                                                htmlFor={`locale-${code}`}
                                                value={
                                                    LOCALE_LABELS[code] ??
                                                    code
                                                }
                                                className="ml-2"
                                            />
                                        </div>
                                    ))}
                                </div>
                                <InputError
                                    className="mt-2"
                                    message={errors.locales}
                                />
                            </div>

                            <PrimaryButton disabled={processing}>
                                更新する
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
