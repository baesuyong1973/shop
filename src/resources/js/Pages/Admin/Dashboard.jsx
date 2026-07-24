import PrimaryButton from '@/Components/PrimaryButton';
import QrScannerModal from '@/Components/QrScannerModal';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Dashboard({ status, error }) {
    const { auth, impersonating } = usePage().props;
    const admin = auth.user;
    const isSuperAdmin = admin.role === 'super_admin';
    const [scanning, setScanning] = useState(false);

    const logout = (e) => {
        e.preventDefault();
        router.post(route('admin.logout'));
    };

    const stopImpersonating = (e) => {
        e.preventDefault();
        router.post(route('admin.stop-impersonating'));
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="管理画面" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        管理画面
                    </div>

                    <Link
                        href="#"
                        onClick={logout}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        ログアウト
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {impersonating && (
                        <div className="mb-4 flex items-center justify-between rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                            <span>
                                現在「{admin.name}」として店舗管理者ログイン中です。
                            </span>
                            <Link
                                href="#"
                                onClick={stopImpersonating}
                                className="font-semibold underline hover:text-yellow-900"
                            >
                                スーパー管理者に戻る
                            </Link>
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        管理者としてログインしました。

                        {status && (
                            <div className="mt-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                                {status}
                            </div>
                        )}

                        {error && (
                            <div className="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                                {error}
                            </div>
                        )}

                        {!isSuperAdmin && (
                            <>
                                <div className="mt-4">
                                    <PrimaryButton
                                        onClick={() => setScanning(true)}
                                    >
                                        会員QRをスキャン
                                    </PrimaryButton>
                                </div>

                                <QrScannerModal
                                    show={scanning}
                                    onClose={() => setScanning(false)}
                                    scanRouteName="admin.shop.scan"
                                    scanRouteParams={admin.shop}
                                />
                            </>
                        )}

                        <div className="mt-4 rounded-md bg-gray-50 p-4">
                            <dl className="space-y-1">
                                <div className="flex gap-2 text-sm">
                                    <dt className="w-20 font-medium text-gray-500">
                                        名前
                                    </dt>
                                    <dd className="text-gray-900">
                                        {admin.name}
                                    </dd>
                                </div>
                                <div className="flex gap-2 text-sm">
                                    <dt className="w-20 font-medium text-gray-500">
                                        メール
                                    </dt>
                                    <dd className="text-gray-900">
                                        {admin.email}
                                    </dd>
                                </div>
                                <div className="flex gap-2 text-sm">
                                    <dt className="w-20 font-medium text-gray-500">
                                        権限
                                    </dt>
                                    <dd className="text-gray-900">
                                        {isSuperAdmin
                                            ? 'スーパー管理者'
                                            : `店舗管理者（${admin.shop?.name}）`}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div className="mt-4 flex flex-col gap-2">
                            {isSuperAdmin ? (
                                <>
                                    <Link
                                        href={route('admin.shops.index')}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        店舗一覧を見る
                                    </Link>
                                    <Link
                                        href={route('admin.products.index')}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        登録商品一覧を見る（全店舗）
                                    </Link>
                                    <Link
                                        href={route('admin.orders.index')}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        注文一覧を見る（全店舗）
                                    </Link>
                                    <Link
                                        href={route('admin.users.index')}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        ユーザー一覧を見る（全体）
                                    </Link>
                                    <Link
                                        href={route('admin.admins.index')}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        管理者一覧を見る
                                    </Link>
                                    <Link
                                        href={route(
                                            'admin.order-statuses.index',
                                        )}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        注文ステータス管理
                                    </Link>
                                </>
                            ) : (
                                <>
                                    <Link
                                        href={route(
                                            'admin.shop.products.index',
                                            admin.shop,
                                        )}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        登録商品一覧を見る
                                    </Link>
                                    <Link
                                        href={route(
                                            'admin.shop.orders.index',
                                            admin.shop,
                                        )}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        注文一覧を見る
                                    </Link>
                                    <Link
                                        href={route(
                                            'admin.shop.users.index',
                                            admin.shop,
                                        )}
                                        className="text-sm text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        顧客一覧を見る
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
