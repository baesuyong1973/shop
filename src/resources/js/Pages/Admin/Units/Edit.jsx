import Breadcrumbs, { adminHomeCrumb } from '@/Components/Breadcrumbs';
import { Head, Link } from '@inertiajs/react';
import UnitForm from './Partials/UnitForm';

export default function Edit({ shop = null, unit }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="単位編集" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        単位編集
                    </div>

                    <Link
                        href={shop ? route('admin.shop.units.index', shop) : route('admin.units.index')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        単位一覧に戻る
                    </Link>
                </div>
            </nav>

            <Breadcrumbs
                items={[
                    adminHomeCrumb(),
                    shop ? { label: `単位管理（${shop.name}）`, href: route('admin.shop.units.index', shop) } : { label: '単位管理（共通）', href: route('admin.units.index') },
                    { label: `${unit.name}の編集` },
                ]}
            />

            <div className="py-12">
                <div className="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <UnitForm shop={shop} unit={unit} />
                    </div>
                </div>
            </div>
        </div>
    );
}
