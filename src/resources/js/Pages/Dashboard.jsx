import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDateTime } from '@/Utils/date';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ orders }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            You're logged in!
                        </div>
                    </div>

                    <div className="mt-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                            注文履歴
                        </h3>

                        {orders.data.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                注文履歴がありません。
                            </p>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                <th className="px-4 py-3">
                                                    注文番号
                                                </th>
                                                <th className="px-4 py-3">
                                                    店舗
                                                </th>
                                                <th className="px-4 py-3">
                                                    商品点数
                                                </th>
                                                <th className="px-4 py-3">
                                                    合計金額
                                                </th>
                                                <th className="px-4 py-3">
                                                    状態
                                                </th>
                                                <th className="px-4 py-3">
                                                    注文日時
                                                </th>
                                                <th className="px-4 py-3">
                                                    操作
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {orders.data.map((order) => (
                                                <tr key={order.id}>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {order.id}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {order.shop?.name ?? '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {order.items.length}
                                                        点
                                                    </td>
                                                    <td className="px-4 py-3 text-sm font-semibold text-gray-900">
                                                        ¥
                                                        {Number(
                                                            order.total_amount,
                                                        ).toLocaleString()}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {order.status_label}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {formatDateTime(
                                                            order.created_at,
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm">
                                                        <Link
                                                            href={route(
                                                                'orders.show',
                                                                [
                                                                    order.shop,
                                                                    order.id,
                                                                ],
                                                            )}
                                                        >
                                                            <SecondaryButton>
                                                                詳細
                                                            </SecondaryButton>
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <Pagination links={orders.links} />
                            </>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
