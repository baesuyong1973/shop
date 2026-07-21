import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDate } from '@/Utils/date';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ shop, users, status }) {
    const isScoped = !!shop;

    const destroy = (user) => {
        if (confirm(`「${user.name}」を削除しますか？`)) {
            router.delete(route('admin.users.destroy', user.id));
        }
    };

    const toggleActive = (user) => {
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

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="ユーザー一覧" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        {isScoped ? '顧客一覧' : 'ユーザー一覧（全体）'}
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
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                            {status}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th className="px-4 py-3">名前</th>
                                        <th className="px-4 py-3">メールアドレス</th>
                                        {!isScoped && (
                                            <th className="px-4 py-3">
                                                利用店舗
                                            </th>
                                        )}
                                        <th className="px-4 py-3">メール認証</th>
                                        <th className="px-4 py-3">状態</th>
                                        <th className="px-4 py-3">登録日</th>
                                        <th className="px-4 py-3">操作</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {users.data.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={isScoped ? 6 : 7}
                                                className="px-4 py-6 text-center text-sm text-gray-500"
                                            >
                                                登録されているユーザーがいません。
                                            </td>
                                        </tr>
                                    )}

                                    {users.data.map((user) => (
                                        <tr key={user.id}>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {user.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {user.email}
                                            </td>
                                            {!isScoped && (
                                                <td className="px-4 py-3 text-sm text-gray-900">
                                                    {user.shop_names?.length
                                                        ? user.shop_names.join(
                                                              '、',
                                                          )
                                                        : '-'}
                                                </td>
                                            )}
                                            <td className="px-4 py-3 text-sm">
                                                {user.email_verified_at ? (
                                                    <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                        認証済み
                                                    </span>
                                                ) : (
                                                    <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                        未認証
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                {user.is_active ? (
                                                    <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                        有効
                                                    </span>
                                                ) : (
                                                    <span className="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                                        無効
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {formatDate(user.created_at)}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={
                                                            isScoped
                                                                ? route(
                                                                      'admin.shop.users.show',
                                                                      [
                                                                          shop,
                                                                          user.id,
                                                                      ],
                                                                  )
                                                                : route(
                                                                      'admin.users.show',
                                                                      user.id,
                                                                  )
                                                        }
                                                    >
                                                        <SecondaryButton>
                                                            詳細
                                                        </SecondaryButton>
                                                    </Link>
                                                    <SecondaryButton
                                                        onClick={() =>
                                                            toggleActive(user)
                                                        }
                                                    >
                                                        {user.is_active
                                                            ? '無効化'
                                                            : '有効化'}
                                                    </SecondaryButton>
                                                    {!isScoped && (
                                                        <DangerButton
                                                            onClick={() =>
                                                                destroy(user)
                                                            }
                                                        >
                                                            削除
                                                        </DangerButton>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <Pagination links={users.links} />
                    </div>
                </div>
            </div>
        </div>
    );
}
