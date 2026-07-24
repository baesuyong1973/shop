import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { Head, Link } from '@inertiajs/react';

export default function Index({ shops, status, error }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="店舗一覧" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        店舗一覧
                    </div>

                    <Link
                        href={route('admin.dashboard')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        管理画面トップに戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                            {status}
                        </div>
                    )}
                    {error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-sm font-medium text-red-700">
                            {error}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <div className="mb-4 flex justify-end">
                            <Link href={route('admin.shops.create')}>
                                <PrimaryButton>店舗を登録する</PrimaryButton>
                            </Link>
                        </div>

                        {shops.data.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                登録されている店舗がありません。
                            </p>
                        ) : (
                            <>
                                {/* スマホ表示: カード形式 */}
                                <div className="space-y-4 sm:hidden">
                                    {shops.data.map((shop) => (
                                        <div
                                            key={shop.id}
                                            className="rounded-lg border border-gray-200 p-4"
                                        >
                                            <div className="flex items-center justify-between gap-2">
                                                <span className="text-sm font-medium text-gray-900">
                                                    {shop.name}
                                                </span>
                                                {shop.is_active ? (
                                                    <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                        営業中
                                                    </span>
                                                ) : (
                                                    <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                        非公開
                                                    </span>
                                                )}
                                            </div>

                                            <dl className="mt-3 grid grid-cols-2 gap-y-1 text-sm text-gray-700">
                                                <dt className="text-gray-500">
                                                    スラッグ
                                                </dt>
                                                <dd className="text-right">
                                                    {shop.slug}
                                                </dd>

                                                <dt className="text-gray-500">
                                                    住所
                                                </dt>
                                                <dd className="text-right">
                                                    {shop.address}
                                                </dd>

                                                {shop.phone && (
                                                    <>
                                                        <dt className="text-gray-500">
                                                            電話
                                                        </dt>
                                                        <dd className="text-right">
                                                            {shop.phone}
                                                        </dd>
                                                    </>
                                                )}
                                            </dl>

                                            <div className="mt-3 flex justify-end gap-2">
                                                <Link
                                                    href={route(
                                                        'shops.show',
                                                        shop,
                                                    )}
                                                    target="_blank"
                                                >
                                                    <SecondaryButton>
                                                        表示
                                                    </SecondaryButton>
                                                </Link>
                                                <Link
                                                    href={route(
                                                        'admin.shops.edit',
                                                        shop,
                                                    )}
                                                >
                                                    <SecondaryButton>
                                                        編集
                                                    </SecondaryButton>
                                                </Link>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* PC/タブレット表示: テーブル形式 */}
                                <div className="hidden overflow-x-auto sm:block">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                <th className="px-4 py-3">
                                                    名前
                                                </th>
                                                <th className="px-4 py-3">
                                                    スラッグ
                                                </th>
                                                <th className="px-4 py-3">
                                                    住所・電話
                                                </th>
                                                <th className="px-4 py-3">
                                                    状態
                                                </th>
                                                <th className="px-4 py-3">
                                                    操作
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {shops.data.map((shop) => (
                                                <tr key={shop.id}>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {shop.name}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {shop.slug}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {shop.address}
                                                        {shop.phone && (
                                                            <div className="text-xs text-gray-500">
                                                                {shop.phone}
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm">
                                                        {shop.is_active ? (
                                                            <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                                営業中
                                                            </span>
                                                        ) : (
                                                            <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                                非公開
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm">
                                                        <div className="flex gap-2">
                                                            <Link
                                                                href={route(
                                                                    'shops.show',
                                                                    shop,
                                                                )}
                                                                target="_blank"
                                                            >
                                                                <SecondaryButton>
                                                                    表示
                                                                </SecondaryButton>
                                                            </Link>
                                                            <Link
                                                                href={route(
                                                                    'admin.shops.edit',
                                                                    shop,
                                                                )}
                                                            >
                                                                <SecondaryButton>
                                                                    編集
                                                                </SecondaryButton>
                                                            </Link>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </>
                        )}

                        <Pagination links={shops.links} />
                    </div>
                </div>
            </div>
        </div>
    );
}
