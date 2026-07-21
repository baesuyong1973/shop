import { Head, Link } from '@inertiajs/react';

export default function Index({ shops }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="店舗一覧" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        店舗一覧
                    </div>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    {shops.length === 0 ? (
                        <p className="py-12 text-center text-sm text-gray-500">
                            現在営業中の店舗はありません。
                        </p>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {shops.map((shop) => (
                                <Link
                                    key={shop.id}
                                    href={route('shops.show', shop)}
                                    className="overflow-hidden rounded-lg border border-gray-200 bg-white p-6 transition hover:shadow-md"
                                >
                                    {shop.logo_path && (
                                        <img
                                            src={`/storage/${shop.logo_path}`}
                                            alt={shop.name}
                                            className="mb-4 h-16 w-16 rounded object-cover"
                                        />
                                    )}
                                    <h2 className="text-lg font-semibold text-gray-900">
                                        {shop.name}
                                    </h2>
                                    {shop.address && (
                                        <p className="mt-1 text-sm text-gray-500">
                                            {shop.address}
                                        </p>
                                    )}
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
