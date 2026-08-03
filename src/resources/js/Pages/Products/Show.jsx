import Header from '@/Components/Header';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

export default function Show({ shop, product }) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        quantity: 1,
    });

    const total = useMemo(() => {
        const quantity = Number(data.quantity) || 0;

        return product.price * quantity;
    }, [data.quantity, product.price]);

    const submit = (e) => {
        e.preventDefault();
        post(route('cart.store', [shop, product.id]));
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title={product.name} />

            <Header
                backHref={route('shops.show', shop)}
                backLabel={t('products.backToList')}
            />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2">
                            <img
                                src={`/storage/${product.image_path}`}
                                alt={product.name}
                                className="w-full rounded-lg object-cover"
                            />

                            <div>
                                <h1 className="text-xl font-bold text-gray-900">
                                    {product.name}
                                </h1>

                                {product.country && (
                                    <p className="mt-1 text-sm text-gray-500">
                                        {t('products.origin', {
                                            name: product.prefecture
                                                ? `${product.country.name}（${product.prefecture.name}）`
                                                : product.country.name,
                                        })}
                                    </p>
                                )}

                                <p className="mt-4 text-2xl font-bold text-gray-900">
                                    ¥
                                    {Number(product.price).toLocaleString()}
                                </p>

                                {product.unit && (
                                    <p className="mt-1 text-sm text-gray-500">
                                        {t('products.unitLine', {
                                            quantity:
                                                product.unit_quantity ?? 1,
                                            unit: product.unit.name,
                                        })}
                                    </p>
                                )}

                                <p className="mt-1 text-sm text-gray-500">
                                    {t('products.stock', {
                                        stock: product.stock,
                                    })}
                                </p>

                                {product.description && (
                                    <p className="mt-4 whitespace-pre-wrap text-sm text-gray-700">
                                        {product.description}
                                    </p>
                                )}

                                {product.stock > 0 ? (
                                    <form
                                        onSubmit={submit}
                                        className="mt-6 space-y-4"
                                    >
                                        <div>
                                            <label
                                                htmlFor="quantity"
                                                className="block text-sm font-medium text-gray-700"
                                            >
                                                {t('products.quantityLabel')}
                                            </label>
                                            <TextInput
                                                id="quantity"
                                                type="number"
                                                min="1"
                                                max={product.stock}
                                                value={data.quantity}
                                                onChange={(e) =>
                                                    setData(
                                                        'quantity',
                                                        e.target.value,
                                                    )
                                                }
                                                className="mt-1 w-32"
                                            />
                                            {errors.quantity && (
                                                <p className="mt-2 text-sm text-red-600">
                                                    {errors.quantity}
                                                </p>
                                            )}
                                        </div>

                                        <div className="text-lg font-bold text-gray-900">
                                            {t('products.total', {
                                                amount: total.toLocaleString(),
                                            })}
                                        </div>

                                        <PrimaryButton disabled={processing}>
                                            {t('products.addToCart')}
                                        </PrimaryButton>
                                    </form>
                                ) : (
                                    <p className="mt-6 text-sm font-medium text-red-600">
                                        {t('products.outOfStock')}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
