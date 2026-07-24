import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDateTime } from '@/Utils/date';
import { Head, Link, router } from '@inertiajs/react';

function groupByUser(orders) {
    const groups = [];
    const indexByUserId = new Map();

    orders.forEach((order) => {
        const key = order.user?.id ?? 'guest';

        if (!indexByUserId.has(key)) {
            indexByUserId.set(key, groups.length);
            groups.push({ user: order.user, orders: [] });
        }

        groups[indexByUserId.get(key)].orders.push(order);
    });

    return groups;
}

export default function Index({ shop, orders }) {
    const isScoped = !!shop;
    const userGroups = isScoped ? groupByUser(orders.data) : [];

    const updateStatus = (order, transition) => {
        if (!confirm(`この注文を「${transition.label}」にしますか？`)) {
            return;
        }

        const routeName = isScoped
            ? 'admin.shop.orders.update-status'
            : 'admin.orders.update-status';
        const routeParams = isScoped
            ? [shop, order.id]
            : order.id;

        router.patch(
            route(routeName, routeParams),
            { status: transition.key },
            { preserveScroll: true },
        );
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="注文一覧" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        注文一覧{!isScoped && '（全店舗）'}
                    </div>

                    <div className="flex items-center gap-4">
                        {isScoped && (
                            <Link
                                href={route(
                                    'admin.shop.orders.summary',
                                    shop,
                                )}
                                className="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                全注文詳細の集計を見る
                            </Link>
                        )}
                        <Link
                            href={route('admin.dashboard')}
                            className="text-sm text-gray-600 underline hover:text-gray-900"
                        >
                            管理画面トップに戻る
                        </Link>
                    </div>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {isScoped ? (
                        <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                            {userGroups.length === 0 && (
                                <p className="py-6 text-center text-sm text-gray-500">
                                    注文がありません。
                                </p>
                            )}

                            <div className="space-y-8">
                                {userGroups.map((group) => (
                                    <div key={group.user?.id ?? 'guest'}>
                                        <h3 className="text-base font-semibold text-gray-900">
                                            {group.user?.name ?? '-'}
                                            <span className="ml-2 text-sm font-normal text-gray-500">
                                                {group.user?.email}
                                            </span>
                                        </h3>

                                        <div className="mt-3 space-y-4">
                                            {group.orders.map((order) => (
                                                <div
                                                    key={order.id}
                                                    className="rounded-md border border-gray-200 p-4"
                                                >
                                                    <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                                                        <div>
                                                            注文番号
                                                            {order.id}
                                                            ・
                                                            {
                                                                order.status_label
                                                            }
                                                            ・
                                                            {formatDateTime(
                                                                order.created_at,
                                                            )}
                                                        </div>
                                                        <div className="flex gap-2">
                                                            {order.available_transitions?.map(
                                                                (
                                                                    transition,
                                                                ) => {
                                                                    const ButtonComponent =
                                                                        transition.is_void
                                                                            ? DangerButton
                                                                            : PrimaryButton;

                                                                    return (
                                                                        <ButtonComponent
                                                                            key={
                                                                                transition.key
                                                                            }
                                                                            onClick={() =>
                                                                                updateStatus(
                                                                                    order,
                                                                                    transition,
                                                                                )
                                                                            }
                                                                        >
                                                                            {
                                                                                transition.label
                                                                            }
                                                                        </ButtonComponent>
                                                                    );
                                                                },
                                                            )}
                                                            <Link
                                                                href={route(
                                                                    'admin.shop.orders.show',
                                                                    [
                                                                        shop,
                                                                        order.id,
                                                                    ],
                                                                )}
                                                            >
                                                                <SecondaryButton>
                                                                    詳細
                                                                </SecondaryButton>
                                                            </Link>
                                                        </div>
                                                    </div>

                                                    {/* スマホ表示: カード形式 */}
                                                    <div className="mt-3 space-y-2 sm:hidden">
                                                        {order.items.map(
                                                            (item) => (
                                                                <div
                                                                    key={
                                                                        item.id
                                                                    }
                                                                    className="rounded border border-gray-100 p-2"
                                                                >
                                                                    <div className="text-sm text-gray-900">
                                                                        {
                                                                            item.product_name
                                                                        }
                                                                        {!item.product && (
                                                                            <span className="ml-1 text-xs text-gray-400">
                                                                                （削除済み商品）
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                    <dl className="mt-1 grid grid-cols-2 gap-y-1 text-xs text-gray-600">
                                                                        <dt>
                                                                            数量
                                                                        </dt>
                                                                        <dd className="text-right">
                                                                            {
                                                                                item.quantity
                                                                            }
                                                                        </dd>

                                                                        <dt>
                                                                            単位
                                                                        </dt>
                                                                        <dd className="text-right">
                                                                            {item
                                                                                .product
                                                                                ?.unit
                                                                                ? `${item.product.unit_quantity ?? 1}${item.product.unit.name}`
                                                                                : '-'}
                                                                        </dd>

                                                                        <dt>
                                                                            値段
                                                                        </dt>
                                                                        <dd className="text-right">
                                                                            ¥
                                                                            {Number(
                                                                                item.subtotal,
                                                                            ).toLocaleString()}
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>

                                                    {/* PC/タブレット表示: テーブル形式 */}
                                                    <div className="hidden overflow-x-auto sm:block">
                                                        <table className="mt-3 min-w-full divide-y divide-gray-200">
                                                            <thead>
                                                                <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    <th className="px-2 py-1">
                                                                        商品名
                                                                    </th>
                                                                    <th className="px-2 py-1">
                                                                        数量
                                                                    </th>
                                                                    <th className="px-2 py-1">
                                                                        単位
                                                                    </th>
                                                                    <th className="px-2 py-1">
                                                                        値段
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className="divide-y divide-gray-100">
                                                                {order.items.map(
                                                                    (
                                                                        item,
                                                                    ) => (
                                                                        <tr
                                                                            key={
                                                                                item.id
                                                                            }
                                                                        >
                                                                            <td className="px-2 py-1 text-sm text-gray-900">
                                                                                {
                                                                                    item.product_name
                                                                                }
                                                                                {!item.product && (
                                                                                    <span className="ml-1 text-xs text-gray-400">
                                                                                        （削除済み商品）
                                                                                    </span>
                                                                                )}
                                                                            </td>
                                                                            <td className="px-2 py-1 text-sm text-gray-900">
                                                                                {
                                                                                    item.quantity
                                                                                }
                                                                            </td>
                                                                            <td className="px-2 py-1 text-sm text-gray-900">
                                                                                {item
                                                                                    .product
                                                                                    ?.unit
                                                                                    ? `${item.product.unit_quantity ?? 1}${item.product.unit.name}`
                                                                                    : '-'}
                                                                            </td>
                                                                            <td className="px-2 py-1 text-sm text-gray-900">
                                                                                ¥
                                                                                {Number(
                                                                                    item.subtotal,
                                                                                ).toLocaleString()}
                                                                            </td>
                                                                        </tr>
                                                                    ),
                                                                )}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <Pagination links={orders.links} />
                        </div>
                    ) : (
                        <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                            {orders.data.length === 0 ? (
                                <p className="py-6 text-center text-sm text-gray-500">
                                    注文がありません。
                                </p>
                            ) : (
                                <>
                                    {/* スマホ表示: カード形式 */}
                                    <div className="space-y-4 sm:hidden">
                                        {orders.data.map((order) => (
                                            <div
                                                key={order.id}
                                                className="rounded-lg border border-gray-200 p-4"
                                            >
                                                <div className="flex items-center justify-between gap-2">
                                                    <span className="text-sm font-medium text-gray-900">
                                                        注文番号 {order.id}
                                                    </span>
                                                    <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                        {order.status_label}
                                                    </span>
                                                </div>

                                                <dl className="mt-3 grid grid-cols-2 gap-y-1 text-sm text-gray-700">
                                                    <dt className="text-gray-500">
                                                        店舗
                                                    </dt>
                                                    <dd className="text-right">
                                                        {order.shop?.name}
                                                    </dd>

                                                    <dt className="text-gray-500">
                                                        注文者
                                                    </dt>
                                                    <dd className="text-right">
                                                        {order.user?.name ??
                                                            '-'}
                                                        <div className="text-xs text-gray-500">
                                                            {
                                                                order.user
                                                                    ?.email
                                                            }
                                                        </div>
                                                    </dd>

                                                    <dt className="text-gray-500">
                                                        合計金額
                                                    </dt>
                                                    <dd className="text-right font-semibold text-gray-900">
                                                        ¥
                                                        {Number(
                                                            order.total_amount,
                                                        ).toLocaleString()}
                                                    </dd>

                                                    <dt className="text-gray-500">
                                                        注文日時
                                                    </dt>
                                                    <dd className="text-right">
                                                        {formatDateTime(
                                                            order.created_at,
                                                        )}
                                                    </dd>
                                                </dl>

                                                <div className="mt-3 flex flex-wrap justify-end gap-2">
                                                    {order.available_transitions?.map(
                                                        (transition) => {
                                                            const ButtonComponent =
                                                                transition.is_void
                                                                    ? DangerButton
                                                                    : PrimaryButton;

                                                            return (
                                                                <ButtonComponent
                                                                    key={
                                                                        transition.key
                                                                    }
                                                                    onClick={() =>
                                                                        updateStatus(
                                                                            order,
                                                                            transition,
                                                                        )
                                                                    }
                                                                >
                                                                    {
                                                                        transition.label
                                                                    }
                                                                </ButtonComponent>
                                                            );
                                                        },
                                                    )}
                                                    <Link
                                                        href={route(
                                                            'admin.orders.show',
                                                            order.id,
                                                        )}
                                                    >
                                                        <SecondaryButton>
                                                            詳細
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
                                                        注文番号
                                                    </th>
                                                    <th className="px-4 py-3">
                                                        店舗
                                                    </th>
                                                    <th className="px-4 py-3">
                                                        注文者
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
                                                            {order.shop?.name}
                                                        </td>
                                                        <td className="px-4 py-3 text-sm text-gray-900">
                                                            {order.user
                                                                ?.name ?? '-'}
                                                            <div className="text-xs text-gray-500">
                                                                {
                                                                    order.user
                                                                        ?.email
                                                                }
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-sm text-gray-900">
                                                            ¥
                                                            {Number(
                                                                order.total_amount,
                                                            ).toLocaleString()}
                                                        </td>
                                                        <td className="px-4 py-3 text-sm text-gray-900">
                                                            {
                                                                order.status_label
                                                            }
                                                        </td>
                                                        <td className="px-4 py-3 text-sm text-gray-900">
                                                            {formatDateTime(
                                                                order.created_at,
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3 text-sm">
                                                            <div className="flex flex-wrap gap-2">
                                                                {order.available_transitions?.map(
                                                                    (
                                                                        transition,
                                                                    ) => {
                                                                        const ButtonComponent =
                                                                            transition.is_void
                                                                                ? DangerButton
                                                                                : PrimaryButton;

                                                                        return (
                                                                            <ButtonComponent
                                                                                key={
                                                                                    transition.key
                                                                                }
                                                                                onClick={() =>
                                                                                    updateStatus(
                                                                                        order,
                                                                                        transition,
                                                                                    )
                                                                                }
                                                                            >
                                                                                {
                                                                                    transition.label
                                                                                }
                                                                            </ButtonComponent>
                                                                        );
                                                                    },
                                                                )}
                                                                <Link
                                                                    href={route(
                                                                        'admin.orders.show',
                                                                        order.id,
                                                                    )}
                                                                >
                                                                    <SecondaryButton>
                                                                        詳細
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

                            <Pagination links={orders.links} />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
