import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDate } from '@/Utils/date';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ admins, status, error }) {
    const destroy = (admin) => {
        if (confirm(`管理者「${admin.name}」を削除しますか？`)) {
            router.delete(route('admin.admins.destroy', admin.id));
        }
    };

    const impersonate = (admin) => {
        if (
            confirm(
                `「${admin.name}」として店舗管理者ログインしますか？（2段階認証は不要です）`,
            )
        ) {
            router.post(route('admin.admins.impersonate', admin.id));
        }
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="管理者一覧" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        管理者一覧
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
                            <Link href={route('admin.admins.create')}>
                                <PrimaryButton>管理者を登録する</PrimaryButton>
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th className="px-4 py-3">名前</th>
                                        <th className="px-4 py-3">メールアドレス</th>
                                        <th className="px-4 py-3">権限</th>
                                        <th className="px-4 py-3">登録日</th>
                                        <th className="px-4 py-3">操作</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {admins.data.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={5}
                                                className="px-4 py-6 text-center text-sm text-gray-500"
                                            >
                                                登録されている管理者がいません。
                                            </td>
                                        </tr>
                                    )}

                                    {admins.data.map((admin) => (
                                        <tr key={admin.id}>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {admin.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {admin.email}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {admin.role === 'super_admin'
                                                    ? 'スーパー管理者'
                                                    : `店舗管理者（${admin.shop?.name ?? '-'}）`}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {formatDate(admin.created_at)}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={route(
                                                            'admin.admins.show',
                                                            admin.id,
                                                        )}
                                                    >
                                                        <SecondaryButton>
                                                            詳細
                                                        </SecondaryButton>
                                                    </Link>
                                                    <Link
                                                        href={route(
                                                            'admin.admins.edit',
                                                            admin.id,
                                                        )}
                                                    >
                                                        <SecondaryButton>
                                                            編集
                                                        </SecondaryButton>
                                                    </Link>
                                                    {admin.role ===
                                                        'shop_admin' && (
                                                        <SecondaryButton
                                                            onClick={() =>
                                                                impersonate(
                                                                    admin,
                                                                )
                                                            }
                                                        >
                                                            店舗管理者でログイン
                                                        </SecondaryButton>
                                                    )}
                                                    <DangerButton
                                                        onClick={() =>
                                                            destroy(admin)
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

                        <Pagination links={admins.links} />
                    </div>
                </div>
            </div>
        </div>
    );
}
