import Header from '@/Components/Header';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';
import { useMemo } from 'react';

export default function Show({ shop, product }) {
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
                backLabel="商品一覧に戻る"
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

                                {product.prefecture && (
                                    <p className="mt-1 text-sm text-gray-500">
                                        産地：{product.prefecture.name}
                                    </p>
                                )}

                                <p className="mt-4 text-2xl font-bold text-gray-900">
                                    ¥
                                    {Number(product.price).toLocaleString()}
                                </p>

                                {product.unit && (
                                    <p className="mt-1 text-sm text-gray-500">
                                        販売単位：
                                        {product.unit_quantity ?? 1}
                                        {product.unit.name}
                                    </p>
                                )}

                                <p className="mt-1 text-sm text-gray-500">
                                    在庫：{product.stock}点
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
                                                注文個数
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
                                            合計金額：¥
                                            {total.toLocaleString()}
                                        </div>

                                        <PrimaryButton disabled={processing}>
                                            カートに追加する
                                        </PrimaryButton>
                                    </form>
                                ) : (
                                    <p className="mt-6 text-sm font-medium text-red-600">
                                        在庫切れです。
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
