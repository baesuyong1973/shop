import { Head, Link } from '@inertiajs/react';
import AdminForm from './Partials/AdminForm';

export default function Create({ shops }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="管理者登録" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        管理者登録
                    </div>

                    <Link
                        href={route('admin.admins.index')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        管理者一覧に戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <AdminForm shops={shops} />
                    </div>
                </div>
            </div>
        </div>
    );
}
