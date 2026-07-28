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
                    <LocaleSwitcher />

                    <Link
                        href={route('cart.index', shop)}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        {t('header.cart')}
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
                </div>
            </div>
        </nav>
    );
}
