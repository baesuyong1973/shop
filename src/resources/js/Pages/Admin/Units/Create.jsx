import { Head, Link } from '@inertiajs/react';
import UnitForm from './Partials/UnitForm';

export default function Create({ shop = null }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="単位追加" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        単位追加
                    </div>

                    <Link
                        href={shop ? route('admin.shop.units.index', shop) : route('admin.units.index')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        単位一覧に戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <UnitForm shop={shop} />
                    </div>
                </div>
            </div>
        </div>
    );
}
