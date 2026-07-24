import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function ProductForm({
    shop,
    product = null,
    prefectures = [],
    units = [],
}) {
    const isEdit = product !== null;

    const { data, setData, post, transform, processing, errors } = useForm({
        name: product?.name ?? '',
        image: null,
        price: product?.price ?? 0,
        description: product?.description ?? '',
        stock: product?.stock ?? 0,
        is_active: product?.is_active ?? true,
        prefecture_id: product?.prefecture_id ?? '',
        unit_id: product?.unit_id ?? '',
        unit_quantity: product?.unit_quantity ?? 1,
        arrival_date: product?.arrival_date ?? '',
    });

    const [preview, setPreview] = useState(
        product?.image_path ? `/storage/${product.image_path}` : null,
    );

    const handleImageChange = (e) => {
        const file = e.target.files[0] ?? null;
        setData('image', file);
        setPreview(file ? URL.createObjectURL(file) : null);
    };

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            transform((data) => ({ ...data, _method: 'put' }));
            post(
                route('admin.shop.products.update', [shop, product.id]),
                { forceFormData: true },
            );
        } else {
            post(route('admin.shop.products.store', shop), {
                forceFormData: true,
            });
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="商品名" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel htmlFor="image" value="商品画像" />
                <input
                    id="image"
                    type="file"
                    accept="image/*"
                    className="mt-1 block w-full text-sm text-gray-700"
                    onChange={handleImageChange}
                />
                {preview && (
                    <img
                        src={preview}
                        alt="プレビュー"
                        className="mt-2 h-32 w-32 rounded object-cover"
                    />
                )}
                <InputError className="mt-2" message={errors.image} />
            </div>

            <div className="flex flex-col gap-4 sm:flex-row">
                <div className="w-full sm:flex-1">
                    <InputLabel htmlFor="price" value="価格（円）" />
                    <TextInput
                        id="price"
                        type="number"
                        min="0"
                        className="mt-1 block w-full"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.price} />
                </div>

                <div className="w-full sm:w-24">
                    <InputLabel htmlFor="unit_quantity" value="数量" />
                    <TextInput
                        id="unit_quantity"
                        type="number"
                        min="1"
                        className="mt-1 block w-full"
                        value={data.unit_quantity}
                        onChange={(e) =>
                            setData('unit_quantity', e.target.value)
                        }
                    />
                    <InputError
                        className="mt-2"
                        message={errors.unit_quantity}
                    />
                </div>

                <div className="w-full sm:w-40">
                    <InputLabel htmlFor="unit_id" value="単位" />
                    <select
                        id="unit_id"
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        value={data.unit_id}
                        onChange={(e) =>
                            setData('unit_id', e.target.value)
                        }
                    >
                        <option value="">選択してください</option>
                        {units.map((unit) => (
                            <option key={unit.id} value={unit.id}>
                                {unit.name}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.unit_id} />
                </div>
            </div>

            <div>
                <InputLabel htmlFor="stock" value="在庫数" />
                <TextInput
                    id="stock"
                    type="number"
                    min="0"
                    className="mt-1 block w-full"
                    value={data.stock}
                    onChange={(e) => setData('stock', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.stock} />
            </div>

            <div>
                <InputLabel htmlFor="prefecture_id" value="産地" />
                <select
                    id="prefecture_id"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    value={data.prefecture_id}
                    onChange={(e) => setData('prefecture_id', e.target.value)}
                >
                    <option value="">選択してください</option>
                    {prefectures.map((prefecture) => (
                        <option key={prefecture.id} value={prefecture.id}>
                            {prefecture.name}
                        </option>
                    ))}
                </select>
                <InputError className="mt-2" message={errors.prefecture_id} />
            </div>

            <div>
                <InputLabel htmlFor="arrival_date" value="入荷日" />
                <TextInput
                    id="arrival_date"
                    type="date"
                    className="mt-1 block w-full"
                    value={data.arrival_date ?? ''}
                    onChange={(e) => setData('arrival_date', e.target.value)}
                />
                <InputError className="mt-2" message={errors.arrival_date} />
            </div>

            <div>
                <InputLabel htmlFor="description" value="説明文" />
                <textarea
                    id="description"
                    rows={4}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                />
                <InputError className="mt-2" message={errors.description} />
            </div>

            <div className="flex items-center">
                <Checkbox
                    id="is_active"
                    checked={data.is_active}
                    onChange={(e) => setData('is_active', e.target.checked)}
                />
                <InputLabel
                    htmlFor="is_active"
                    value="公開する"
                    className="ml-2"
                />
            </div>

            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>
                    {isEdit ? '更新する' : '登録する'}
                </PrimaryButton>
            </div>
        </form>
    );
}
