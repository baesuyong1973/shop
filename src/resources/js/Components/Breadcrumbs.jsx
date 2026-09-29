import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

/** First crumb on customer-facing pages: the shop directory. */
export const homeCrumb = (t) => ({
    label: t('breadcrumbs.home'),
    href: route('shops.index'),
});

/** First crumb on admin pages. */
export const adminHomeCrumb = () => ({
    label: '管理画面トップ',
    href: route('admin.dashboard'),
});

/**
 * Breadcrumb trail shown as a thin bar under a page's top navigation.
 *
 * `items` is ordered from the top level down: `{ label, href }`. The last
 * item is the current page and is rendered as plain text. `width` should
 * match the page header's container so the trail lines up with it.
 */
export default function Breadcrumbs({
    items,
    width = 'max-w-7xl px-4 sm:px-6 lg:px-8',
}) {
    const { t } = useTranslation();

    if (!items?.length) {
        return null;
    }

    return (
        <nav
            aria-label={t('breadcrumbs.label')}
            className="border-b border-gray-100 bg-white"
        >
            <ol className={`mx-auto flex flex-wrap items-center gap-x-2 gap-y-1 py-2 text-sm ${width}`}>
                {items.map((item, i) => {
                    const isCurrent = i === items.length - 1;

                    return (
                        <li key={i} className="flex min-w-0 items-center gap-2">
                            {i > 0 && (
                                <svg
                                    aria-hidden="true"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    className="h-4 w-4 flex-shrink-0 text-gray-400"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.17 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            )}
                            {isCurrent || !item.href ? (
                                <span
                                    aria-current={isCurrent ? 'page' : undefined}
                                    className={`truncate ${isCurrent ? 'font-medium text-gray-900' : 'text-gray-500'}`}
                                >
                                    {item.label}
                                </span>
                            ) : (
                                <Link
                                    href={item.href}
                                    className="truncate text-indigo-600 hover:text-indigo-900 hover:underline"
                                >
                                    {item.label}
                                </Link>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
