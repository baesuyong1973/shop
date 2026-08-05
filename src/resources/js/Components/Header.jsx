import LocaleSwitcher from '@/Components/LocaleSwitcher';
import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Header({ title, backHref, backLabel }) {
    const { t } = useTranslation();
    const { auth, shop } = usePage().props;

    return (
        <nav className="border-b border-gray-100 bg-white">
            <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                {backHref ? (
                    <Link
                        href={backHref}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        {backLabel}
                    </Link>
                ) : (
                    <div className="text-lg font-semibold text-gray-900">
                        {title}
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <Link
                        href={route('cart.index', shop)}
                        aria-label={t('header.cart')}
                        className="text-gray-600 hover:text-gray-900"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="1.8"
                            className="h-6 w-6"
                        >
                            <circle cx="9" cy="21" r="1.25" fill="currentColor" stroke="none" />
                            <circle cx="19" cy="21" r="1.25" fill="currentColor" stroke="none" />
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M2.5 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 8H6"
                            />
                        </svg>
                    </Link>

                    {auth.user ? (
                        <>
                            <span className="text-sm text-gray-700">
                                {t('header.greeting', {
                                    name: auth.user.name,
                                })}
                            </span>
                            <Link
                                href={route('dashboard')}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                {t('header.dashboard')}
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                {t('header.login')}
                            </Link>
                            <Link
                                href={route('register')}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                {t('header.register')}
                            </Link>
                        </>
                    )}

                    <LocaleSwitcher available={shop.available_locales} />
                </div>
            </div>
        </nav>
    );
}
