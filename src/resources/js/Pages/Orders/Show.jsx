import { homeCrumb } from '@/Components/Breadcrumbs';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDateTime } from '@/Utils/date';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Show({ order }) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                homeCrumb(t),
                { label: t('dashboard.title'), href: route('dashboard') },
                { label: t('orders.detailTitle', { id: order.id }) },
            ]}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {t('orders.detailTitle', { id: order.id })}
                </h2>
            }
        >
            <Head title={t('orders.detailTitle', { id: order.id })} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('dashboard')}
                            className="text-sm text-gray-600 underline hover:text-gray-900"
                        >
                            {t('orders.backToDashboard')}
                        </Link>
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="divide-y divide-gray-200">
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    {t('orders.orderNumber')}
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.id}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    {t('orders.shop')}
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.shop?.name}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    {t('orders.status')}
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.status_label}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    {t('orders.orderedAt')}
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {formatDateTime(order.created_at)}
                                </dd>
                            </div>
                        </dl>

                        <h3 className="mb-2 mt-6 text-sm font-semibold text-gray-900">
                            {t('orders.itemsHeading')}
                        </h3>

                        <div className="divide-y divide-gray-200">
                            {order.items.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex items-center gap-4 py-4"
                                >
                                    {item.product ? (
                                        <img
                                            src={`/storage/${item.product.image_path}`}
                                            alt={item.product_name}
                                            className="h-16 w-16 rounded border border-gray-200 bg-white object-contain"
                                        />
                                    ) : (
                                        <div className="flex h-16 w-16 items-center justify-center rounded bg-gray-100 text-[10px] text-gray-400">
                                            {t('orders.noImage')}
                                        </div>
                                    )}

                                    <div className="flex-1">
                                        <p className="text-sm font-medium text-gray-900">
                                            {item.product_name}
                                            {!item.product && (
                                                <span className="ml-2 text-xs text-gray-400">
                                                    {t(
                                                        'orders.deletedProduct',
                                                    )}
                                                </span>
                                            )}
                                        </p>
                                        <p className="text-sm text-gray-500">
                                            ¥
                                            {Number(
                                                item.unit_price,
                                            ).toLocaleString()}
                                            {' × '}
                                            {item.quantity}
                                        </p>
                                    </div>

                                    <div className="text-sm font-semibold text-gray-900">
                                        ¥
                                        {Number(
                                            item.subtotal,
                                        ).toLocaleString()}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="mt-6 flex justify-end text-xl font-bold text-gray-900">
                            {t('orders.total', {
                                amount: Number(
                                    order.total_amount,
                                ).toLocaleString(),
                            })}
                        </div>

                        {order.deposit_amount > 0 && (
                            <dl className="mt-4 ml-auto max-w-sm space-y-1 rounded-md bg-gray-50 p-4 text-sm">
                                <div className="flex justify-between gap-4">
                                    <dt className="text-gray-600">
                                        {t('orders.depositPaid')}
                                    </dt>
                                    <dd className="font-medium text-gray-900">
                                        ¥
                                        {Number(
                                            order.deposit_amount,
                                        ).toLocaleString()}
                                        <span className="ml-2 text-xs text-gray-500">
                                            {t(
                                                `orders.paymentStatus.${order.payment_status}`,
                                            )}
                                        </span>
                                    </dd>
                                </div>
                                {order.payment_status === 'paid' && (
                                    <div className="flex justify-between gap-4">
                                        <dt className="text-gray-600">
                                            {t('orders.remainingAtPickup')}
                                        </dt>
                                        <dd className="font-bold text-gray-900">
                                            ¥
                                            {Number(
                                                order.remaining_amount,
                                            ).toLocaleString()}
                                        </dd>
                                    </div>
                                )}
                            </dl>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
