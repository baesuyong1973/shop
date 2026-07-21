import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDate, formatDateTime } from '@/Utils/date';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ shop, user, orders }) {
    const isScoped = !!shop;

    const shopNames = !isScoped
        ? [
              ...new Set(
                  (orders?.data ?? [])
                      .map((order) => order.shop?.name)
                      .filter(Boolean),
              ),
          ]
        : [];

    const destroy = () => {
        if (confirm(`「${user.name}」を削除しますか？`)) {
            router.delete(route('admin.users.destroy', user.id));
        }
    };

    const toggleActive = () => {
        const action = user.is_active ? '無効化' : '有効化';
        if (!confirm(`「${user.name}」を${action}しますか？`)) {
            return;
        }

        const routeName = isScoped
            ? 'admin.shop.users.toggle-active'
            : 'admin.users.toggle-active';
        const routeParams = isScoped ? [shop, user.id] : user.id;

        router.patch(route(routeName, routeParams), {}, { preserveScroll: true });
    };

    const updateOrderStatus = (order, transition) => {
        if (!confirm(`この注文を「${transition.label}」にしますか？`)) {
            return;
        }

        router.patch(
            route('admin.shop.orders.update-status', [shop, order.id]),
            { status: transition.key },
            { preserveScroll: true },
        );
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="ユーザー詳細" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        {isScoped ? '顧客詳細' : 'ユーザー詳細'}
                    </div>

                    <Link
                        href={
                            isScoped
                                ? route('admin.shop.users.index', shop)
                                : route('admin.users.index')
                        }
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        {isScoped ? '顧客一覧に戻る' : 'ユーザー一覧に戻る'}
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="divide-y divide-gray-200">
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    ID
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {user.id}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    名前
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {user.name}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    メールアドレス
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {user.email}
                                </dd>
                            </div>
                            {!isScoped && (
                                <div className="grid grid-cols-3 gap-4 py-3">
                                    <dt className="text-sm font-medium text-gray-500">
                                        利用店舗
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900">
                                        {shopNames.length
                                            ? shopNames.join('、')
                                            : '-'}
                                    </dd>
                                </div>
                            )}
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    アカウント状態
                                </dt>
                                <dd className="col-span-2 text-sm">
                                    {user.is_active ? (
                                        <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                            有効
                                        </span>
                                    ) : (
                                        <span className="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                            無効
                                        </span>
                                    )}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    メール認証状態
                                </dt>
                                <dd className="col-span-2 text-sm">
                                    {user.email_verified_at ? (
                                        <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                            認証済み（
                                            {formatDate(
                                                user.email_verified_at,
                                            )}
                                            ）
                                        </span>
                                    ) : (
                                        <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                            未認証
                                        </span>
                                    )}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    登録日時
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {formatDateTime(user.created_at)}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    更新日時
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {formatDateTime(user.updated_at)}
                                </dd>
                            </div>
                        </dl>

                        <div className="mt-6 flex gap-2">
                            <SecondaryButton onClick={toggleActive}>
                                {user.is_active
                                    ? 'このユーザーを無効化する'
                                    : 'このユーザーを有効化する'}
                            </SecondaryButton>

                            {!isScoped && (
                                <DangerButton onClick={destroy}>
                                    このユーザーを削除する
                                </DangerButton>
                            )}
                        </div>
                    </div>

                    {orders && isScoped && (
                        <div className="mt-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="mb-4 text-base font-semibold text-gray-900">
                                注文履歴（自店舗）
                            </h3>

                            {orders.data.length === 0 && (
                                <p className="py-6 text-center text-sm text-gray-500">
                                    注文がありません。
                                </p>
                            )}

                            <div className="space-y-4">
                                {orders.data.map((order) => (
                                    <div
                                        key={order.id}
                                        className="rounded-md border border-gray-200 p-4"
                                    >
                                        <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                                            <div>
                                                注文番号{order.id}・
                                                {order.status_label}・
                                                {formatDateTime(
                                                    order.created_at,
                                                )}
                                                ・¥
                                                {Number(
                                                    order.total_amount,
                                                ).toLocaleString()}
                                            </div>
                                            <div className="flex gap-2">
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
                                                                    updateOrderStatus(
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
                                                        [shop, order.id],
                                                    )}
                                                >
                                                    <SecondaryButton>
                                                        詳細
                                                    </SecondaryButton>
                                                </Link>
                                            </div>
                                        </div>

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
                                                {order.items.map((item) => (
                                                    <tr key={item.id}>
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
                                                            {item.quantity}
                                                        </td>
                                                        <td className="px-2 py-1 text-sm text-gray-900">
                                                            {item.product
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
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ))}
                            </div>

                            <Pagination links={orders.links} />
                        </div>
                    )}

                    {orders && !isScoped && (
                        <div className="mt-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="mb-4 text-base font-semibold text-gray-900">
                                注文履歴
                            </h3>

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
                                        {orders.data.length === 0 && (
                                            <tr>
                                                <td
                                                    colSpan={6}
                                                    className="px-4 py-6 text-center text-sm text-gray-500"
                                                >
                                                    注文がありません。
                                                </td>
                                            </tr>
                                        )}

                                        {orders.data.map((order) => (
                                            <tr key={order.id}>
                                                <td className="px-4 py-3 text-sm text-gray-900">
                                                    {order.id}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-gray-900">
                                                    {order.shop?.name}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-gray-900">
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
                                                            'admin.orders.show',
                                                            order.id,
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
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
