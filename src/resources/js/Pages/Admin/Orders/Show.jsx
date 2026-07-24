import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { formatDateTime } from '@/Utils/date';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ shop, order, status }) {
    const isScoped = !!shop;

    const updateStatus = (transition) => {
        if (!confirm(`この注文を「${transition.label}」にしますか？`)) {
            return;
        }

        const routeName = isScoped
            ? 'admin.shop.orders.update-status'
            : 'admin.orders.update-status';
        const routeParams = isScoped ? [shop, order.id] : order.id;

        router.patch(
            route(routeName, routeParams),
            { status: transition.key },
            { preserveScroll: true },
        );
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title={`注文詳細 #${order.id}`} />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        注文詳細
                    </div>

                    <Link
                        href={
                            isScoped
                                ? route('admin.shop.orders.index', shop)
                                : route('admin.orders.index')
                        }
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        注文一覧に戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                            {status}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <dl className="divide-y divide-gray-200">
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    注文番号
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.id}
                                </dd>
                            </div>
                            {!isScoped && (
                                <div className="grid grid-cols-3 gap-4 py-3">
                                    <dt className="text-sm font-medium text-gray-500">
                                        店舗
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900">
                                        {order.shop?.name}
                                    </dd>
                                </div>
                            )}
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    注文者
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {order.user?.name}（{order.user?.email}）
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

                        <h2 className="mt-6 text-sm font-semibold text-gray-900">
                            注文商品
                        </h2>

                        {/* スマホ表示: カード形式 */}
                        <div className="mt-2 space-y-4 sm:hidden">
                            {order.items.map((item) => (
                                <div
                                    key={item.id}
                                    className="rounded-lg border border-gray-200 p-4"
                                >
                                    <div className="text-sm font-medium text-gray-900">
                                        {item.product_name}
                                        {!item.product && (
                                            <span className="ml-2 text-xs text-gray-400">
                                                （削除済み商品）
                                            </span>
                                        )}
                                    </div>

                                    <dl className="mt-3 grid grid-cols-2 gap-y-1 text-sm text-gray-700">
                                        <dt className="text-gray-500">
                                            単価
                                        </dt>
                                        <dd className="text-right">
                                            ¥
                                            {Number(
                                                item.unit_price,
                                            ).toLocaleString()}
                                        </dd>

                                        <dt className="text-gray-500">
                                            数量
                                        </dt>
                                        <dd className="text-right">
                                            {item.quantity}
                                        </dd>

                                        <dt className="text-gray-500">
                                            小計
                                        </dt>
                                        <dd className="text-right font-semibold text-gray-900">
                                            ¥
                                            {Number(
                                                item.subtotal,
                                            ).toLocaleString()}
                                        </dd>
                                    </dl>
                                </div>
                            ))}
                        </div>

                        {/* PC/タブレット表示: テーブル形式 */}
                        <div className="mt-2 hidden overflow-x-auto sm:block">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th className="px-4 py-3">商品名</th>
                                        <th className="px-4 py-3">単価</th>
                                        <th className="px-4 py-3">数量</th>
                                        <th className="px-4 py-3">小計</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {order.items.map((item) => (
                                        <tr key={item.id}>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {item.product_name}
                                                {!item.product && (
                                                    <span className="ml-2 text-xs text-gray-400">
                                                        （削除済み商品）
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                ¥
                                                {Number(
                                                    item.unit_price,
                                                ).toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {item.quantity}
                                            </td>
                                            <td className="px-4 py-3 text-sm font-semibold text-gray-900">
                                                ¥
                                                {Number(
                                                    item.subtotal,
                                                ).toLocaleString()}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-6 flex justify-end text-xl font-bold text-gray-900">
                            合計金額：¥
                            {Number(order.total_amount).toLocaleString()}
                        </div>

                        {order.available_transitions?.length > 0 && (
                            <div className="mt-6 flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-6">
                                {order.available_transitions.map(
                                    (transition) => {
                                        const ButtonComponent =
                                            transition.is_void
                                                ? DangerButton
                                                : PrimaryButton;

                                        return (
                                            <ButtonComponent
                                                key={transition.key}
                                                onClick={() =>
                                                    updateStatus(transition)
                                                }
                                            >
                                                {transition.label}
                                            </ButtonComponent>
                                        );
                                    },
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
