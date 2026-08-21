import Footer from '@/Components/Footer';
import Header from '@/Components/Header';
import Pagination from '@/Components/Pagination';
import TextInput from '@/Components/TextInput';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

const emptyFilters = {
    name: '',
};

export default function Show({ shop, products, filters }) {
    const { t } = useTranslation();
    const [form, setForm] = useState({ ...emptyFilters, ...filters });

    const hasActiveFilters = Object.values(filters ?? {}).some(
        (v) => v !== '' && v !== null && v !== undefined,
    );

    const setField = (key) => (e) =>
        setForm((prev) => ({ ...prev, [key]: e.target.value }));

    const submitSearch = (e) => {
        e.preventDefault();
        const query = Object.fromEntries(
            Object.entries(form).filter(([, v]) => v !== ''),
        );
        router.get(route('shops.show', shop), query, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <div className="flex min-h-screen flex-col bg-gray-100">
            <Head title={shop.name} />

            <Header title={shop.name} />

            <div className="flex-1 py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submitSearch} className="mb-6">
                            <div>
                                <label className="block text-xs font-medium text-gray-500">
                                    {t('shops.searchLabel')}
                                </label>
                                <TextInput
                                    type="text"
                                    value={form.name}
                                    onChange={setField('name')}
                                    className="mt-1 w-full sm:w-64"
                                />
                            </div>

                            <div className="mt-3 flex items-center gap-2">
                                <button
                                    type="submit"
                                    className="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                                >
                                    {t('shops.searchButton')}
                                </button>
                                {hasActiveFilters && (
                                    <Link
                                        href={route('shops.show', shop)}
                                        className="text-sm text-gray-600 underline hover:text-gray-900"
                                    >
                                        {t('shops.clearButton')}
                                    </Link>
                                )}
                            </div>
                        </form>

                        {products.data.length === 0 ? (
                            <p className="py-12 text-center text-sm text-gray-500">
                                {hasActiveFilters
                                    ? t('shops.noResultsFiltered')
                                    : t('shops.noResults')}
                            </p>
                        ) : (
                            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                                {products.data.map((product) => (
                                    <Link
                                        key={product.id}
                                        href={route('products.show', [
                                            shop,
                                            product.id,
                                        ])}
                                        className="overflow-hidden rounded-lg border border-gray-200 transition hover:shadow-md"
                                    >
                                        <img
                                            src={`/storage/${product.image_path}`}
                                            alt={product.name}
                                            className="mx-auto h-24 w-1/2 object-cover"
                                        />
                                        <div className="p-2">
                                            <h2 className="text-xs font-semibold text-gray-900">
                                                {product.name}
                                            </h2>
                                            {product.country && (
                                                <p className="text-[10px] text-gray-500">
                                                    {t('shops.origin', {
                                                        name: product.prefecture
                                                            ? `${product.country.name}（${product.prefecture.name}）`
                                                            : product.country
                                                                  .name,
                                                    })}
                                                </p>
                                            )}
                                            <p className="text-sm font-bold text-gray-900">
                                                ¥
                                                {Number(
                                                    product.price,
                                                ).toLocaleString()}
                                            </p>
                                            {product.unit && (
                                                <p className="text-[10px] text-gray-500">
                                                    {t('shops.unitShort', {
                                                        quantity:
                                                            product.unit_quantity ??
                                                            1,
                                                        unit: product.unit
                                                            .name,
                                                    })}
                                                </p>
                                            )}
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        )}

                        <Pagination links={products.links} />
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
}
