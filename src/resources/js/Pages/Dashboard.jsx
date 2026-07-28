import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDateTime } from '@/Utils/date';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Dashboard({ orders }) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {t('dashboard.title')}
                </h2>
            }
        >
            <Head title={t('dashboard.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            {t('dashboard.loggedIn')}
                        </div>
                    </div>

                    <div className="mt-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                            {t('orders.history')}
                        </h3>

                        {orders.data.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                {t('orders.historyEmpty')}
                            </p>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                <th className="px-4 py-3">
                                                    {t('orders.orderNumber')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('orders.shop')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t(
                                                        'orders.itemCountHeader',
                                                    )}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('orders.totalHeader')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('orders.status')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('orders.orderedAt')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t(
                                                        'orders.actionsHeader',
                                                    )}
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
                                                        {t('orders.itemCount', {
                                                            count: order.items
                                                                .length,
                                                        })}
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
                                                                {t(
                                                                    'orders.detail',
                                                                )}
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
