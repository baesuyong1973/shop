import { router, usePage } from '@inertiajs/react';
import { useId } from 'react';
import { useTranslation } from 'react-i18next';

function JapanFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#fff" />
            <circle cx="30" cy="20" r="12" fill="#BC002D" />
        </svg>
    );
}

function UkFlag(props) {
    const clipId = useId();
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <clipPath id={clipId}>
                <rect width="60" height="40" />
            </clipPath>
            <g clipPath={`url(#${clipId})`}>
                <rect width="60" height="40" fill="#012169" />
                <path d="M0,0 60,40 M60,0 0,40" stroke="#fff" strokeWidth="8" />
                <path d="M0,0 60,40 M60,0 0,40" stroke="#C8102E" strokeWidth="4" />
                <path d="M30,0 30,40 M0,20 60,20" stroke="#fff" strokeWidth="14" />
                <path d="M30,0 30,40 M0,20 60,20" stroke="#C8102E" strokeWidth="8" />
            </g>
        </svg>
    );
}

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

    const flagClass = (active) =>
        'h-4 w-6 rounded-sm ring-1 ring-black/10 transition ' +
        (active ? 'opacity-100' : 'opacity-40 hover:opacity-70');

    return (
        <div className="flex items-center gap-2">
            <button
                type="button"
                onClick={() => switchTo('ja')}
                aria-label={t('lang.japanese')}
                aria-pressed={locale === 'ja'}
            >
                <JapanFlag className={flagClass(locale === 'ja')} />
            </button>
            <button
                type="button"
                onClick={() => switchTo('en')}
                aria-label={t('lang.english')}
                aria-pressed={locale === 'en'}
            >
                <UkFlag className={flagClass(locale === 'en')} />
            </button>
        </div>
    );
}
