import DangerButton from '@/Components/DangerButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDateTime } from '@/Utils/date';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ admin, error }) {
    const destroy = () => {
        if (confirm(`管理者「${admin.name}」を削除しますか？`)) {
            router.delete(route('admin.admins.destroy', admin.id));
        }
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="管理者詳細" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        管理者詳細
                    </div>

                    <Link
                        href={route('admin.admins.index')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        管理者一覧に戻る
                    </Link>
                </div>
            </nav>

            <div className="mx-auto mt-6 max-w-2xl px-4 sm:px-6 lg:px-8">
                <Link href={route('admin.admins.edit', admin.id)}>
                    <SecondaryButton>この管理者を編集する</SecondaryButton>
                </Link>
            </div>

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    {error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-sm font-medium text-red-700">
                            {error}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="divide-y divide-gray-200">
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    ID
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {admin.id}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    名前
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {admin.name}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    メールアドレス
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {admin.email}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    権限
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {admin.role === 'super_admin'
                                        ? 'スーパー管理者'
                                        : `店舗管理者（${admin.shop?.name ?? '-'}）`}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    登録日時
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {formatDateTime(admin.created_at)}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4 py-3">
                                <dt className="text-sm font-medium text-gray-500">
                                    更新日時
                                </dt>
                                <dd className="col-span-2 text-sm text-gray-900">
                                    {formatDateTime(admin.updated_at)}
                                </dd>
                            </div>
                        </dl>

                        <div className="mt-6">
                            <DangerButton onClick={destroy}>
                                この管理者を削除する
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
