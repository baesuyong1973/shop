import { Link, usePage } from '@inertiajs/react';

export default function Header({ title, backHref, backLabel }) {
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
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        カートを見る
                    </Link>

                    {auth.user ? (
                        <>
                            <span className="text-sm text-gray-700">
                                {auth.user.name} さん
                            </span>
                            <Link
                                href={route('dashboard')}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                ダッシュボード
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                ログイン
                            </Link>
                            <Link
                                href={route('register')}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                会員登録
                            </Link>
                        </>
                    )}
                </div>
            </div>
        </nav>
    );
}
