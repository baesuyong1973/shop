import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDateTime } from '@/Utils/date';
import { Head, Link } from '@inertiajs/react';

export default function Show({ order }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    注文詳細 #{order.id}
                </h2>
            }
        >
            <Head title={`注文詳細 #${order.id}`} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('dashboard')}
                            className="text-sm text-gray-600 underline hover:text-gray-900"
                        >
                            ダッシュボードに戻る
                        </Link>
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="divide-y divide-gray-200">
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    注文番号
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.id}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    店舗
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.shop?.name}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    状態
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.status_label}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    注文日時
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {formatDateTime(order.created_at)}
                                </dd>
                            </div>
                        </dl>

                        <h3 className="mb-2 mt-6 text-sm font-semibold text-gray-900">
                            注文商品
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
                                            className="h-16 w-16 rounded object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-16 w-16 items-center justify-center rounded bg-gray-100 text-[10px] text-gray-400">
                                            画像なし
                                        </div>
                                    )}

                                    <div className="flex-1">
                                        <p className="text-sm font-medium text-gray-900">
                                            {item.product_name}
                                            {!item.product && (
                                                <span className="ml-2 text-xs text-gray-400">
                                                    （削除済み商品）
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
                            合計金額：¥
                            {Number(order.total_amount).toLocaleString()}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
