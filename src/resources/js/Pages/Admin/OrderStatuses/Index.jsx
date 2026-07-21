import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ statuses, status, error }) {
    const destroy = (orderStatus) => {
        if (confirm(`「${orderStatus.label}」を削除しますか？`)) {
            router.delete(
                route('admin.order-statuses.destroy', orderStatus),
            );
        }
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="注文ステータス管理" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        注文ステータス管理
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
                    {error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-sm font-medium text-red-700">
                            {error}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="mb-4 flex justify-end">
                            <Link href={route('admin.order-statuses.create')}>
                                <PrimaryButton>
                                    状態を追加する
                                </PrimaryButton>
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th className="px-4 py-3">並び順</th>
                                        <th className="px-4 py-3">
                                            表示名
                                        </th>
                                        <th className="px-4 py-3">キー</th>
                                        <th className="px-4 py-3">属性</th>
                                        <th className="px-4 py-3">
                                            遷移可能な次の状態
                                        </th>
                                        <th className="px-4 py-3">操作</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {statuses.map((s) => (
                                        <tr key={s.id}>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {s.sort_order}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {s.label}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-500">
                                                {s.key}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex gap-1">
                                                    {s.is_initial && (
                                                        <span className="rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800">
                                                            初期状態
                                                        </span>
                                                    )}
                                                    {s.is_void && (
                                                        <span className="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                                            キャンセル相当
                                                        </span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {s.next_statuses.length === 0
                                                    ? '-'
                                                    : s.next_statuses
                                                          .map(
                                                              (n) => n.label,
                                                          )
                                                          .join('、')}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={route(
                                                            'admin.order-statuses.edit',
                                                            s,
                                                        )}
                                                    >
                                                        <SecondaryButton>
                                                            編集
                                                        </SecondaryButton>
                                                    </Link>
                                                    <DangerButton
                                                        onClick={() =>
                                                            destroy(s)
                                                        }
                                                    >
                                                        削除
                                                    </DangerButton>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
