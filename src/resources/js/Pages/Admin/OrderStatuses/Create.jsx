import { Head, Link } from '@inertiajs/react';
import OrderStatusForm from './Partials/OrderStatusForm';

export default function Create({ statuses }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="注文ステータス追加" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        注文ステータス追加
                    </div>

                    <Link
                        href={route('admin.order-statuses.index')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        ステータス一覧に戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <OrderStatusForm statuses={statuses} />
                    </div>
                </div>
            </div>
        </div>
    );
}
