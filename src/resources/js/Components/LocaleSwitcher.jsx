import { router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function LocaleSwitcher() {
    const { t } = useTranslation();
    const { locale } = usePage().props;

    const switchTo = (value) => {
        if (value === locale) {
            return;
        }

        router.post(
            route('locale.update'),
            { locale: value },
            { preserveScroll: true },
        );
    };

    return (
        <div className="flex items-center gap-1 text-sm text-gray-500">
            <button
                type="button"
                onClick={() => switchTo('ja')}
                className={
                    locale === 'ja'
                        ? 'font-semibold text-gray-900 underline'
                        : 'hover:text-gray-900'
                }
            >
                {t('lang.japanese')}
            </button>
            <span>/</span>
            <button
                type="button"
                onClick={() => switchTo('en')}
                className={
                    locale === 'en'
                        ? 'font-semibold text-gray-900 underline'
                        : 'hover:text-gray-900'
                }
            >
                {t('lang.english')}
            </button>
        </div>
    );
}
